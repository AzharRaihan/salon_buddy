<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('phone', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $suppliers = $query->paginate($perPage);

        return $this->successResponse([
            'suppliers' => $suppliers->items(),
            'total' => $suppliers->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'contact_person' => 'required|string|max:55',
            'phone' => 'required|string|max:55',
            'email' => 'nullable|string|max:55|unique:suppliers,email',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['del_status'] = 'Live';
            $supplier = Supplier::create($validatedData);
            DB::commit();
            return $this->successResponse($supplier, 'Supplier created successfully');
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
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }
        return $this->successResponse($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'contact_person' => 'required|string|max:55',
            'phone' => 'required|string|max:55',
            'email' => 'nullable|string|max:55|unique:suppliers,email,' . $id,
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $supplier->update($validatedData);
            DB::commit();
            return $this->successResponse($supplier, 'Supplier updated successfully');
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
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }
        try {
            $supplier->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
