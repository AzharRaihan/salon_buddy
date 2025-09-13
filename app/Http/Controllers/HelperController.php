<?php

namespace App\Http\Controllers;

use DateTimeZone;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Models\Branch;
use App\Models\Damage;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\CustomerReceive;
use App\Models\ExpenseCategory;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\Auth;
use App\Services\LoyaltyPointService;

class HelperController extends Controller
{
    use ApiResponse;

    public function getCompanyInfo()
    {
        if(Auth::user()){
            $company_id = Auth::user()->company_id;
        }else{
            $company_id = 1;
        }
        $companyInfo = Company::where('id', $company_id)->where('del_status', 'Live')->first();
        return $this->successResponse($companyInfo, 'Company info fetched successfully');
    }
    public function getBranchList()
    {
        $branchList = Branch::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->get();
        return $this->successResponse($branchList, 'Branch list fetched successfully');
    }
    public function generateBranchCode()
    {
        $lastBranch = Branch::where('company_id', Auth::user()->company_id)->orderBy('id', 'desc')->first();
        $nextId = $lastBranch ? $lastBranch->id + 1 : 1;
        $branch_code = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($branch_code, 'Branch code generated successfully');
    }

    public function generateItemCode()
    {
        $item = Item::where('company_id', Auth::user()->company_id)->orderBy('id', 'desc')->first();
        $nextId = $item ? $item->id + 1 : 1;
        $item_code = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($item_code, 'Item code generated successfully');
    }

    public function getCategoryList()
    {
        $categoryList = Category::where('company_id', Auth::user()->company_id)->where('status', 'Enabled')->where('del_status', 'Live')->get();
        return $this->successResponse($categoryList, 'Category list fetched successfully');
    }
    public function getServiceCategoryList()
    {
        $categoryList = Category::where('company_id', Auth::user()->company_id)->where('status', 'Enabled')->where('del_status', 'Live')->where('type', 'Service')->get();
        return $this->successResponse($categoryList, 'Category list fetched successfully');
    }
    public function getUnitList()
    {
        $unitList = Unit::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->get();
        return $this->successResponse($unitList, 'Unit list fetched successfully');
    }
    public function getUserList()
    {
        $userList = User::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->select('id', 'name', 'phone')->get();
        return $this->successResponse($userList, 'User list fetched successfully');
    }

    public function generateExpenseReferenceNo()
    {
        $lastExpense = Expense::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
        $nextId = $lastExpense ? $lastExpense->id + 1 : 1;
        $expense_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($expense_reference_no, 'Expense reference no generated successfully');
    }
    public function getExpenseCategoryList()
    {
        $expenseCategoryList = ExpenseCategory::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->get();
        return $this->successResponse($expenseCategoryList, 'Expense category list fetched successfully');
    }
    public function generatePurchaseReferenceNo()
    {
        $lastPurchase = Purchase::where('company_id', Auth::user()->company_id)->orderBy('id', 'desc')->first();
        $nextId = $lastPurchase ? $lastPurchase->id + 1 : 1;
        $purchase_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($purchase_reference_no, 'Purchase reference no generated successfully');
    }
    public function generateDamageReferenceNo()
    {
        $lastDamage = Damage::where('company_id', Auth::user()->company_id)->orderBy('id', 'desc')->first();
        $nextId = $lastDamage ? $lastDamage->id + 1 : 1;
        $damage_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($damage_reference_no, 'Damage reference no generated successfully');
    }
    public function customerReceiveReferenceNo()
    {
        $lastCustomerReceive = CustomerReceive::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
        $nextId = $lastCustomerReceive ? $lastCustomerReceive->id + 1 : 1;
        $customer_receive_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($customer_receive_reference_no, 'Customer receive reference no generated successfully');
    }
    public function supplierPaymentReferenceNo()
    {
        $lastSupplierPayment = SupplierPayment::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
        $nextId = $lastSupplierPayment ? $lastSupplierPayment->id + 1 : 1;
        $supplier_payment_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($supplier_payment_reference_no, 'Supplier payment reference no generated successfully');
    }
    public function getTimezoneList()
    {
        $timezoneList = DateTimeZone::listIdentifiers();
        return $this->successResponse($timezoneList, 'Timezone list fetched successfully');
    }
    public function getAllUsers()
    {
        $users = User::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('id', '!=', 1)
                    ->select('id', 'name', 'phone')
                    ->orderBy('name', 'asc')
                    ->get();
        return $this->successResponse($users, 'Users fetched successfully');
    }
    public function getAllCustomers()
    {
        $customers = Customer::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->select('id', 'name', 'phone', 'same_or_diff_state')
                    ->orderBy('name', 'asc')
                    ->get();
        return $this->successResponse($customers, 'Customers fetched successfully');
    }
    public function getAllCustomersPOS()
    {
        $customers = Customer::where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->select('id', 'name', 'phone', 'same_or_diff_state')
            ->orderByRaw("CASE WHEN name = 'Walk-in Customer' THEN 0 ELSE 1 END")
            ->orderBy('name', 'asc')
            ->get();

        return $this->successResponse($customers, 'Customers fetched successfully');
    }
    public function getCustomersExceptWalkingCustomer()
    {
        $customers = Customer::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('name', '!=', 'Walk-in Customer')
                    ->select('id', 'name', 'phone', 'same_or_diff_state')
                    ->orderBy('name', 'asc')
                    ->get();
        return $this->successResponse($customers, 'Customers fetched successfully');
    }
    public function getAllSuppliers()
    {
        $suppliers = Supplier::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->select('id', 'name', 'phone')
                    ->get();
        return $this->successResponse($suppliers, 'Suppliers fetched successfully');
    }
    public function setBranchData($id)
    {
        $branch = Branch::where('id', $id)
                    ->where('del_status', 'Live')
                    ->first();
        return $this->successResponse($branch, 'Branch fetched successfully');
    }
    public function getItemList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where(function($query) {
                        $query->where('type', 'Product');
                    })
                    ->get();
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getItemAndServiceList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where(function($query) {
                $query->where('type', 'Product')
                    ->orWhere(function ($q) {
                        $q->where('type', 'Service')
                            ->where('use_consumption', false); // only services where use_consumption is false
                    });
            })
            ->get();

