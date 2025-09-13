<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CustomerReceive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerReceiveController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CustomerReceive::query();

        $query->join('customers', 'customer_receives.customer_id', '=', 'customers.id')
            ->select('customer_receives.*', 'customers.name as customer_name', 'customers.phone as customer_phone');

        $query->where('customer_receives.company_id', Auth::user()->company_id);
        $query->where('customer_receives.del_status', 'Live');
        $query->where('customer_receives.branch_id', $request->branch_id);
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('customer_receives.reference_no', 'like', '%' . $request->q . '%')
                    ->orWhere('customer_receives.note', 'like', '%' . $request->q . '%')
                    ->orWhere('customers.name', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('customer_receives.id', 'desc');
        }
        
        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $customerReceives = $query->paginate($perPage);

        return $this->successResponse([
            'customer_receives' => $customerReceives->items(),
            'total' => $customerReceives->total(),
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
            'customer_id' => 'required|exists:customers,id',
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
            $customerReceive = CustomerReceive::create($validatedData);
            DB::commit();
            return $this->successResponse($customerReceive, 'Customer receive created successfully');
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
        $customerReceive = CustomerReceive::with('customer:id,name', 'paymentMethod:id,name')
            ->findOrFail($id);

        return $this->successResponse([
            'customer_receive' => $customerReceive
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customerReceive = CustomerReceive::find($id);
        if (!$customerReceive) {
            return $this->errorResponse('Customer receive not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|string|max:25', 
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
            'customer_id' => 'required|exists:customers,id',
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
            $validatedData['created_at'] = now();
            $customerReceive->update($validatedData);
            DB::commit();
            return $this->successResponse($customerReceive, 'Customer receive updated successfully');
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
        $customerReceive = CustomerReceive::find($id);
        if (!$customerReceive) {
            return $this->errorResponse('Customer receive not found', 404);
        }
        try {
            $customerReceive->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Customer receive deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
