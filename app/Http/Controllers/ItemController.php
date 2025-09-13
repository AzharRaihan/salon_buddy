<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::query();
        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('code', 'like', '%' . $request->q . '%');
        }
        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('items.id', 'desc');
        }
        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $items = $query->paginate($perPage);

        return $this->successResponse([
            'items' => $items->items(),
            'total' => $items->total(),
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Decode JSON strings to arrays
        $taxes = json_decode($request->taxes, true);
        $itemRows = json_decode($request->itemRows, true);
        // $useConsumption = filter_var($request->use_consumption, FILTER_VALIDATE_BOOLEAN);

        // Update request data with decoded arrays
        $request->merge([
            'taxes' => $taxes,
            'itemRows' => $itemRows
            // 'use_consumption' => $useConsumption
        ]);

        // Validate request
        $validationRules = [
            'name' => 'required|string|max:55',
            'code' => 'required|string|max:55|unique:items,code',
            'type' => 'required|in:Product,Service,Package',
            'category_id' => 'required|exists:categories,id',
            'loyalty_point' => 'nullable|numeric|min:0', 
            'status' => 'required|in:Enable,Disable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'description' => 'nullable|string',
            // 'use_consumption' => 'nullable|boolean',
            'taxes' => 'required|array',
            'taxes.*.tax' => 'required|string',
            'taxes.*.tax_rate' => 'required|numeric|min:0'
        ];

        // Type specific validation
        if ($request->type === 'Product') {
            $productValidator = Validator::make($request->all(), [
                'unit_id' => 'required|exists:units,id',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id'
            ]);

            if ($productValidator->fails()) {
                return $this->validationErrorResponse($productValidator->errors());
            }
        }

        if ($request->type === 'Service') {
            // Service validation - simplified without consumption
            $serviceValidator = Validator::make($request->all(), [
                'duration' => 'nullable|numeric',
                'duration_type' => 'nullable|in:Day,Hour,Minute',
                'sale_price' => 'nullable|numeric|min:0'
            ]);

            if ($serviceValidator->fails()) {
                return $this->validationErrorResponse($serviceValidator->errors());
            }
        }

        if ($request->type === 'Package') {
            $packageValidator = Validator::make($request->all(), [
                'sale_price' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'duration' => 'nullable|numeric',
                'duration_type' => 'nullable|in:Day,Hour,Minute',
                'itemRows' => 'required|array|min:1',
                'itemRows.*.item_id' => 'required|exists:items,id',
                'itemRows.*.price' => 'required|numeric|min:0',
                'itemRows.*.quantity' => 'required|numeric|min:1',
                'itemRows.*.discount' => 'nullable|numeric|min:0',
                'itemRows.*.total_price' => 'required|numeric|min:0'
            ]);

            if ($packageValidator->fails()) {
                return $this->validationErrorResponse($packageValidator->errors());
            }
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'items');
            }
            
            // Create item
            $item = Item::create([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
                'duration' => $request->duration,
                'duration_type' => $request->duration_type === "null" ? null : $request->duration_type,
                'category_id' => (int)$request->category_id,
                'loyalty_point' => $request->loyalty_point,
                'status' => $request->status,
                'profit_margin' => $request->profit_margin,
                'photo' => $validatedData['photo'] ?? null,
                'description' => $request->description,
                'purchase_price' => $request->purchase_price ?? 0,
                'last_purchase_price' => $request->last_purchase_price ?? 0,
                'last_three_purchase_avg' => $request->last_three_purchase_avg ?? 0,
                'sale_price' => $request->sale_price ?? 0,
                'unit_id' => (int)$request->unit_id,
                'supplier_id' => (int)$request->supplier_id,
                // 'use_consumption' => $request->type === 'Service' ? ($request->use_consumption ?? false) : false,
                'tax_information' => json_encode($request->taxes), // Encode taxes array to JSON string
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
                'del_status' => 'Live'
            ]);

            // Create item rows for Service and Package types
            if ($request->type === 'Package') {
                if (!empty($request->itemRows)) {
                    foreach ($request->itemRows as $row) {
                        $item->itemDetails()->create([
                            'item_relation_id' => $item->id,
                            'item_id' => $row['item_id'],
                            'unit_id' => $row['unit'] ?? null,
                            'user_id' => Auth::id(),
                            'company_id' => Auth::user()->company_id,
                        ] + $row);
                    }
                }
            }

            DB::commit();
            return $this->successResponse($item, 'Item created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        try {
            // $item = Item::with([
            //     'itemDetails:id,item_relation_id,item_id,unit_id,consumption,conversion_rate,cost_per_unit,price,total_price,quantity,discount',
            //     'unit:id,name'
            // ])
            // ->where('id', $id)
            // ->where('company_id', Auth::user()->company_id)
            // ->where('del_status', 'Live')
            // ->firstOrFail();

            // // Add photo URL
            // $item->photo_url = $item->photo ? asset('assets/images/' . $item->photo) : null;

            // // Format item details for frontend
            // if ($item->type === 'Package') {
            //     $item->item_rows = $item->itemDetails->map(function ($detail) {
            //         return [
            //             'item_id' => $detail->item_id,
            //             'item_name' => $detail->item->name,
            //             'price' => $detail->price,
            //             'quantity' => $detail->quantity,
            //             'discount' => $detail->discount,
            //             'total_price' => $detail->total_price
            //         ];
            //     });
            // }

            return $this->successResponse([
                'item' => $item->load(
                                    'itemDetails:id,item_relation_id,item_id,unit_id,consumption,conversion_rate,cost_per_unit,price,total_price,quantity,discount',
                                    'unit:id,name',
                                    'category:id,name',
                                    'supplier:id,name',
                                    'itemDetails.items:id,name,code'
                                )
            ]);

            // return $this->successResponse($item, 'Item retrieved successfully');
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
            $item = Item::find($id);
            if (!$item) {
                return $this->errorResponse('Item not found', 404);
            }

            // Decode JSON strings to arrays
            $taxes = json_decode($request->taxes, true);
            $itemRows = json_decode($request->itemRows, true);
            $useConsumption = filter_var($request->use_consumption, FILTER_VALIDATE_BOOLEAN);

            // Update request data with decoded arrays
            $request->merge([
                'taxes' => $taxes,
                'itemRows' => $itemRows,
                'use_consumption' => $useConsumption
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:55',
                'code' => 'required|string|max:55|unique:items,code,' . $id,
                'type' => 'required|in:Product,Service,Package',
                'category_id' => 'required|exists:categories,id',
                'loyalty_point' => 'nullable|numeric|min:0',
                'status' => 'required|in:Enable,Disable',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp',
                'description' => 'nullable|string',
                'use_consumption' => 'nullable|boolean',
                'taxes' => 'required|array',
                'taxes.*.tax' => 'required|string',
                'taxes.*.tax_rate' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validatedData = $validator->validated();

            // Type specific validation
            if ($request->type === 'Product') {
                $productValidator = Validator::make($request->all(), [
                    'unit_id' => 'required|exists:units,id',
                    'purchase_price' => 'required|numeric|min:0',
                    'sale_price' => 'required|numeric|min:0',
                    'supplier_id' => 'nullable'
                ]);

                if ($productValidator->fails()) {
                    return $this->errorResponse($productValidator->errors(), 422);
                }
            }

            if ($request->type === 'Service') {
                // Service validation - simplified without consumption
                $serviceValidator = Validator::make($request->all(), [
                    'duration' => 'nullable|numeric',
                    'duration_type' => 'nullable|in:Day,Hour,Minute',
                    'sale_price' => 'nullable|numeric|min:0'
                ]);

                if ($serviceValidator->fails()) {
                    return $this->errorResponse($serviceValidator->errors(), 422);
                }
            }

            if ($request->type === 'Package') {
                $packageValidator = Validator::make($request->all(), [
                    'sale_price' => 'required|numeric|min:0',
                    'duration' => 'nullable|numeric',
                    'duration_type' => 'nullable|in:Day,Hour,Minute',
                    'itemRows' => 'required|array|min:1',
                    'itemRows.*.item_id' => 'required|exists:items,id',
                    'itemRows.*.price' => 'required|numeric|min:0',
                    'itemRows.*.quantity' => 'required|numeric|min:1',
                    'itemRows.*.discount' => 'nullable|numeric|min:0',
                    'itemRows.*.total_price' => 'required|numeric|min:0'
                ]);

                if ($packageValidator->fails()) {
                    return $this->errorResponse($packageValidator->errors(), 422);
                }
            }

            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $item->photo, 'items');
            }

            DB::beginTransaction();

            // Update item
            $item->update([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
                'duration' => $request->duration,
                'duration_type' => $request->duration_type === "null" ? null : $request->duration_type,
                'category_id' => (int)$request->category_id,
                'loyalty_point' => $request->loyalty_point,
                'status' => $request->status,
                'profit_margin' => $request->profit_margin ?? 0,
                'photo' => $validatedData['photo'] ?? $item->photo,
                'description' => $request->description,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
                'unit_id' => (int)$request->unit_id,
                'supplier_id' => (int)$request->supplier_id,
                'tax_information' => json_encode($request->taxes),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
                'updated_at' => now()
            ]);

            // Update item rows for Service and Package types
            if ($request->type === 'Package') {
                // Delete existing item details
                $item->itemDetails()->delete();
                
                // Create new item details if itemRows exist
                if (!empty($request->itemRows)) {
                    foreach ($request->itemRows as $row) {
                        $item->itemDetails()->create([
                            'item_relation_id' => $item->id,
                            'item_id' => $row['item_id'],
                            'unit_id' => $row['unit'] ?? null,
                            'user_id' => Auth::id(),
                            'company_id' => Auth::user()->company_id,
                        ] + $row);
                    }
                }
            } else {
                // If product, delete any existing item details
                $item->itemDetails()->delete();
            }

            DB::commit();
            return $this->successResponse($item, 'Item updated successfully');

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
        $item = Item::find($id);
        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        try {
            // Delete photo if exists
            if ($item->photo) {
                $this->delete($item->photo);
            }

            // Soft delete by updating del_status
            $item->update([
                'del_status' => 'Deleted'
            ]);

            // Soft delete item details
            $item->itemDetails()->update(['del_status' => 'Deleted']);

            return $this->successResponse(null, 'Item deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get item details by ID
     */
    public function getItemById($id)
    {
        try {
            $item = Item::where('id', $id)
                       ->where('del_status', 'Live')
                       ->first();

            if (!$item) {
                return $this->errorResponse('Item not found', 404);
            }

            return $this->successResponse($item);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get item details: ' . $e->getMessage());
        }
    }
}
