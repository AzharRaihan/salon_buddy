<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Traits\ApiResponse;
use App\Models\SalaryDetail;
use Illuminate\Http\Request;
use App\Models\SalaryPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalaryController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Salary::query();

        // Join with supplier
        $query->join('branches', 'salaries.branch_id', '=', 'branches.id')
              ->select('salaries.*', 'branches.branch_name');

        // Filter by del_status
        $query->where('salaries.del_status', 'Live');
        $query->where('salaries.company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('salaries.year', 'like', '%' . $request->q . '%')
            ->orWhere('salaries.month', 'like', '%' . $request->q . '%')
            ->orWhere('salaries.generated_date', 'like', '%' . $request->q . '%')
            ->orWhere('branches.branch_name', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $salaries = $query->paginate($perPage);

        return $this->successResponse([
            'salaries' => $salaries->items(),
            'total' => $salaries->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'year' => 'required|integer',
                'generated_date' => 'required|string|max:25',
                'total_amount' => 'required',
                'items' => 'required|array',
                'payments' => 'required|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }


            // Decode JSON items
            $items = $request->items;
            $payments = $request->payments;

            // Validate items array
            foreach ($items as $item) {
                $itemValidator = Validator::make($item, [
                    'employee_id' => 'required|exists:users,id',
                    'salary_amount' => 'required|numeric|min:1',
                    'net_salary' => 'required|numeric|min:0',
                ]);

                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            // Validate items array
            foreach ($items as $item) {
                $itemValidator = Validator::make($item, [
                    'employee_id' => 'required|exists:users,id',
                    'salary_amount' => 'required|numeric|min:1',
                    'net_salary' => 'required|numeric|min:0',
                ]);

                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            // Validate payment array
            foreach ($payments as $payment) {
                $itemValidator = Validator::make($payment, [
                    'payment_method_id' => 'required',
                    'amount' => 'required',
                ]);
                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            DB::beginTransaction();
            // Create salaries
            $salary = Salary::create([
                'year' => $request->year,
                'month' => $request->month,
                'total_amount' => $request->total_amount,
                'generated_date' => $request->generated_date,
                'branch_id' => 1,
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Create purchase items
            foreach ($items as $item) {
                SalaryDetail::create([
                    'salary_id' => $salary->id,
                    'employee_id' => $item['employee_id'],
                    'salary_amount' => $item['salary_amount'],
                    'overtime_rate' => $item['overtime_rate'],
                    'overtime_hour' => $item['overtime_hour'],
                    'additional_amount' => $item['additional_amount'],
                    'deduction_amount' => $item['deduction_amount'],
                    'absent_day' => $item['absent_day'],
                    'absent_day_amount' => $item['absent_day_amount'],
                    'tips' => $item['tips'] ?? 0,
                    'advance_taken' => $item['advance_taken'],
                    'net_salary' => $item['net_salary'],
                    'note' => $item['note'],
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);
            }
            // Create purchase items
            foreach ($payments as $payment) {
                SalaryPayment::create([
                    'salary_id' => $salary->id,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'company_id' => Auth::user()->company_id,
                ]);
            }
            DB::commit();
            return $this->successResponse($salary, 'Salary created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $salary = Salary::with([
                'salaryDetails:id,salary_id,employee_id,salary_amount,overtime_rate,overtime_hour,additional_amount,deduction_amount,absent_day,absent_day_amount,advance_taken,net_salary,note',
                'salaryDetails.employee:id,name',
                'salaryPayments:id,salary_id,payment_method_id,amount',
                'salaryPayments.paymentMethod:id,name'
            ])
            ->select('id', 'year', 'month', 'total_amount', 'generated_date')
            ->where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->firstOrFail();

            return $this->successResponse($salary, 'Salary retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $salary = Salary::find($id);
            if (!$salary) {
                return $this->errorResponse('Salary not found', 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'year' => 'required|integer',
                'generated_date' => 'required|string|max:25', 
                'total_amount' => 'required',
                'items' => 'required|array',
                'payments' => 'required|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Decode JSON items
            $items = $request->items;
            $payments = $request->payments;

            // Validate items array
            foreach ($items as $item) {
                $itemValidator = Validator::make($item, [
                    'employee_id' => 'required|exists:users,id',
                    'salary_amount' => 'required|numeric|min:1',
                    'net_salary' => 'required|numeric|min:0',
                ]);

                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            // Validate payment array
            foreach ($payments as $payment) {
                $itemValidator = Validator::make($payment, [
                    'payment_method_id' => 'required',
                    'amount' => 'required',
                ]);
                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            DB::beginTransaction();

            // Find and update salary
            $salary = Salary::findOrFail($id);
            $salary->update([
                'year' => $request->year,
                'month' => $request->month,
                'total_amount' => $request->total_amount,
                'generated_date' => $request->generated_date,
                'branch_id' => 1,
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Delete existing salary details and payments
            SalaryDetail::where('salary_id', $id)->delete();
            SalaryPayment::where('salary_id', $id)->delete();

            // Create salary details
            foreach ($items as $item) {
                SalaryDetail::create([
                    'salary_id' => $salary->id,
                    'employee_id' => $item['employee_id'],
                    'salary_amount' => $item['salary_amount'],
                    'overtime_rate' => $item['overtime_rate'],
                    'overtime_hour' => $item['overtime_hour'],
                    'additional_amount' => $item['additional_amount'],
                    'deduction_amount' => $item['deduction_amount'],
                    'absent_day' => $item['absent_day'],
                    'absent_day_amount' => $item['absent_day_amount'],
                    'tips' => $item['tips'] ?? 0,
                    'advance_taken' => $item['advance_taken'],
                    'net_salary' => $item['net_salary'],
                    'note' => $item['note'],
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            // Create salary payments
            foreach ($payments as $payment) {
                SalaryPayment::create([
                    'salary_id' => $salary->id,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            DB::commit();
            return $this->successResponse($salary, 'Salary updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $salary = Salary::find($id);
        if (!$salary) {
            return $this->errorResponse('Salary not found', 404);
        }

        DB::beginTransaction();
        try {
            // Delete salary details
            SalaryDetail::where('salary_id', $id)->update([
                'del_status' => 'Deleted'
            ]);

            // Delete salary payments 
            SalaryPayment::where('salary_id', $id)->update([
                'del_status' => 'Deleted'
            ]);

            // Delete main salary
            $salary->update([
                'del_status' => 'Deleted'
            ]);

            DB::commit();
            return $this->successResponse(null, 'Salary deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
