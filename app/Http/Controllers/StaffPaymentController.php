<?php

namespace App\Http\Controllers;

use App\Models\StaffPayment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StaffPaymentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StaffPayment::query();

        // Join with users table
        $query->join('users as employees', 'staff_payments.employee_id', '=', 'employees.id')
              ->join('payment_methods', 'staff_payments.payment_method_id', '=', 'payment_methods.id')
              ->select('staff_payments.*', 'employees.name as employee_name', 'employees.phone as employee_phone', 'payment_methods.name as payment_method_name');

        // Filter by del_status
        $query->where('staff_payments.del_status', 'Live');
        $query->where('staff_payments.branch_id', $request->branch_id);
        $query->where('staff_payments.company_id', Auth::user()->company_id);
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('staff_payments.reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('staff_payments.note', 'like', '%' . $request->q . '%')
                ->orWhere('employees.name', 'like', '%' . $request->q . '%')
                ->orWhere('employees.phone', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('staff_payments.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $staffPayments = $query->paginate($perPage);



        return $this->successResponse([
            'staff_payments' => $staffPayments->items(),
            'total' => $staffPayments->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'employee_id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $staffPayment = StaffPayment::create($validatedData);
            DB::commit();
            return $this->successResponse($staffPayment, 'Staff payment created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $staffPayment = StaffPayment::with(['paymentMethod:id,name', 'employee:id,name'])->find($id);
        if (!$staffPayment) {
            return $this->errorResponse('Staff payment not found', 404);
        }
        return $this->successResponse($staffPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'employee_id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $staffPayment = StaffPayment::find($id);
        if (!$staffPayment) {
            return $this->errorResponse('Staff payment not found', 404);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['note'] = $request->note == null ? '' : $request->note;
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $staffPayment->update($validatedData);
            DB::commit();
            return $this->successResponse($staffPayment, 'Staff payment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $staffPayment = StaffPayment::find($id);
        if (!$staffPayment) {
            return $this->errorResponse('Staff payment not found', 404);
        }

        DB::beginTransaction();
        try {
            // Delete staff payment
            StaffPayment::where('id', $id)->update([
                'del_status' => 'Deleted'
            ]);
            // Delete main staff payment
            $staffPayment->update([
                'del_status' => 'Deleted'
            ]);
            DB::commit();
            return $this->successResponse(null, 'Staff payment deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

    }
}
