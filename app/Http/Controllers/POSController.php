<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Booking;
use App\Models\Company;
use App\Models\SaleDetail;
use App\Traits\ApiResponse;
use App\Services\NotificationService;
use App\Services\LoyaltyPointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    use ApiResponse;

    protected $notificationService;
    protected $loyaltyPointService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->loyaltyPointService = new LoyaltyPointService();
    }

    public function allItems()
    {
        $items = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->get();
        
        return $this->successResponse($items, 'Items fetched successfully');
    }

    public function getItemById($id)
    {
        try {
            $item = Item::where('id', $id)
                       ->where('company_id', Auth::user()->company_id)
                       ->where('del_status', 'Live')
                       ->where('status', 'Enable')
                       ->first();

            if (!$item) {
                return $this->errorResponse('Item not found', 404);
            }

            return $this->successResponse($item);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get item details: ' . $e->getMessage());
        }
    }

    public function saveOrder(Request $request)
    {
        $company = Company::find(Auth::user()->company_id);
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.employee_id' => 'nullable',
            'items.*.is_free' => 'required|in:Yes,No',
            'items.*.promotion_id' => 'nullable|exists:promotions,id',
            'items.*.promotion_discount' => 'nullable|numeric|min:0',
            'items.*.tips' => 'nullable|array',
            'items.*.tips.employeeId' => 'nullable|exists:users,id',
            'items.*.tips.amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'tax_breakdown' => 'nullable|array',
            'discount' => 'required|numeric|min:0',
            'promotionDiscount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_amount' => 'required|numeric|min:0',
            'due_amount' => 'nullable|numeric',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'nullable|date',
            'transaction_id' => 'nullable|string',
            'total_tips' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            // Generate reference number
            $lastSale = Sale::where('company_id', Auth::user()->company_id)
                           ->where('del_status', 'Live')
                           ->orderBy('id', 'desc')
                           ->first();
            $nextId = $lastSale ? $lastSale->id + 1 : 1;
            $referenceNo = 'S-' . date('Ymd') . str_pad($nextId, 4, '0', STR_PAD_LEFT);


            $tax_type = $company->tax_type;
            if($tax_type == 'Inclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'] + $validatedData['discount'];
            }else if($tax_type == 'Exclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'] + $validatedData['tax'] + $validatedData['discount'];
            }

            // Calculate loyalty points
            $loyaltyPointsEarned = 0;
            $loyaltyPointsRedeemed = 0;
            $loyaltyPointsValue = 0;
            
            if ($request->customer_id) {
                // Calculate loyalty points earned
                $loyaltyPointsEarned = $this->loyaltyPointService->calculateLoyaltyPointsEarned($validatedData['items'], $request->customer_id);
                
                // Check if loyalty points are being used for payment
                if($request->payment_method_id) {
                    if ($this->loyaltyPointService->canUseLoyaltyPoints($validatedData['payment_method_id'])) {
                        $customer = \App\Models\Customer::find($request->customer_id);
                        if ($customer && $customer->name !== 'Walk-in Customer' && $customer->name !== 'Walking-in Customer') {
                            $loyaltyPointsNeeded = $this->loyaltyPointService->calculateLoyaltyPointsNeeded($validatedData['total'], $request->customer_id);
                            
                            if ($this->loyaltyPointService->hasEnoughLoyaltyPoints($request->customer_id, $loyaltyPointsNeeded)) {
                                $loyaltyPointsRedeemed = $loyaltyPointsNeeded;
                                $loyaltyPointsValue = $this->loyaltyPointService->calculateLoyaltyPointValue($loyaltyPointsRedeemed, Auth::user()->company_id);
                                
                                // Redeem loyalty points
                                $this->loyaltyPointService->redeemLoyaltyPoints($request->customer_id, $loyaltyPointsRedeemed);
                            }
                        }
                    }
                }

            }

            // Create sale record
            $sale = Sale::create([
                'reference_no' => $referenceNo,
                'order_date' => $validatedData['order_date'] ?? now()->format('Y-m-d H:i:s'),
                'order_from' => 'POS',
                'order_status' => 'Completed',
                'subtotal_without_tax_discount' => $validatedData['subtotal'],
                'grandtotal_with_tax_discount' => $grandtotal_with_tax_discount,
                'discount' => $validatedData['discount'],
                'promotion_discount' => $validatedData['promotionDiscount'] ?? 0,
                'total_tax' => $validatedData['tax'],
                'tax_breakdown' => !empty($validatedData['tax_breakdown']) ? json_encode($validatedData['tax_breakdown']) : null,
                'total_payable' => $validatedData['total'],
                'total_paid' => $validatedData['payment_amount'],
                'total_due' => $validatedData['due_amount'],
                'total_tips' => $validatedData['total_tips'],
                'customer_id' => $request->customer_id ?? null, // Use customer_id from request
                'payment_method_id' => $validatedData['payment_method_id'] ?? null,
                'transaction_id' => $validatedData['transaction_id'] ?? null,
                'branch_id' => $validatedData['branch_id'],
                'user_id' => $request->user_id ?? Auth::id(), // Use user_id from request or fallback
                'company_id' => Auth::user()->company_id,
                'loyalty_points_earned' => $loyaltyPointsEarned,
                'loyalty_points_redeemed' => $loyaltyPointsRedeemed,
                'loyalty_points_value' => $loyaltyPointsValue,
                'del_status' => 'Live',
            ]);

            // Create sale details
            foreach ($validatedData['items'] as $item) {
                // Get item details from database
                $itemRecord = Item::find($item['id']);
                if (!$itemRecord) {
                    throw new \Exception('Item not found: ' . $item['id']);
                }

                $unitPrice = $item['price'];
                $quantity = $item['qty'];
                $subtotal = $unitPrice * $quantity;
                
                if ($item['is_free'] == 'Yes') {
                    $itemTax = 0; // Free items are not taxed
                    $itemTotal = 0; // Free items have no cost
                    $itemTaxBreakdown = null;
                } else {
                    // Calculate tax proportionally for non-free items (after promotion discount)
                    $itemSubtotalAfterPromotion = $subtotal - ($item['promotion_discount'] ?? 0);
                    $itemTax = $itemSubtotalAfterPromotion * ($validatedData['tax'] / $validatedData['subtotal']);

                    // Calculate item-specific tax breakdown
                    $itemTaxBreakdown = [];
                    if (!empty($validatedData['tax_breakdown'])) {
                        foreach ($validatedData['tax_breakdown'] as $taxName => $totalTaxAmount) {
                            $itemTaxBreakdown[$taxName] = $itemSubtotalAfterPromotion * ($totalTaxAmount / $validatedData['subtotal']);
                        }
                    }

                    if($tax_type == 'Inclusive'){
                        $itemTotal = $itemSubtotalAfterPromotion;
                    }else if($tax_type == 'Exclusive'){
                        $itemTotal = $itemSubtotalAfterPromotion + $itemTax;
                    }
                }


                $employee_id = NULL;
                $tipsAmount = 0;

                if(is_array($item['tips'])){
                    $tips = $item['tips'];
                    $tipsAmount = $tips['amount'];
                    $tipsEmployeeId = $tips['employeeId'];

                    if($tipsEmployeeId){
                        $employee_id = $tipsEmployeeId;
                    }else {
                        $employee_id = $item['employee_id'];
                    }
                } else {
                    $employee_id = $item['employee_id'];
                }


                // Prepare sale detail data
                $saleDetailData = [
                    'sale_id' => $sale->id,
                    'item_id' => $item['id'],
                    'employee_id' => $employee_id,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotalAfterPromotion,
                    'total_tax' => $itemTax,
                    'tax_breakdown' => !empty($itemTaxBreakdown) ? json_encode($itemTaxBreakdown) : null,
                    'total_payable' => $itemTotal,
                    'promotion_discount' => $item['promotion_discount'] ?? 0,
                    'is_free' => $item['is_free'],
                    'loyalty_point_earn' => $itemRecord->loyalty_point ?? null,
                    'promotion_id' => $item['promotion_id'] ?? null,
                    'tips' => $tipsAmount,
                    'user_id' => $request->user_id ?? Auth::id(),
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => $validatedData['branch_id'],
                    'del_status' => 'Live',
                ];
                
                SaleDetail::create($saleDetailData);
            }
            
            // Add loyalty points to customer if earned
            if ($loyaltyPointsEarned > 0 && $request->customer_id) {
                $this->loyaltyPointService->addLoyaltyPoints($request->customer_id, $loyaltyPointsEarned);
            }
            
            // If booking_id is present, update booking status to Completed
            if ($request->has('booking_id')) {
                $booking = \App\Models\Booking::find($request->booking_id);
                if ($booking) {
                    $booking->status = 'Completed';
                    $booking->save();
                }
            }
            DB::commit();

            $notificationData = [
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'send_whatsapp' => $request->send_whatsapp ?? false,
                'sale_id' => $sale->id,
            ];
            $notificationResult = $this->notificationService->sendNotifications($notificationData, 'Sale');
            
            if ($notificationResult['status'] === 'Success') {
                return $this->successResponse([
                    'order_id' => $sale->id,
                    'reference_no' => $referenceNo,
                    'total_amount' => $validatedData['total'],
                    'payment_amount' => $validatedData['payment_amount'],
                ], 'Order saved successfully');
            } else {
                return $this->successResponse([
                    'order_id' => $sale->id,
                    'reference_no' => $referenceNo,
                    'total_amount' => $validatedData['total'],
                    'payment_amount' => $validatedData['payment_amount'],
                ], 'Order saved successfully but ' . $notificationResult['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to save order: ' . $e->getMessage());
        }
    }

    public function getOrderDetails($order_id)
    {

        $order = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->leftJoin('companies', 'sales.company_id', '=', 'companies.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('payment_methods', 'sales.payment_method_id', '=', 'payment_methods.id')
            ->where('sales.id', $order_id)
            ->select(
                'sales.*',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'users.name as user_name',
                'payment_methods.name as payment_method_name',
                'branches.branch_name as branch_name',
                'branches.address as branch_address',
                'companies.name as company_name',
                'companies.logo as company_logo',
                'companies.address as company_address'
            )
            ->first();

        $saleDetails = DB::table('sale_details')
            ->leftJoin('items', 'sale_details.item_id', '=', 'items.id')
            ->where('sale_id', $order_id)
            ->select(
                'sale_details.*',
                'items.name as item_name',
                'items.code as item_code'
            )
            ->get();
        $order->sale_details = $saleDetails;

        return $this->successResponse($order, 'Order details fetched successfully');
    }

    public function getBookingListForPOS(Request $request)
    {
        $query = Booking::query();

        $query->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('branches', 'bookings.branch_id', '=', 'branches.id')
            ->select('bookings.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'branches.branch_name')
            ->where('bookings.company_id', Auth::user()->company_id)
            ->where('bookings.status', '!=', 'Completed')
            ->where('bookings.del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function ($q) use ($request) {
                $q->where('bookings.reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('customers.name', 'like', '%' . $request->q . '%')
                ->orWhere('customers.phone', 'like', '%' . $request->q . '%')
                ->orWhere('bookings.date', 'like', '%' . $request->q . '%')
                ->orWhere('branches.branch_name', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if (!empty($request->sortBy)) {
            $direction = $request->orderBy === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('bookings.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $bookings = $query->paginate($perPage);

        return $this->successResponse([
            'bookings' => $bookings->items(),
            'total' => $bookings->total(),
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'per_page' => $bookings->perPage(),
        ]);
    }

    public function getSaleForEdit($sale_id)
    {
        try {
            // Get sale with related data
            $sale = Sale::where('id', $sale_id)
                ->where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->first();

            if (!$sale) {
                return $this->errorResponse('Sale not found', 404);
            }

            // Get sale details with item information
            $saleDetails = DB::table('sale_details')
                ->leftJoin('items', 'sale_details.item_id', '=', 'items.id')
                ->leftJoin('users', 'sale_details.employee_id', '=', 'users.id')
                ->where('sale_details.sale_id', $sale_id)
                ->where('sale_details.del_status', 'Live')
                ->select(
                    'sale_details.*',
                    'items.name as item_name',
                    'items.code as item_code',
                    'items.type as item_type',
                    'items.photo as item_photo',
                    'users.name as employee_name'
                )
                ->get();

            // Transform sale details to match POS format
            $transformedItems = [];
            foreach ($saleDetails as $detail) {
                $transformedItems[] = [
                    'id' => $detail->item_id,
                    'name' => $detail->item_name,
                    'code' => $detail->item_code,
                    'type' => $detail->item_type,
                    'photo' => $detail->item_photo,
                    'qty' => $detail->quantity,
                    'price' => $detail->unit_price,
                    'sale_price' => $detail->unit_price,
                    'employee_id' => $detail->employee_id,
                    'employee_name' => $detail->employee_name,
                    'is_free' => $detail->is_free,
                    'promotion_id' => $detail->promotion_id,
                    'subtotal' => $detail->subtotal,
                    'total_tax' => $detail->total_tax,
                    'total_payable' => $detail->total_payable,
                    'tips' => $detail->tips ? json_decode($detail->tips, true) : null,
                ];
            }

            // Get customer information
            $customer = null;
            if ($sale->customer_id) {
                $customer = DB::table('customers')
                    ->where('id', $sale->customer_id)
                    ->select('id', 'name', 'phone', 'email')
                    ->first();
            }

            // Get employee information
            $employee = null;
            if ($sale->user_id) {
                $employee = DB::table('users')
                    ->where('id', $sale->user_id)
                    ->select('id', 'name', 'email')
                    ->first();
            }

            $response = [
                'sale' => $sale,
                'items' => $transformedItems,
                'customer' => $customer,
                'employee' => $employee,
            ];

            return $this->successResponse($response, 'Sale data fetched successfully for editing');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch sale data: ' . $e->getMessage());
        }
    }

    public function updateOrder(Request $request, $sale_id)
    {
        $company = Company::find(Auth::user()->company_id);
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.employee_id' => 'nullable',
            'items.*.is_free' => 'required|in:Yes,No',
            'items.*.promotion_id' => 'nullable|exists:promotions,id',
            'items.*.promotion_discount' => 'nullable|numeric|min:0',
            'items.*.tips' => 'nullable|array',
            'items.*.tips.employee_id' => 'nullable|exists:users,id',
            'items.*.tips.amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'tax_breakdown' => 'nullable|array',
            'discount' => 'required|numeric|min:0',
            'promotionDiscount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_amount' => 'required|numeric|min:0',
            'due_amount' => 'nullable|numeric',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if sale exists and belongs to the company
        $sale = Sale::where('id', $sale_id)
            ->where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->first();

        if (!$sale) {
            return $this->errorResponse('Sale not found', 404);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            $tax_type = $company->tax_type;
            if($tax_type == 'Inclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'] + $validatedData['discount'];
            }else if($tax_type == 'Exclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'] + $validatedData['tax'] + $validatedData['discount'];
            }

            // Handle loyalty points for edit mode
            $customerId = $request->customer_id ?? $sale->customer_id;
            $loyaltyPointsEarned = 0;
            $loyaltyPointsRedeemed = 0;
            $loyaltyPointsValue = 0;
            
            if ($customerId) {
                // Calculate new loyalty points earned
                $loyaltyPointsEarned = $this->loyaltyPointService->calculateLoyaltyPointsEarned($validatedData['items'], $customerId);
                
                // Check if loyalty points are being used for payment
                if($request->payment_method_id){
                    if ($this->loyaltyPointService->canUseLoyaltyPoints($validatedData['payment_method_id'])) {
                        $customer = \App\Models\Customer::find($customerId);
                        if ($customer && $customer->name !== 'Walk-in Customer' && $customer->name !== 'Walking-in Customer') {
                            $loyaltyPointsNeeded = $this->loyaltyPointService->calculateLoyaltyPointsNeeded($validatedData['total'], $customerId);
                            
                            if ($this->loyaltyPointService->hasEnoughLoyaltyPoints($customerId, $loyaltyPointsNeeded)) {
                                $loyaltyPointsRedeemed = $loyaltyPointsNeeded;
                                $loyaltyPointsValue = $this->loyaltyPointService->calculateLoyaltyPointValue($loyaltyPointsRedeemed, Auth::user()->company_id);
                                
                                // Redeem loyalty points
                                $this->loyaltyPointService->redeemLoyaltyPoints($customerId, $loyaltyPointsRedeemed);
                            }
                        }
                    }
                }
                
                // Adjust customer loyalty points (remove old earned points and add new ones)
                if ($sale->loyalty_points_earned > 0) {
                    $this->loyaltyPointService->redeemLoyaltyPoints($customerId, $sale->loyalty_points_earned);
                }
                if ($loyaltyPointsEarned > 0) {
                    $this->loyaltyPointService->addLoyaltyPoints($customerId, $loyaltyPointsEarned);
                }
            }

            // Update sale record
            $sale->update([
                'order_date' => $validatedData['order_date'] ?? $sale->order_date,
                'subtotal_without_tax_discount' => $validatedData['subtotal'],
                'grandtotal_with_tax_discount' => $grandtotal_with_tax_discount,
                'discount' => $validatedData['discount'],
                'promotion_discount' => $validatedData['promotionDiscount'] ?? 0,
                'total_tax' => $validatedData['tax'],
                'tax_breakdown' => !empty($validatedData['tax_breakdown']) ? json_encode($validatedData['tax_breakdown']) : null,
                'total_payable' => $validatedData['total'],
                'total_paid' => $validatedData['payment_amount'],
                'total_due' => $validatedData['due_amount'],
                'customer_id' => $request->customer_id ?? $sale->customer_id,
                'payment_method_id' => $request->payment_method_id ?? $sale->payment_method_id,
                'branch_id' => $validatedData['branch_id'],
                'user_id' => $request->user_id ?? $sale->user_id,
                'loyalty_points_earned' => $loyaltyPointsEarned,
                'loyalty_points_redeemed' => $loyaltyPointsRedeemed,
                'loyalty_points_value' => $loyaltyPointsValue,
            ]);

            // Delete existing sale details
            SaleDetail::where('sale_id', $sale_id)->delete();

            // Create new sale details
            foreach ($validatedData['items'] as $item) {
                // Get item details from database
                $itemRecord = Item::find($item['id']);
                if (!$itemRecord) {
                    throw new \Exception('Item not found: ' . $item['id']);
                }

                $unitPrice = $item['price'];
                $quantity = $item['qty'];
                $subtotal = $unitPrice * $quantity;
                
                if ($item['is_free'] == 'Yes') {
                    $itemTax = 0; // Free items are not taxed
                    $itemTotal = 0; // Free items have no cost
                    $itemTaxBreakdown = null;
                } else {
                    // Calculate tax proportionally for non-free items (after promotion discount)
                    $itemSubtotalAfterPromotion = $subtotal - ($item['promotion_discount'] ?? 0);
                    $itemTax = $itemSubtotalAfterPromotion * ($validatedData['tax'] / $validatedData['subtotal']);

                    // Calculate item-specific tax breakdown
                    $itemTaxBreakdown = [];
                    if (!empty($validatedData['tax_breakdown'])) {
                        foreach ($validatedData['tax_breakdown'] as $taxName => $totalTaxAmount) {
                            $itemTaxBreakdown[$taxName] = $itemSubtotalAfterPromotion * ($totalTaxAmount / $validatedData['subtotal']);
                        }
                    }

                    if($tax_type == 'Inclusive'){
                        $itemTotal = $itemSubtotalAfterPromotion;
                    }else if($tax_type == 'Exclusive'){
                        $itemTotal = $itemSubtotalAfterPromotion + $itemTax;
                    }
                }

                // Prepare sale detail data
                $saleDetailData = [
                    'sale_id' => $sale->id,
                    'item_id' => $item['id'],
                    'employee_id' => $item['employee_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'total_tax' => $itemTax,
                    'tax_breakdown' => !empty($itemTaxBreakdown) ? json_encode($itemTaxBreakdown) : null,
                    'total_payable' => $itemTotal,
                    'promotion_discount' => $item['promotion_discount'] ?? 0,
                    'is_free' => $item['is_free'],
                    'loyalty_point_earn' => $itemRecord->loyalty_point ?? null,
                    'promotion_id' => $item['promotion_id'] ?? null,
                    'tips' => !empty($item['tips']) ? json_encode($item['tips']) : null,
                    'user_id' => $request->user_id ?? Auth::id(),
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => $validatedData['branch_id'],
                    'del_status' => 'Live',
                ];
                
                SaleDetail::create($saleDetailData);
            }

            DB::commit();

            return $this->successResponse([
                'order_id' => $sale->id,
                'reference_no' => $sale->reference_no,
                'total_amount' => $validatedData['total'],
                'payment_amount' => $validatedData['payment_amount'],
            ], 'Order updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update order: ' . $e->getMessage());
        }
    }

}
