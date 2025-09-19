<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        // Join with users table
        $query->join('users as employees', 'expenses.employee_id', '=', 'employees.id')
              ->join('payment_methods', 'expenses.payment_method_id', '=', 'payment_methods.id')
              ->select('expenses.*', 'employees.name as employee_name', 'employees.phone as employee_phone', 'payment_methods.name as payment_method_name');

        // Filter by del_status
        $query->where('expenses.del_status', 'Live');
        $query->where('expenses.branch_id', $request->branch_id);
        $query->where('expenses.company_id', Auth::user()->company_id);
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('expenses.reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('expenses.note', 'like', '%' . $request->q . '%')
                ->orWhere('employees.name', 'like', '%' . $request->q . '%')
                ->orWhere('employees.phone', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('expenses.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $expenses = $query->paginate($perPage);

        return $this->successResponse([
            'expenses' => $expenses->items(),
            'total' => $expenses->total(),
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
            'category_id' => 'required|exists:expense_categories,id',
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
            $expense = Expense::create($validatedData);
            DB::commit();
            return $this->successResponse($expense, 'Expense created successfully');
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
        $expense = Expense::with(['paymentMethod:id,name', 'employee:id,name', 'category:id,name'])->find($id);
        if (!$expense) {
            return $this->errorResponse('Expense not found', 404);
        }
        return $this->successResponse($expense);
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
            'category_id' => 'required|exists:expense_categories,id',
            'employee_id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $expense = Expense::find($id);
        if (!$expense) {
            return $this->errorResponse('Expense not found', 404);
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
            $expense->update($validatedData);
            DB::commit();
            return $this->successResponse($expense, 'Expense updated successfully');
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
        $expense = Expense::find($id);
        if (!$expense) {
            return $this->errorResponse('Expense not found', 404);
        }
        $expense->delete();
        return $this->successResponse(null, 'Expense deleted successfully');
    }
}
