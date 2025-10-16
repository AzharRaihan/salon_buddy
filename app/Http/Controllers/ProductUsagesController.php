<?php

namespace App\Http\Controllers;

use App\Models\ProductUsages;
use App\Models\ProductUsageDetail;
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
        $query = ProductUsages::query();

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Filter by branch if provided
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->q . '%')
                  ->orWhere('note', 'like', '%' . $request->q . '%');
            });
        }

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

        $validator = Validator::make($request->all(), [
            'reference_no' => 'required|string|unique:product_usages',
            'date' => 'required|date',
            'items' => 'required|json',
            'branch_id' => 'required|integer|exists:branches,id',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Decode items
        $items = json_decode($request->items, true);
        if (empty($items)) {
            return $this->errorResponse('At least one item is required', 422);
        }
        // Validate items array
        foreach ($items as $item) {
            $itemValidator = Validator::make($item, [
                'item_id' => 'required|exists:items,id',
                'quantity' => 'required|numeric|min:1',
                'note' => 'nullable|string|max:255',
                'employee_id' => 'required|integer|exists:users,id',
            ]);

            if ($itemValidator->fails()) {
                return $this->errorResponse($itemValidator->errors(), 422);
            }
        }


        DB::beginTransaction();
        try {
            // Create product usage
            $usage = ProductUsages::create([
                'reference_no' => $request->reference_no,
                'date' => $request->date,
                'note' => $request->note,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Create product usage details
            foreach ($items as $item) {
                ProductUsageDetail::create([
                    'product_usage_id' => $usage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'note' => $item['note'],
                    'employee_id' => $item['employee_id'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                ]);
            }
            
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
    public function show(string $id)
    {
        $usage = ProductUsages::with([
                'productUsageDetails.item:id,name,code,type',
                'productUsageDetails.employee:id,name'
            ])
            ->where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->first();
        
        if (!$usage) {
            return $this->errorResponse('Product usage not found', 404);
        }
        
        return $this->successResponse($usage);
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
            'reference_no' => 'required|string|max:55|unique:product_usages,reference_no,' . $id,
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
            'branch_id' => 'required|integer|exists:branches,id',
            'items' => 'required|json',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Decode items
        $items = json_decode($request->items, true);
        if (empty($items)) {
            return $this->errorResponse('At least one item is required', 422);
        }

        // Validate each item
        foreach ($items as $item) {
            if (empty($item['item_id']) || empty($item['quantity']) || empty($item['employee_id'])) {
                return $this->errorResponse('Invalid item data. Item, quantity, and employee are required.', 422);
            }
        }

        DB::beginTransaction();
        try {

            // Delete old details
            ProductUsageDetail::where('product_usage_id', $usage->id)->delete();

            // Update usage
            $usage->update([
                'reference_no' => $request->reference_no,
                'date' => $request->date,
                'note' => $request->note,
                'branch_id' => $request->branch_id,
                'updated_at' => now(),
            ]);

            // Create new usage details and update stock
            foreach ($items as $item) {
                ProductUsageDetail::create([
                    'product_usage_id' => $usage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'note' => $item['note'],
                    'employee_id' => $item['employee_id'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                ]);
            }
            
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
        
        DB::beginTransaction();
        try {
            // Restore stock quantities
            $details = ProductUsageDetail::where('product_usage_id', $usage->id)->get();
            foreach ($details as $detail) {
                $itemRecord = Item::find($detail->item_id);
                if ($itemRecord && $itemRecord->type == 'Product') {
                    $itemRecord->increment('quantity', $detail->quantity);
                }
            }

            // Soft delete details
            ProductUsageDetail::where('product_usage_id', $usage->id)
                ->update(['del_status' => 'Deleted']);

            // Soft delete usage
            $usage->update(['del_status' => 'Deleted']);
            
            DB::commit();
            return $this->successResponse(null, 'Product usage deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Generate reference number
     */
    public function generateReferenceNo()
    {
        $lastUsage = ProductUsages::where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastUsage) {
            $lastNumber = (int) substr($lastUsage->reference_no, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $referenceNo = 'PU-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        
        return $this->successResponse($referenceNo);
    }
}
