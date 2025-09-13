<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierPaymentController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupplierPayment::query();

        $query->join('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->select('supplier_payments.*', 'suppliers.name as supplier_name', 'suppliers.phone as supplier_phone')
            ->join('payment_methods', 'supplier_payments.payment_method_id', '=', 'payment_methods.id')
            ->select('supplier_payments.*', 'suppliers.name as supplier_name', 'suppliers.phone as supplier_phone', 'payment_methods.name as payment_method_name');

        $query->where('supplier_payments.company_id', Auth::user()->company_id);
        $query->where('supplier_payments.del_status', 'Live');
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('supplier_payments.reference_no', 'like', '%' . $request->q . '%')
                    ->orWhere('supplier_payments.note', 'like', '%' . $request->q . '%')
                    ->orWhere('suppliers.name', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('supplier_payments.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $supplierPayments = $query->paginate($perPage);

        return $this->successResponse([
            'supplier_payments' => $supplierPayments->items(),
            'total' => $supplierPayments->total(),
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
            'date' => 'required|string|max:25',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method_id' => 'required|exists:payment_methods,id'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $supplierPayment = SupplierPayment::create($validatedData);
            DB::commit();
            return $this->successResponse($supplierPayment, 'Supplier payment created successfully');
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
        $supplierPayment = SupplierPayment::with(['supplier:id,name', 'paymentMethod:id,name'])->find($id);
        if (!$supplierPayment) {
            return $this->errorResponse('Supplier payment not found', 404);
        }
        return $this->successResponse($supplierPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplierPayment = SupplierPayment::find($id);
        if (!$supplierPayment) {
            return $this->errorResponse('Supplier payment not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|string|max:25', 
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method_id' => 'required|exists:payment_methods,id'
        ];
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $supplierPayment->update($validatedData);
            DB::commit();
            return $this->successResponse($supplierPayment, 'Supplier payment updated successfully');
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
        $supplierPayment = SupplierPayment::find($id);
        if (!$supplierPayment) {
            return $this->errorResponse('Supplier payment not found', 404);
        }
        try {
            $supplierPayment->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Supplier payment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