        return $this->successResponse($itemList, 'Item list fetched successfully');
    }

    public function getAllTypeItemList()
    {
        if(Auth::user()){
            $company_id = Auth::user()->company_id;
        }else{
            $company_id = 1;
        }
        $itemList = Item::with('category')
                    ->where('company_id', $company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->get();
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getProductTypeItemList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where('type', 'Product')
                    ->get()
                    ->select('id', 'name', 'code', 'type', 'sale_price', 'purchase_price', 'last_purchase_price', 'last_three_purchase_avg');
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getServiceTypeItemList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where('type', 'Service')
                    ->select('id', 'name', 'code', 'type', 'tax_information')
                    ->get();
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getPackageTypeItemList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where('type', 'Package')
                    ->get()
                    ->select('id', 'name', 'code', 'type');
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getServicePackageList()
    {
        $itemList = Item::where('company_id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where(function($query) {
                        $query->where('type', 'Service')
                            ->orWhere('type', 'Package');
                    })
                    ->select('id', 'name', 'code', 'type', 'tax_information')
                    ->get();
        return $this->successResponse($itemList, 'Item list fetched successfully');
    }
    public function getAllEmployees()
    {
        $listEmployees = User::where('company_id', Auth::user()->company_id)
                    ->where('status', 'Active')
                    ->where('salary', '>', 0)
                    ->where('del_status', 'Live')
                    ->get();
        return $this->successResponse($listEmployees, 'Employees fetched successfully');
    }
    public function getAllPaymentMethods()
    {
        $paymentMethods = PaymentMethod::where('company_id', Auth::user()->company_id)
                    ->where('status', 'Enable')
                    ->where('account_type', '!=', 'Loyalty Point')
                    ->where('del_status', 'Live')
                    ->orderBy('sort_id', 'ASC')
                    ->get();
        return $this->successResponse($paymentMethods, 'Payment methods fetched successfully');
    }
    public function getAllPaymentMethodsPos()
    {
        $paymentMethods = PaymentMethod::where('company_id', Auth::user()->company_id)
                    ->where('status', 'Enable')
                    ->where('del_status', 'Live')
                    ->orderBy('sort_id', 'ASC')
                    ->get();
        return $this->successResponse($paymentMethods, 'Payment methods fetched successfully');
    }

    public function getTaxDetails()
    {
        $taxDetails = Company::where('id', Auth::user()->company_id)
                    ->where('del_status', 'Live')
                    ->select('tax_setting')
                    ->first();

        $taxDetails = json_decode($taxDetails->tax_setting);

        
        return $this->successResponse($taxDetails, 'Tax details fetched successfully');
    }

    public function generateBookingReferenceNo()
    {
        $lastBooking = Booking::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
        $nextId = $lastBooking ? $lastBooking->id + 1 : 1;
        $booking_reference_no = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        return $this->successResponse($booking_reference_no, 'Booking reference no generated successfully');
    }
    public function getBookingList()
    {
        $bookingList = Booking::with('customer:id,name,phone')->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->get();
        
        return $this->successResponse($bookingList, 'Booking list fetched successfully');
    }

    public function getCustomerDue($customerId)
    {
        $customerReceive = CustomerReceive::where('customer_id', $customerId)->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->sum('amount');
        $sale_due = Sale::where('customer_id', $customerId)->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->sum('total_due');
        $customerDue = $sale_due - $customerReceive;
        return $this->successResponse($customerDue, 'Customer due fetched successfully');
    }

    public function getSupplierPayment($supplierId)
    {
        $supplierPayment = SupplierPayment::where('supplier_id', $supplierId)->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->sum('amount');
        $purchasePayment = Purchase::where('supplier_id', $supplierId)->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->sum('due_amount');
        $supplierPayment = $purchasePayment - $supplierPayment;
        return $this->successResponse($supplierPayment, 'Supplier payment fetched successfully');
    }

    public function getNotifications()
    {
        $notifications = Notification::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->orderBy('id', 'desc')->get();
        return $this->successResponse($notifications, 'Notifications fetched successfully');
    }

    public function deleteNotification($id)
    {
        $notification = Notification::where('id', $id)->where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->first();
        $notification->del_status = 'Deleted';
        $notification->save();
        return $this->successResponse($notification, 'Notification deleted successfully');
    }
    public function notificationCount()
    {
        $notificationCount = Notification::where('company_id', Auth::user()->company_id)->where('del_status', 'Live')->count();
        return $this->successResponse($notificationCount, 'Notification count fetched successfully');
    }

    public function getCustomerLoyaltyPoints($customerId)
    {
        $loyaltyPointService = new LoyaltyPointService();
        $points = $loyaltyPointService->getCustomerLoyaltyPoints($customerId);
        $settings = $loyaltyPointService->getCompanyLoyaltySettings(Auth::user()->company_id);
        
        return $this->successResponse([
            'loyalty_points' => $points,
            'settings' => $settings
        ], 'Customer loyalty points fetched successfully');
    }

    public function getCompanyLoyaltySettings()
    {
        $loyaltyPointService = new LoyaltyPointService();
        $settings = $loyaltyPointService->getCompanyLoyaltySettings(Auth::user()->company_id);
        
        return $this->successResponse($settings, 'Company loyalty settings fetched successfully');
    }

    public function calculateLoyaltyPointsNeeded(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'customer_id' => 'required|exists:customers,id'
        ]);

        $loyaltyPointService = new LoyaltyPointService();
        $pointsNeeded = $loyaltyPointService->calculateLoyaltyPointsNeeded($request->total_amount, $request->customer_id);

        $hasEnoughPoints = $loyaltyPointService->hasEnoughLoyaltyPoints($request->customer_id, $pointsNeeded);
        $currentPoints = $loyaltyPointService->getCustomerLoyaltyPoints($request->customer_id);
        
        return $this->successResponse([
            'points_needed' => $pointsNeeded,
            'has_enough_points' => $hasEnoughPoints,
            'current_points' => $currentPoints,
            'can_use_loyalty_points' => $hasEnoughPoints
        ], 'Loyalty points calculation completed');
    }

    public function markNotificationAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id'
        ]);

        $notificationIds = $request->notification_ids;
        
        $updated = Notification::whereIn('id', $notificationIds)
            ->where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->update(['read_status' => 'Yes']);

        return $this->successResponse($updated, 'Notifications marked as read successfully');
    }

    public function markNotificationAsUnread(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id'
        ]);

        $notificationIds = $request->notification_ids;
        
        $updated = Notification::whereIn('id', $notificationIds)
            ->where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->update(['read_status' => 'No']);

        return $this->successResponse($updated, 'Notifications marked as unread successfully');
    }
}
