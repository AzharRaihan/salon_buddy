<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Purchase::query();

        // Join with supplier
        $query->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
              ->select('purchases.*', 'suppliers.name as supplier_name', 'suppliers.phone as supplier_phone');

        // Filter by del_status
        $query->where('purchases.del_status', 'Live');
        $query->where('purchases.branch_id', $request->branch_id);
        $query->where('purchases.company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('purchases.reference_no', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('purchases.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $purchases = $query->paginate($perPage);

        return $this->successResponse([
            'purchases' => $purchases->items(),
            'total' => $purchases->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|unique:purchases',
                'supplier_id' => 'required|exists:suppliers,id',
                'date' => 'required|date',
                'items' => 'required|json',
                'grand_total' => 'required|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0|lte:grand_total',
                // 'payment_method_id' => 'required_if:paid_amount,>,0|exists:payment_methods,id',
                'attachment' => 'nullable|mimes:jpg,jpeg,png,pdf,xls,xlsx,doc,docx|max:2048',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validatedData = $validator->validated();

            // Calculate due amount
            $dueAmount = $request->grand_total - $request->paid_amount;

            // Decode JSON items
            $items = json_decode($request->items, true);

            // Validate items array
            foreach ($items as $item) {
                $itemValidator = Validator::make($item, [
                    'item_id' => 'required|exists:items,id',
                    'quantity' => 'required|numeric|min:1',
                    'unit_price' => 'required|numeric|min:0',
                ]);

                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }

            if ($request->hasFile('attachment')) {
                $validatedData['attachment'] = $this->imageUpload($request->file('attachment'), null, 'purchases');
            }

            DB::beginTransaction();

            // Create purchase
            $purchase = Purchase::create([
                'reference_no' => $request->reference_no,
                'supplier_invoice_no' => $request->supplier_invoice_no,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'grand_total' => $request->grand_total,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'attachment' => $validatedData['attachment'] ?? null,
                'note' => $request->note,
                'payment_method_id' => $request->payment_method_id,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Create purchase items
            foreach ($items as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);


                $lastPrice = $item['unit_price'];
                $lastThreePrices = PurchaseDetail::where('item_id', $item['item_id'])
                    ->latest()
                    ->limit(3)
                    ->pluck('unit_price');

                $avgPrice = $lastThreePrices->avg();

                Item::where('id', $item['item_id'])->update([
                    'last_purchase_price' => $lastPrice,
                    'last_three_purchase_avg' => $avgPrice,
                ]);

            }
            DB::commit();
            return $this->successResponse($purchase, 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['purchaseDetails', 'purchaseDetails.item:id,name', 'supplier:id,name', 'paymentMethod:id,name'])->find($id);
        if (!$purchase) {
            return $this->errorResponse('Purchase not found', 404);
        }
        return $this->successResponse($purchase);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Find the purchase
            $purchase = Purchase::find($id);
            if (!$purchase) {
                return $this->errorResponse('Purchase not found', 404);
            }
            // Validate request
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|unique:purchases,reference_no,' . $id,
                'supplier_id' => 'required|exists:suppliers,id',
                'date' => 'required|date',
                'items' => 'required|json',
                'grand_total' => 'required|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0|lte:grand_total',
                // 'payment_method_id' => 'required_if:paid_amount,>,0|exists:payment_methods,id',
                'attachment' => 'nullable|mimes:jpg,jpeg,png,pdf,xls,xlsx,doc,docx|max:2048',
                'note' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $validatedData = $validator->validated();
            // Calculate due amount
            $dueAmount = $request->grand_total - $request->paid_amount;
            // Decode JSON items
            $items = json_decode($request->items, true);
            // Validate items array
            foreach ($items as $item) {
                $itemValidator = Validator::make($item, [
                    'item_id' => 'required|exists:items,id',
                    'quantity' => 'required|numeric|min:1',
                    'unit_price' => 'required|numeric|min:0',
                ]);
                if ($itemValidator->fails()) {
                    return $this->errorResponse($itemValidator->errors(), 422);
                }
            }
            if ($request->hasFile('attachment')) {
                $validatedData['attachment'] = $this->imageUpload($request->file('attachment'), $purchase->attachment, 'purchases');
            }

            DB::beginTransaction();

            // Update purchase
            $purchase->update([
                'reference_no' => $request->reference_no,
                'supplier_invoice_no' => $request->supplier_invoice_no,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'grand_total' => $request->grand_total,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'attachment' => $validatedData['attachment'] ?? $purchase->attachment,
                'note' => $request->note,
                'payment_method_id' => $request->payment_method_id,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::user()->id,
                'updated_at' => now(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Delete old purchase items
            PurchaseDetail::where('purchase_id', $purchase->id)->delete();

            // Create new purchase items
            foreach ($items as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            DB::commit();
            return $this->successResponse($purchase, 'Purchase updated successfully');
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
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return $this->errorResponse('Purchase not found', 404);
        }
        DB::beginTransaction();
        try {
            // Delete purchase details
            PurchaseDetail::where('purchase_id', $id)->update([
                'del_status' => 'Deleted'
            ]);
            // Delete main purchase
            $purchase->update([
                'del_status' => 'Deleted'
            ]);
            DB::commit();
            return $this->successResponse(null, 'Purchase deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
