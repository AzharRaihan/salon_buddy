<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\DeliveryPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeliveryPartnerController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryPartner::query();

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
            $query->orderBy('delivery_partners.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $deliveryPartners = $query->paginate($perPage);

        return $this->successResponse([
            'delivery_partners' => $deliveryPartners->items(),
            'total' => $deliveryPartners->total(),
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
            $deliveryPartner = DeliveryPartner::create($validatedData);
            DB::commit();
            return $this->successResponse($deliveryPartner, 'Delivery partner created successfully');
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
        $deliveryPartner = DeliveryPartner::find($id);
        if (!$deliveryPartner) {
            return $this->errorResponse('Delivery partner not found', 404);
        }
        return $this->successResponse($deliveryPartner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deliveryPartner = DeliveryPartner::find($id);
        if (!$deliveryPartner) {
            return $this->errorResponse('Delivery partner not found', 404);
        }
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
            $validatedData['updated_at'] = now();
            $deliveryPartner->update($validatedData);
            DB::commit();
            return $this->successResponse($deliveryPartner, 'Delivery partner updated successfully');
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
        $deliveryPartner = DeliveryPartner::find($id);
        if (!$deliveryPartner) {
            return $this->errorResponse('Delivery partner not found', 404);
        }
        try {
            $deliveryPartner->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Delivery partner deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
