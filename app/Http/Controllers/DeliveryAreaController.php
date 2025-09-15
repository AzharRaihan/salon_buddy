<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeliveryAreaController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryArea::query();

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('delivery_charge', 'like', '%' . $request->q . '%')
                ->orWhere('note', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('delivery_areas.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $deliveryAreas = $query->paginate($perPage);

        return $this->successResponse([
            'delivery_areas' => $deliveryAreas->items(),
            'total' => $deliveryAreas->total(),
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
            'delivery_charge' => 'required|numeric',
            'note' => 'nullable|string|max:255',
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
            $deliveryArea = DeliveryArea::create($validatedData);
            DB::commit();
            return $this->successResponse($deliveryArea, 'Delivery area created successfully');
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
        $deliveryArea = DeliveryArea::find($id);
        if (!$deliveryArea) {
            return $this->errorResponse('Delivery area not found', 404);
        }
        return $this->successResponse($deliveryArea);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deliveryArea = DeliveryArea::find($id);
        if (!$deliveryArea) {
            return $this->errorResponse('Unit not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'delivery_charge' => 'required|numeric',
            'note' => 'nullable|string|max:255',
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
            $deliveryArea->update($validatedData);
            DB::commit();
            return $this->successResponse($deliveryArea, 'Delivery area updated successfully');
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
        $deliveryArea = DeliveryArea::find($id);
        if (!$deliveryArea) {
            return $this->errorResponse('Delivery area not found', 404);
        }
        try {
            $deliveryArea->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Delivery area deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
