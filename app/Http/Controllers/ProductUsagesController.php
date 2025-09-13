<?php

namespace App\Http\Controllers;

use App\Models\ProductUsages;
use App\Models\Item;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductUsagesController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductUsages::with(['item:id,name,code,type']);

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('code', 'like', '%' . $request->q . '%');
            });
        }

        // Filter by item type (Product only)
        $query->whereHas('item', function($q) {
            $q->where('type', 'Product');
        });

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('product_usages.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $usages = $query->paginate($perPage);

        return $this->successResponse([
            'usages' => $usages->items(),
            'total' => $usages->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'usage_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Verify item is a Product type
        $item = Item::where('id', $request->item_id)
                   ->where('type', 'Product')
                   ->where('company_id', Auth::user()->company_id)
                   ->first();

        if (!$item) {
            return $this->errorResponse('Invalid product selected', 422);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            
            $usage = ProductUsages::create($validatedData);
            
            DB::commit();
            return $this->successResponse($usage, 'Product usage recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductUsages $usage)
    {
        // $usage = ProductUsages::with(['item:id,name,code,type'])
        //                      ->where('id', $id)
        //                      ->where('company_id', Auth::user()->company_id)
        //                      ->first();
        
        // if (!$usage) {
        //     return $this->errorResponse('Product usage not found', 404);
        // }
        
        // return $this->successResponse($usage);

        return $this->successResponse([
            'item' => $usage->load(
                                'item:id,name,code,type',
                                'user:id,name'
                            )
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usage = ProductUsages::where('id', $id)
                             ->where('company_id', Auth::user()->company_id)
                             ->first();
        
        if (!$usage) {
            return $this->errorResponse('Product usage not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'usage_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Verify item is a Product type
        $item = Item::where('id', $request->item_id)
                   ->where('type', 'Product')
                   ->where('company_id', Auth::user()->company_id)
                   ->first();

        if (!$item) {
            return $this->errorResponse('Invalid product selected', 422);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            
            $usage->update($validatedData);
            
            DB::commit();
            return $this->successResponse($usage, 'Product usage updated successfully');
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
        $usage = ProductUsages::where('id', $id)
                             ->where('company_id', Auth::user()->company_id)
                             ->first();
        
        if (!$usage) {
            return $this->errorResponse('Product usage not found', 404);
        }
        
        try {
            $usage->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Product usage deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get products for dropdown
     */
    public function getProducts()
    {
        $products = Item::where('type', 'Product')
                       ->where('company_id', Auth::user()->company_id)
                       ->where('del_status', 'Live')
                       ->where('status', 'Enable')
                       ->select('id', 'name', 'code')
                       ->orderBy('name')
                       ->get();

        return $this->successResponse($products);
    }
}
