<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('description', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('expense_categories.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $expenseCategories = $query->paginate($perPage);

        return $this->successResponse([
            'expenseCategories' => $expenseCategories->items(),
            'total' => $expenseCategories->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'description' => 'nullable|string|max:255',
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
            $validatedData['company_id'] = Auth::user()->company_id;
            $expenseCategory = ExpenseCategory::create($validatedData);
            DB::commit();
            return $this->successResponse($expenseCategory, 'Expense category created successfully');
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
        $expenseCategory = ExpenseCategory::find($id);
        if (!$expenseCategory) {
            return $this->errorResponse('Expense category not found', 404);
        }
        return $this->successResponse($expenseCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         // Base validation rules
         $validationRules = [
            'name' => 'required|string|max:55',
            'description' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $expenseCategory = ExpenseCategory::find($id);
        if (!$expenseCategory) {
            return $this->errorResponse('Expense category not found', 404);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $expenseCategory->update($validatedData);
            DB::commit();
            return $this->successResponse($expenseCategory, 'Expense category updated successfully');
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
        $expenseCategory = ExpenseCategory::find($id);
        if (!$expenseCategory) {
            return $this->errorResponse('Expense category not found', 404);
        }
        try {
            $expenseCategory->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Expense category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
