<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use App\Models\Branch;
use App\Models\Damage;
use App\Models\Salary;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Attendance;
use App\Models\ItemDetail;
use App\Models\SaleDetail;
use App\Traits\ApiResponse;
use App\Models\DamageDetail;
use App\Models\StaffPayment;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\PurchaseDetail;
use App\Models\CustomerReceive;
use App\Models\ExpenseCategory;
use App\Models\SupplierPayment;
use App\Models\DepositWithdraw;
use App\Models\ProductUsageDetail;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * Get expense report with filters
     */
    public function expenseReport(Request $request)
    {
        $query = Expense::with(['branch:id,branch_name', 'category:id,name', 'paymentMethod:id,name', 'employee:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);

        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Category filter  
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }



        // Employee filter
        if ($request->has('employee_id') && !empty($request->employee_id)) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Search by reference number
        if ($request->has('reference_no') && !empty($request->reference_no)) {
            $query->where('reference_no', 'like', '%' . $request->reference_no . '%');
        }

        // General search (for compatibility with existing search patterns)
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->q . '%')
                  ->orWhere('note', 'like', '%' . $request->q . '%')
                  ->orWhere('amount', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('date', 'desc');
        }

        $expenses = $query->get();

        // Calculate summary statistics
        $summary = $this->calculateExpenseSummary($query);

        return $this->successResponse([
            'expenses' => $expenses,
            'total' => $expenses->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * Calculate expense summary statistics
     */
    private function calculateExpenseSummary($query)
    {
        $totalExpenses = $query->count();
        $totalAmount = $query->sum('amount');
        $avgAmount = $query->avg('amount');


        return [
            'totalExpenses' => $totalExpenses,
            'totalAmount' => $totalAmount,
            'avgAmount' => $avgAmount,
        ];
    }

    /**
     * Get filter options for expense report
     */
    public function expenseReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get expense categories
        $categories = ExpenseCategory::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        // Get payment methods
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        // Get employees (users with role employee or admin)
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->select('id', 'name', 'email', 'phone')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
            'employees' => $employees,
        ]);
    }

    /**
     * Get attendance report with filters
     */
    public function attendanceReport(Request $request)
    {

        $query = Attendance::with(['user'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);
            if ($request->has('employee_id') && !empty($request->employee_id)) {
                $query->where('user_id', $request->employee_id);
            }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }


        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('date', 'desc');
        }

        $attendances = $query->get();

        // Calculate summary statistics
        $summary = $this->calculateAttendanceSummary($query);

        return $this->successResponse([
            'attendances' => $attendances,
            'total' => $attendances->count(),
            'summary' => $summary
        ]);
    }

    /**
     * Get filter options for attendance report
     */
    public function attendanceReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get employees (users)
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->select('id', 'name', 'email', 'phone')
            ->get();

        return $this->successResponse([
            'employees' => $employees,
        ]);
    }

    /**
     * Calculate attendance summary statistics
     */
    private function calculateAttendanceSummary($query)
    {
        $totalRecords = $query->count();

        
        // Calculate average working hours
        $totalHours = $query->whereNotNull('total_time')
            ->get()
            ->sum(function($attendance) {
                return $this->timeToHours($attendance->total_time);
            });

        return [
            'totalRecords' => $totalRecords,
            'totalHours' => $totalHours,
        ];
    }

    /**
     * Convert time string to hours
     */
    private function timeToHours($timeString)
    {
        if (empty($timeString)) return 0;
        
        $parts = explode(':', $timeString);
        if (count($parts) >= 2) {
            return (int)$parts[0] + ((int)$parts[1] / 60);
        }
        
        return 0;
    }

    /**
     * Get purchase report with filters
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['supplier:id,name,phone', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);


        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Supplier filter
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Search by reference number
        if ($request->has('reference_no') && !empty($request->reference_no)) {
            $query->where('reference_no', 'like', '%' . $request->reference_no . '%');
        }

        // General search (for compatibility with existing search patterns)
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->q . '%')
                  ->orWhere('supplier_invoice_no', 'like', '%' . $request->q . '%')
                  ->orWhere('note', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('date', 'desc');
        }


        $purchases = $query->get();

        // Calculate summary statistics
        $summary = $this->calculatePurchaseSummary($query);

        return $this->successResponse([
            'purchases' => $purchases,
            'total' => $purchases->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * Get filter options for purchase report
     */
    public function purchaseReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get suppliers
        $suppliers = \App\Models\Supplier::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'suppliers' => $suppliers,
        ]);
    }
    /**
     * Get damage report with filters
     */
    public function damageReport(Request $request)
    {
        $query = Damage::with(['employee:id,name,phone', 'damageDetails.item:id,name,type,code'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);

        // Filter by product type (only Product type items)
        $query->whereHas('damageDetails.item', function($q) {
            $q->where('type', 'Product');
        });

        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Supplier filter
        if ($request->has('employee_id') && !empty($request->employee_id)) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Search by reference number
        if ($request->has('reference_no') && !empty($request->reference_no)) {
            $query->where('reference_no', 'like', '%' . $request->reference_no . '%');
        }

        // General search (for compatibility with existing search patterns)
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
            $query->orderBy('date', 'desc');
        }

        $damages = $query->get();

        // Add damage_items field
        $damages->map(function ($damage) {
            $damage->damage_items = $damage->damageDetails
                ->map(function ($detail) {
                    return $detail->item->name . ' (' . $detail->item->code . ')';
                })
                ->implode(",");
            return $damage;
        });



        // Calculate summary statistics
        $summary = $this->calculateDamageSummary($query);

        return $this->successResponse([
            'damages' => $damages,
            'total' => $damages->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * Get filter options for purchase report
     */
    public function damageReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get employees
        $employees = \App\Models\User::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }

    /**
     * Get filter options for sale report
     */
    public function saleReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get customers
        $customers = \App\Models\Customer::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'customers' => $customers,
        ]);
    }

    

    /**
     * Calculate purchase summary statistics
     */
    private function calculatePurchaseSummary($query)
    {
        $totalPurchases = $query->count();
        $totalAmount = $query->sum('grand_total');
        $totalPaid = $query->sum('paid_amount');
        $totalDue = $query->sum('due_amount');

        return [
            'totalPurchases' => $totalPurchases,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
        ];
    }
    /**
     * Calculate purchase summary statistics
     */
    private function calculateDamageSummary($query)
    {
        $totalDamages = $query->count();
        $totalAmount = $query->sum('total_loss');

        return [
            'totalDamages' => $totalDamages,
            'totalAmount' => $totalAmount,
        ];
    }

    /**
     * Get sales report with filters
     */
    public function salesReport(Request $request)
    {

        $query = Sale::with(['customer:id,name', 'paymentMethod:id,name', 'branch:id,branch_name'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);

        // Customer filter
        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->where('customer_id', $request->customer_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Search by reference number
        if ($request->has('reference_no') && !empty($request->reference_no)) {
            $query->where('reference_no', 'like', '%' . $request->reference_no . '%');
        }

        // General search (for compatibility with existing search patterns)
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->q . '%')
                  ->orWhere('order_status', 'like', '%' . $request->q . '%')
                  ->orWhere('order_from', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('order_date', 'desc');
        }

        $sales = $query->get();

        // Calculate summary statistics
        $summary = $this->calculateSalesSummary($query);

        return $this->successResponse([
            'sales' => $sales,
            'total' => $sales->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * Get filter options for sales report
     */
    public function salesReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get customers
        $customers = \App\Models\Customer::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        return $this->successResponse([
            'customers' => $customers,
        ]);
    }

    /**
     * Calculate sales summary statistics
     */
    private function calculateSalesSummary($query)
    {
        $totalSales = $query->count();
        $totalRevenue = $query->sum('total_payable');
        $totalPaid = $query->sum('total_paid');
        $totalDue = $query->sum('total_due');
        $totalTax = $query->sum('total_tax');

        return [
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
            'totalTax' => $totalTax,
        ];
    }

    /**
     * Get employee commission report with filters
     */
    public function employeeCommissionReport(Request $request)
    {
        $query = \App\Models\SaleDetail::with(['sale:id,reference_no,order_date,order_status', 'item:id,name,code', 'employee:id,name,phone,commission'])
            ->whereHas('sale', function($q) {
                $q->where('del_status', 'Live')
                  ->where('company_id', Auth::user()->company_id);
            })
            ->whereNotNull('employee_id'); // Only include records with assigned employees

        // Branch filter - filter by employee's branch
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('branch_id', 'like', '%' . $request->branch_id . '%');
            });
        }

        // Employee filter
        if ($request->has('employee_id') && !empty($request->employee_id)) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            });
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_to);
            });
        }

        // Search by reference number
        if ($request->has('reference_no') && !empty($request->reference_no)) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->reference_no . '%');
            });
        }

        // General search (for compatibility with existing search patterns)
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->whereHas('sale', function($saleQuery) use ($request) {
                    $saleQuery->where('reference_no', 'like', '%' . $request->q . '%');
                })
                ->orWhereHas('item', function($itemQuery) use ($request) {
                    $itemQuery->where('name', 'like', '%' . $request->q . '%');
                })
                ->orWhereHas('employee', function($empQuery) use ($request) {
                    $empQuery->where('name', 'like', '%' . $request->q . '%');
                });
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            
            // Handle sorting for related fields
            if ($request->sortBy === 'order_date') {
                $query->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                      ->orderBy('sales.order_date', $direction)
                      ->select('sale_details.*');
            } elseif ($request->sortBy === 'reference_no') {
                $query->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                      ->orderBy('sales.reference_no', $direction)
                      ->select('sale_details.*');
            } elseif ($request->sortBy === 'order_status') {
                $query->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                      ->orderBy('sales.order_status', $direction)
                      ->select('sale_details.*');
            } else {
                $query->orderBy($request->sortBy, $direction);
            }
        } else {
            $query->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                  ->orderBy('sales.order_date', 'desc')
                  ->select('sale_details.*');
        }

        // Pagination
        $commissionDetails = $query->get();
    

        // Transform data to include calculated commission
        $transformedData = $commissionDetails->map(function ($detail) {
            $commissionRate = $detail->employee->commission ?? 0;
            $commissionAmount = ($detail->subtotal * $commissionRate) / 100;
            
            return [
                'id' => $detail->id,
                'order_date' => $detail->sale->order_date,
                'reference_no' => $detail->sale->reference_no,
                'employee' => [
                    'id' => $detail->employee->id,
                    'name' => $detail->employee->name,
                    'phone' => $detail->employee->phone,
                ],
                'item' => [
                    'id' => $detail->item->id,
                    'name' => $detail->item->name,
                ],
                'subtotal' => $detail->subtotal,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'order_status' => $detail->sale->order_status,
            ];
        });

        // Calculate summary statistics
        $summary = $this->calculateCommissionSummary($query);

        return $this->successResponse([
            'commissions' => $transformedData,
            'total' => $commissionDetails->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * Calculate commission summary statistics
     */
    private function calculateCommissionSummary($query)
    {
        $totalOrders = $query->count();
        $totalSubtotal = $query->sum('subtotal');
        
        // Calculate total commission amount
        $totalCommissionAmount = $query->get()->sum(function($detail) {
            $commissionRate = $detail->employee->commission ?? 0;
            return ($detail->subtotal * $commissionRate) / 100;
        });
        
        // Calculate average commission rate
        $avgCommissionRate = $query->get()->avg(function($detail) {
            return $detail->employee->commission ?? 0;
        });

        // sum of commission rate
        $totalCommissionRate = $query->get()->sum(function($detail) {
            return $detail->employee->commission ?? 0;
        });

        // sum of commission amount
        $totalCommissionAmount = $query->get()->sum(function($detail) {
            return ($detail->subtotal * $detail->employee->commission) / 100;
        });

        return [
            'totalOrders' => $totalOrders,
            'totalSubtotal' => $totalSubtotal,
            'totalCommissionAmount' => $totalCommissionAmount,
            'avgCommissionRate' => round($avgCommissionRate, 2),
            'totalCommissionRate' => $totalCommissionRate,
            'totalCommissionAmount' => round($totalCommissionAmount, 2),
        ];
    }

    /**
     * Get filter options for employee commission report
     */
    public function employeeCommissionReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;
        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();
        // Get employees (users with commission)
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereNotNull('commission')
            ->select('id', 'name', 'email', 'phone')
            ->get();
        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }



    /**
     * Get employee earning report with filters
     */
    public function employeeEarningReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $query = User::with('saleDetails:id,employee_id,SUM(subtotal_without_tax_discount) as subtotal, SUM(quantity) as quantity')
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereNotNull('employee_id');
            

        $query->groupBy('employee_id');
        $result = $query->get();
        return $this->successResponse([
            'employee_earning' => $result,
        ]);
            
    }


    /**
     * Get filter options for employee earning report
     */
    public function employeeEarningReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;
        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();
        // Get employees (users with commission)
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereNotNull('commission')
            ->select('id', 'name', 'email', 'phone')
            ->get();
        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }

    /**
     * Get staff earning report with filters
     */
    public function staffEarningReport(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = SaleDetail::selectRaw('
                employee_id,
                SUM(subtotal) as subtotal,
                SUM(quantity) as quantity,
                SUM(tips) as tips
            ')
            ->with([
                'employee:id,name,phone,commission',
            ])
            ->whereHas('sale', function($q) use ($companyId) {
                $q->where('del_status', 'Live')
                ->where('company_id', $companyId);
            })
            ->whereNotNull('employee_id')
            ->whereHas('employee', function($q) {
                $q->whereNotNull('commission');
            });

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('branch_id', 'like', '%' . $request->branch_id . '%');
            });
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            });
        }
        if ($request->filled('date_to')) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_to);
            });
        }

        // group by employee
        $query->groupBy('employee_id');

        $earnings = $query->get();

        // Calculate commission for each employee
        $earnings->map(function($earning) {
            $commission = 0;
            $commissionPercent = 0;
            if ($earning->employee && $earning->employee->commission) {
                $commissionPercent = floatval(str_replace('%', '', $earning->employee->commission));
                $commission = ($earning->subtotal * $commissionPercent) / 100;
            }
            $earning->commission = $commission;
            $earning->commission_rate = $commissionPercent . '%';
            return $earning;
        });

        // Summary
        $totalEmployees = $earnings->count();
        $totalSales = $earnings->sum('subtotal');
        $totalCommission = $earnings->sum('commission');
        $avgSales = $totalEmployees > 0 ? $totalSales / $totalEmployees : 0;

        return $this->successResponse([
            'earnings' => $earnings,
            'total' => $totalEmployees,
            'summary' => [
                'totalEmployees' => $totalEmployees,
                'totalSales' => $totalSales,
                'totalCommission' => $totalCommission,
                'avgSales' => $avgSales,
            ],
        ]);
    }

    /**
     * Get filter options for staff earning report
     */
    public function staffEarningReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();
            
        // Get employees (users with commission)
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereNotNull('commission')
            ->select('id', 'name', 'email', 'phone')
            ->get();
            
        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }

    /**
     * Get staff payout report with filters
     */
    public function staffPayoutReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        // Base query for staff payments
        $query = StaffPayment::with([
            'employee:id,name,phone',
            'paymentMethod:id,name'
        ])
        ->where('del_status', 'Live')
        ->where('company_id', $companyId);

        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Employee filter
        if ($request->has('employee_id') && !empty($request->employee_id)) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Get payouts data
        $payouts = $query->get();

        // Calculate summary
        $totalPayouts = $payouts->count();
        $totalAmount = $payouts->sum('amount');
        $avgAmount = $totalPayouts > 0 ? $totalAmount / $totalPayouts : 0;

        return $this->successResponse([
            'payouts' => $payouts,
            'total' => $totalPayouts,
            'summary' => [
                'totalPayouts' => $totalPayouts,
                'totalAmount' => $totalAmount,
                'avgAmount' => $avgAmount,
            ],
        ]);
    }

    /**
     * Get filter options for staff payout report
     */
    public function staffPayoutReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();
            
        // Get employees
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->select('id', 'name', 'email', 'phone')
            ->get();
            
        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }


    

    /**
     * Get profit loss report with filters
     */
    public function profitLossReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        // Base query for sales
        $salesQuery = Sale::where('del_status', 'Live')
            ->where('company_id', $companyId);

        // Base query for expenses
        $expensesQuery = Expense::where('del_status', 'Live')
            ->where('company_id', $companyId);

        // Base query for salaries
        $salariesQuery = Salary::where('del_status', 'Live')
            ->where('company_id', $companyId);

        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $salesQuery->where('branch_id', $request->branch_id);
            $expensesQuery->where('branch_id', $request->branch_id);
            $salariesQuery->where('branch_id', $request->branch_id);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $salesQuery->whereDate('order_date', '>=', $request->date_from);
            $expensesQuery->whereDate('date', '>=', $request->date_from);
            $salariesQuery->whereDate('generated_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $salesQuery->whereDate('order_date', '<=', $request->date_to);
            $expensesQuery->whereDate('date', '<=', $request->date_to);
            $salariesQuery->whereDate('generated_date', '<=', $request->date_to);
        }

        // Calculate totals
        $totalSales = $salesQuery->sum('grandtotal_with_tax_discount') ?? 0;
        $totalTax = $salesQuery->sum('total_tax') ?? 0;
        $totalDiscount = $salesQuery->sum('discount') ?? 0;
        $totalTips = $salesQuery->sum('total_tips') ?? 0;
        $deliveryCharge = $salesQuery->sum('delivery_charge') ?? 0;
        $totalExpenses = $expensesQuery->sum('amount') ?? 0;
        $totalSalaries = $salariesQuery->sum('total_amount') ?? 0;

        // Calculate cost of sale based on costing method
        $costOfSale = $this->calculateCostOfSale($request, $companyId);


        // GorssProfit
        $grossProfit = $totalSales - ($costOfSale + $totalTax + $totalDiscount + $totalTips + $deliveryCharge);

        // Calculate Net Profit: (10) - (11+12)
        $netProfit = $grossProfit - ($totalSalaries + $totalExpenses);

        return $this->successResponse([
            'total_sales' => $totalSales,
            'total_cost_of_sale' => $costOfSale,
            'tax' => $totalTax,
            'total_tips' => $totalTips,
            'discount' => $totalDiscount,
            'gross_profit' => $grossProfit,
            'delivery_charge' => $deliveryCharge,
            'total_salaries' => $totalSalaries,
            'expense' => $totalExpenses,
            'net_profit' => $netProfit,
        ]);
    }

    /**
     * Get filter options for profit loss report
     */
    public function profitLossReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
        ]);
    }

    /**
     * Calculate cost of sale based on costing method
     */
    private function calculateCostOfSale($request, $companyId)
    {
        $costingMethod = $request->get('costing_method', 'last_purchase');
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Get sales with details for the period
        $salesQuery = Sale::with([
            'saleDetails.item' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'type',
                    'last_purchase_price',
                    'last_three_purchase_avg',
                    'company_id',
                    'del_status'
                );
            }
        ])
        ->where('del_status', 'Live')
        ->where('company_id', $companyId);


        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $salesQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('order_date', '<=', $dateTo);
        }

        $sales = $salesQuery->get();


        $totalCostOfSale = 0;

        foreach ($sales as $sale) {
            foreach ($sale->saleDetails as $detail) {
                if ($detail->item && $detail->item->type === 'Product') {
                    if ($costingMethod === 'avg_purchase') {
                        $unitCost =$detail->item->last_three_purchase_avg ?? 0;
                    }else {
                        $unitCost = $detail->item->last_purchase_price ?? 0;
                    }
                    $totalCostOfSale += $unitCost * $detail->quantity;
                }
            }
        }

        return $totalCostOfSale;
    }

   

    /**
     * Get daily summary report with filters
     */
    public function dailySummaryReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $date = $request->get('date', date('Y-m-d'));
        $branchId = $request->get('branch_id');

        // Get today's sales
        $salesQuery = Sale::with(['customer:id,name,phone'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('order_date', $date);

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $sales = $salesQuery->get()->map(function($sale) {
            return [
                'id' => $sale->id,
                'invoice_no' => $sale->reference_no,
                'date' => $sale->order_date,
                'customer_name' => ($sale->customer->phone ? $sale->customer->name . ' (' . $sale->customer->phone . ')' : $sale->customer->name) ?? 'Walk-in Customer',
                'total_payable' => (float)$sale->total_payable,
                'total_paid' => (float)$sale->total_paid,
                'total_due' => (float)$sale->total_due
            ];
        });

        // Get today's purchases
        $purchasesQuery = Purchase::with(['supplier:id,name,phone'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', $date);

        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }

        $purchases = $purchasesQuery->get()->map(function($purchase) {
            return [
                'id' => $purchase->id,
                'invoice_no' => $purchase->reference_no,
                'date' => $purchase->date,
                'supplier_name' => $purchase->supplier->phone ? $purchase->supplier->name . ' (' . $purchase->supplier->phone . ')' : $purchase->supplier->name ?? 'N/A',
                'grand_total' => (float)$purchase->grand_total,
                'paid_amount' => (float)$purchase->paid_amount,
                'due_amount' => (float)$purchase->due_amount,
            ];
        });

        // Get supplier due payments
        $supplierDueQuery = SupplierPayment::with(['supplier:id,name,phone'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', $date);

        if ($branchId) {
            $supplierDueQuery->where('branch_id', $branchId);
        }

        $supplierDuePayments = $supplierDueQuery->get()->map(function($payment) {
            return [
                'id' => $payment->id,
                'supplier_name' => $payment->supplier->phone ? $payment->supplier->name . ' (' . $payment->supplier->phone . ')' : $payment->supplier->name ?? 'N/A',
                'date' => $payment->date,
                'amount' => (float)$payment->amount,
                'note' => $payment->note,
            ];
        });

        // Get customer due receives
        $customerDueQuery = CustomerReceive::with(['customer:id,name,phone'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', $date);

        if ($branchId) {
            $customerDueQuery->where('branch_id', $branchId);
        }

        $customerDueReceives = $customerDueQuery->get()->map(function($receive) {
            return [
                'id' => $receive->id,
                'customer_name' => $receive->customer->phone ? $receive->customer->name . ' (' . $receive->customer->phone . ')' : $receive->customer->name ?? 'N/A',
                'date' => $receive->date,
                'amount' => (float)$receive->amount,
                'note' => $receive->note,
            ];
        });

        return $this->successResponse([
            'sales' => $sales,
            'purchases' => $purchases,
            'supplier_due_payments' => $supplierDuePayments,
            'customer_due_receives' => $customerDueReceives,
        ]);
    }

    /**
     * Get filter options for daily summary report
     */
    public function dailySummaryReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
        ]);
    }


    /**
     * Get stock report with filters
     */
    public function stockReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
    
        $query = Item::with([
            'category:id,name',
            'unit:id,name'
        ])->where('type', 'Product')
        ->where('company_id', Auth::user()->company_id)
        ->where('del_status', 'Live')
        ->where('status', 'Enable')
        ->select('id', 'name', 'code', 'type', 'purchase_price', 'last_purchase_price', 'last_three_purchase_avg', 'category_id', 'unit_id');
        

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('code', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        if ($request->has('item_id') && !empty($request->item_id)) {
            $query->where('id', $request->item_id);
        }
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Get all items without pagination for stock report
        $items = $query->get();


        // Calculate stock for each item
        foreach ($items as $item) {
            $item->stock = $this->calculateItemStock($item->id);
        }


        return $this->successResponse([
            'items' => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * Get filter options for stock report
     */
    public function stockReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;


        // Get suppliers
        $suppliers = Supplier::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        // Get items
        $items = Item::where('del_status', 'Live')
            ->where('type', 'Product')
            ->where('company_id', $companyId)
            ->where('status', 'Enable')
            ->select('id', 'name', 'code')
            ->get();

        // Get categories
        $categories = Category::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('type', 'Product')
            ->where('status', 'Enabled')
            ->select('id', 'name')
            ->get();

        return $this->successResponse([
            'suppliers' => $suppliers,
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    /**
     * Calculate stock for a specific item
     */
    public function calculateItemStock($itemId)
    {
        $companyId = Auth::user()->company_id;

        // 1. Calculate total purchases
        $totalPurchases = PurchaseDetail::join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->where('purchase_details.item_id', $itemId)
            ->where('purchases.company_id', $companyId)
            ->where('purchases.del_status', 'Live')
            ->where('purchase_details.del_status', 'Live')
            ->sum('purchase_details.quantity');

        // 2. Calculate total direct sales
        $totalDirectSales = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sale_details.item_id', $itemId)
            ->where('sales.company_id', $companyId)
            ->where('sales.del_status', 'Live')
            ->where('sale_details.del_status', 'Live')
            ->sum('sale_details.quantity');

        // 3. Calculate total sales through packages
        $totalPackageSales = $this->calculatePackageSales($itemId, $companyId);

        // 4. Calculate total product usages
        $totalProductUsages = ProductUsageDetail::where('item_id', $itemId)
            ->where('company_id', $companyId)
            ->where('del_status', 'Live')
            ->sum('quantity');



        // 5. Calculate total damage stock
        $totalDamageStock = DamageDetail::join('damages', 'damage_details.damage_id', '=', 'damages.id')
            ->where('damage_details.item_id', $itemId)
            ->where('damages.company_id', $companyId)
            ->where('damages.del_status', 'Live')
            ->where('damage_details.del_status', 'Live')
            ->sum('damage_details.quantity');

        // Stock formula: Purchase + (- Sale) + (- Package Products sold) + (- Product Usages)
        $stock = $totalPurchases - $totalDirectSales - $totalPackageSales - $totalProductUsages - $totalDamageStock;

        return $stock;
    }

    /**
     * Calculate sales through packages for a specific item
     */
    public function calculatePackageSales($itemId, $companyId)
    {
        // Get all packages that contain this item
        $packagesWithItem = ItemDetail::join('items as package', 'item_details.item_relation_id', '=', 'package.id')
            ->where('item_details.item_id', $itemId)
            ->where('package.type', 'Package')
            ->where('package.company_id', $companyId)
            ->where('package.del_status', 'Live')
            ->where('item_details.del_status', 'Live')
            ->select('package.id as package_id', 'item_details.quantity as item_quantity_in_package')
            ->get();

        $totalPackageSales = 0;

        foreach ($packagesWithItem as $packageItem) {
            // Get total sales of this package
            $packageSales = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
                ->where('sale_details.item_id', $packageItem->package_id)
                ->where('sales.company_id', $companyId)
                ->where('sales.del_status', 'Live')
                ->where('sale_details.del_status', 'Live')
                ->sum('sale_details.quantity');

            // Calculate how many of this item were sold through this package
            $itemSoldThroughPackage = $packageSales * $packageItem->item_quantity_in_package;
            $totalPackageSales += $itemSoldThroughPackage;
        }

        return $totalPackageSales;
    }


    public function salaryReport(Request $request)
    {
        $query = Salary::with(['branch:id,branch_name', 'user:id,name,phone', 'salaryPayments:id,salary_id,payment_method_id,amount',
    'salaryPayments.paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id);
            
        // Branch filter
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }


        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('generated_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('generated_date', '<=', $request->date_to);
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        }


        $salaries = $query->get();

        // Make Payment method name by comma separated
        $salaries->map(function($salary) {
            $salary->payment_method_name = $salary->salaryPayments->map(function($payment) {
                return $payment->paymentMethod->name;
            })->implode(', ');
            return $salary;
        });

        // convert month number to month name
        $salaries->map(function($salary) {
            $salary->month = date('F', strtotime('2025-' . $salary->month . '-01')) . ', ' . $salary->year;
            return $salary;
        });

        return $this->successResponse([
            'salaries' => $salaries,
            'total' => $salaries->count(),
            'total_amount' => $salaries->sum('total_amount'),
        ]);
    }

    /**
     * Get filter options for salary report
     */
    public function salaryReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get employees
        $employees = User::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }

    /**
     * Get staff evaluation report with filters
     */
    public function staffEvaluationReport(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = \App\Models\Ratting::selectRaw('
                employee_id,
                COUNT(*) as total_ratings,
                AVG(rating) as avg_rating,
                SUM(rating) as total_rating
            ')
            ->with([
                'employee:id,name,phone',
            ])
            ->whereNotNull('employee_id')
            ->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->where('status', 'Active');
            });

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('branch_id', 'like', '%' . $request->branch_id . '%');
            });
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Group by employee
        $query->groupBy('employee_id');

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('avg_rating', 'desc');
        }

        $evaluations = $query->get();

        // Format the data
        $evaluations->map(function($evaluation) {
            $evaluation->avg_rating = round($evaluation->avg_rating, 2);
            return $evaluation;
        });

        // Summary
        $totalEmployees = $evaluations->count();
        $totalRatings = $evaluations->sum('total_ratings');
        $avgRating = $evaluations->count() > 0 ? $evaluations->avg('avg_rating') : 0;

        return $this->successResponse([
            'evaluations' => $evaluations,
            'total' => $totalEmployees,
            'summary' => [
                'totalEmployees' => $totalEmployees,
                'totalRatings' => $totalRatings,
                'avgRating' => round($avgRating, 2),
            ],
        ]);
    }

    /**
     * Get staff evaluation details report with filters
     */
    public function staffEvaluationDetailsReport(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = \App\Models\Ratting::with([
            'employee:id,name,phone',
            'customer:id,name,phone',
            'item:id,name',
        ])
        ->whereNotNull('employee_id')
        ->whereHas('employee', function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->where('status', 'Active');
        });

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('branch_id', 'like', '%' . $request->branch_id . '%');
            });
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->whereHas('employee', function($empQuery) use ($request) {
                    $empQuery->where('name', 'like', '%' . $request->q . '%')
                             ->orWhere('phone', 'like', '%' . $request->q . '%');
                })
                ->orWhereHas('customer', function($custQuery) use ($request) {
                    $custQuery->where('name', 'like', '%' . $request->q . '%')
                              ->orWhere('phone', 'like', '%' . $request->q . '%');
                });
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $evaluationDetails = $query->get();

        // Add formatted date and time
        $evaluationDetails->map(function($detail) {
            $detail->rating_date = $detail->created_at->format('Y-m-d');
            $detail->rating_time = $detail->created_at->format('H:i:s');
            return $detail;
        });

        // Summary
        $totalRatings = $evaluationDetails->count();
        $avgRating = $evaluationDetails->count() > 0 ? $evaluationDetails->avg('rating') : 0;
        $totalRatingSum = $evaluationDetails->sum('rating');

        return $this->successResponse([
            'evaluationDetails' => $evaluationDetails,
            'total' => $totalRatings,
            'summary' => [
                'totalRatings' => $totalRatings,
                'avgRating' => round($avgRating, 2),
                'totalRatingSum' => $totalRatingSum,
            ],
        ]);
    }

    /**
     * Get filter options for staff evaluation reports
     */
    public function staffEvaluationReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();
            
        // Get employees
        $employees = User::where('company_id', $companyId)
            ->where('status', 'Active')
            ->select('id', 'name', 'email', 'phone')
            ->get();
            
        return $this->successResponse([
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }

    /**
     * Get account balance report
     */
    public function accountBalanceReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');

        // Get all payment methods
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'current_balance')
            ->get();

        $accountBalances = [];
        $totalBalance = 0;

        foreach ($paymentMethods as $index => $paymentMethod) {
            $balance = $this->calculatePaymentMethodBalance($paymentMethod->id, $companyId, $branchId);
            $balance = (float)$paymentMethod->current_balance + ($balance) ;
            $accountBalances[] = [
                'sn' => $index + 1,
                'account_name' => $paymentMethod->name,
                'balance' => $balance,
            ];

            $totalBalance += $balance;
        }

        return $this->successResponse([
            'accounts' => $accountBalances,
            'total' => count($accountBalances),
            'summary' => [
                'totalBalance' => $totalBalance,
            ],
        ]);
    }

    /**
     * Calculate balance for a specific payment method
     */
    private function calculatePaymentMethodBalance($paymentMethodId, $companyId, $branchId = null)
    {
        $balance = 0;

        // Sales income (Debit)
        $salesQuery = Sale::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $balance += $salesQuery->sum('total_paid');

        // Customer receives (Debit)
        $customerReceivesQuery = CustomerReceive::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $customerReceivesQuery->where('branch_id', $branchId);
        }
        $balance += $customerReceivesQuery->sum('amount');

        // Deposits (Debit)
        $depositsQuery = DepositWithdraw::where('type', 'Deposit')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        $balance += $depositsQuery->sum('amount');

        // Purchases expense (Credit)
        $purchasesQuery = Purchase::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        $balance -= $purchasesQuery->sum('paid_amount');

        // Supplier payments (Credit)
        $supplierPaymentsQuery = SupplierPayment::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $supplierPaymentsQuery->where('branch_id', $branchId);
        }
        $balance -= $supplierPaymentsQuery->sum('amount');

        // Expenses (Credit)
        $expensesQuery = Expense::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        $balance -= $expensesQuery->sum('amount');

        // Staff payments (Credit)
        $staffPaymentsQuery = StaffPayment::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $staffPaymentsQuery->where('branch_id', $branchId);
        }
        $balance -= $staffPaymentsQuery->sum('amount');

        // Withdraws (Credit)
        $withdrawsQuery = DepositWithdraw::where('type', 'Withdraw')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $withdrawsQuery->where('branch_id', $branchId);
        }
        $balance -= $withdrawsQuery->sum('amount');

        return $balance;
    }

    /**
     * Get filter options for account balance report
     */
    public function accountBalanceReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
        ]);
    }

    /**
     * Get balance sheet report
     */
    public function balanceSheetReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Assets calculation
        $assets = [];
        $totalAssets = 0;

        // 1. Customer Due (Asset)
        $customerDue = $this->calculateCustomerDue($companyId, $branchId, $dateFrom, $dateTo);
        $assets[] = [
            'sn' => 1,
            'title' => 'Customer Due',
            'amount' => $customerDue,
        ];
        $totalAssets += $customerDue;

        // 2. Current Stock (Asset)
        $currentStock = $this->calculateCurrentStockValue($companyId, $branchId);
        $assets[] = [
            'sn' => 2,
            'title' => 'Current Stock',
            'amount' => $currentStock,
        ];
        $totalAssets += $currentStock;

        // 3. Payment Methods (Cash, Bank, etc.)
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->get();

        $sn = 3;
        foreach ($paymentMethods as $paymentMethod) {
            $balance = $this->calculatePaymentMethodBalance($paymentMethod->id, $companyId, $branchId);
            if ($balance != 0) {
                $assets[] = [
                    'sn' => $sn++,
                    'title' => $paymentMethod->name,
                    'amount' => $balance,
                ];
                $totalAssets += $balance;
            }
        }

        // Liabilities calculation
        $liabilities = [];
        $totalLiabilities = 0;

        // 1. Supplier Due (Liability)
        $supplierDue = $this->calculateSupplierDue($companyId, $branchId, $dateFrom, $dateTo);
        $liabilities[] = [
            'sn' => 1,
            'title' => 'Supplier Due',
            'amount' => $supplierDue,
        ];
        $totalLiabilities += $supplierDue;

        return $this->successResponse([
            'assets' => $assets,
            'liabilities' => $liabilities,
            'summary' => [
                'totalAssets' => $totalAssets,
                'totalLiabilities' => $totalLiabilities,
                'netWorth' => $totalAssets - $totalLiabilities,
            ],
        ]);
    }

    /**
     * Calculate customer due
     */
    private function calculateCustomerDue($companyId, $branchId = null, $dateFrom = null, $dateTo = null)
    {
        $salesQuery = Sale::where('del_status', 'Live')
            ->where('company_id', $companyId);
        
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $salesQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('order_date', '<=', $dateTo);
        }

        return $salesQuery->sum('total_due');
    }

    /**
     * Calculate supplier due
     */
    private function calculateSupplierDue($companyId, $branchId = null, $dateFrom = null, $dateTo = null)
    {
        $purchasesQuery = Purchase::where('del_status', 'Live')
            ->where('company_id', $companyId);
        
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $purchasesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('date', '<=', $dateTo);
        }

        return $purchasesQuery->sum('due_amount');
    }

    /**
     * Calculate current stock value
     */
    private function calculateCurrentStockValue($companyId, $branchId = null)
    {
        $items = Item::where('type', 'Product')
            ->where('company_id', $companyId)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->get();

        $totalStockValue = 0;

        foreach ($items as $item) {
            $stock = $this->calculateItemStock($item->id);
            $unitPrice = $item->last_purchase_price ?? $item->purchase_price ?? 0;
            $totalStockValue += $stock * $unitPrice;
        }

        return $totalStockValue;
    }

    /**
     * Get filter options for balance sheet report
     */
    public function balanceSheetReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
        ]);
    }

    /**
     * Get trial balance report
     */
    public function trialBalanceReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $sn = 1;

        // Customer Due (Debit)
        $customerDue = $this->calculateCustomerDue($companyId, $branchId, $dateFrom, $dateTo);
        if ($customerDue > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Customer Due',
                'debit' => $customerDue,
                'credit' => 0,
            ];
            $totalDebit += $customerDue;
        }

        // Sales (Credit)
        $salesQuery = Sale::where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $salesQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('order_date', '<=', $dateTo);
        }
        $totalSales = $salesQuery->sum('total_paid');
        if ($totalSales > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Sales',
                'debit' => 0,
                'credit' => $totalSales,
            ];
            $totalCredit += $totalSales;
        }

        // Customer Receives (Credit)
        $customerReceivesQuery = CustomerReceive::where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $customerReceivesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $customerReceivesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $customerReceivesQuery->whereDate('date', '<=', $dateTo);
        }
        $totalCustomerReceives = $customerReceivesQuery->sum('amount');
        if ($totalCustomerReceives > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Customer Receives',
                'debit' => 0,
                'credit' => $totalCustomerReceives,
            ];
            $totalCredit += $totalCustomerReceives;
        }

        // Supplier Due (Credit)
        $supplierDue = $this->calculateSupplierDue($companyId, $branchId, $dateFrom, $dateTo);
        if ($supplierDue > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Supplier Due',
                'debit' => 0,
                'credit' => $supplierDue,
            ];
            $totalCredit += $supplierDue;
        }

        // Purchases (Debit)
        $purchasesQuery = Purchase::where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $purchasesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('date', '<=', $dateTo);
        }
        $totalPurchases = $purchasesQuery->sum('paid_amount');
        if ($totalPurchases > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Purchases',
                'debit' => $totalPurchases,
                'credit' => 0,
            ];
            $totalDebit += $totalPurchases;
        }

        // Supplier Payments (Debit)
        $supplierPaymentsQuery = SupplierPayment::where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $supplierPaymentsQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $supplierPaymentsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $supplierPaymentsQuery->whereDate('date', '<=', $dateTo);
        }
        $totalSupplierPayments = $supplierPaymentsQuery->sum('amount');
        if ($totalSupplierPayments > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Supplier Payments',
                'debit' => $totalSupplierPayments,
                'credit' => 0,
            ];
            $totalDebit += $totalSupplierPayments;
        }

        // Expenses (Debit)
        $expensesQuery = Expense::where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $expensesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $expensesQuery->whereDate('date', '<=', $dateTo);
        }
        $totalExpenses = $expensesQuery->sum('amount');
        if ($totalExpenses > 0) {
            $trialBalance[] = [
                'sn' => $sn++,
                'title' => 'Expenses',
                'debit' => $totalExpenses,
                'credit' => 0,
            ];
            $totalDebit += $totalExpenses;
        }

        // Payment Methods Balances
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->get();

        foreach ($paymentMethods as $paymentMethod) {
            $balance = $this->calculatePaymentMethodBalance($paymentMethod->id, $companyId, $branchId);
            if ($balance > 0) {
                $trialBalance[] = [
                    'sn' => $sn++,
                    'title' => $paymentMethod->name,
                    'debit' => $balance,
                    'credit' => 0,
                ];
                $totalDebit += $balance;
            } elseif ($balance < 0) {
                $trialBalance[] = [
                    'sn' => $sn++,
                    'title' => $paymentMethod->name,
                    'debit' => 0,
                    'credit' => abs($balance),
                ];
                $totalCredit += abs($balance);
            }
        }

        return $this->successResponse([
            'trialBalance' => $trialBalance,
            'total' => count($trialBalance),
            'summary' => [
                'totalDebit' => $totalDebit,
                'totalCredit' => $totalCredit,
                'difference' => $totalDebit - $totalCredit,
            ],
        ]);
    }

    /**
     * Get filter options for trial balance report
     */
    public function trialBalanceReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
        ]);
    }

    /**
     * Get account statement report
     */
    public function accountStatementReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');
        $paymentMethodId = $request->get('payment_method_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $statements = [];
        $runningBalance = 0;

        // Opening Balance
        if ($dateFrom) {
            $openingBalance = $this->calculateOpeningBalance($companyId, $branchId, $paymentMethodId, $dateFrom);
            $statements[] = [
                'date' => $dateFrom,
                'title' => 'Opening Balance',
                'debit' => $openingBalance >= 0 ? $openingBalance : 0,
                'credit' => $openingBalance < 0 ? abs($openingBalance) : 0,
                'balance' => $openingBalance,
                'added_by' => 'System',
                'added_date_time' => $dateFrom . ' 00:00:00',
            ];
            $runningBalance = $openingBalance;
        }

        // Get all transactions
        $transactions = $this->getAccountTransactions($companyId, $branchId, $paymentMethodId, $dateFrom, $dateTo);

        foreach ($transactions as $transaction) {
            $runningBalance += $transaction['debit'] - $transaction['credit'];
            $transaction['balance'] = $runningBalance;
            $statements[] = $transaction;
        }

        // Calculate totals
        $totalDebit = array_sum(array_column($statements, 'debit'));
        $totalCredit = array_sum(array_column($statements, 'credit'));
        $closingBalance = $runningBalance;

        return $this->successResponse([
            'statements' => $statements,
            'total' => count($statements),
            'summary' => [
                'totalDebit' => $totalDebit,
                'totalCredit' => $totalCredit,
                'openingBalance' => $dateFrom ? $openingBalance : 0,
                'closingBalance' => $closingBalance,
            ],
        ]);
    }

    /**
     * Calculate opening balance for account statement
     */
    private function calculateOpeningBalance($companyId, $branchId, $paymentMethodId, $beforeDate)
    {
        $balance = 0;

        // Sales
        $salesQuery = Sale::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('order_date', '<', $beforeDate);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $salesQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance += $salesQuery->sum('total_paid');

        // Customer receives
        $customerReceivesQuery = CustomerReceive::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $customerReceivesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $customerReceivesQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance += $customerReceivesQuery->sum('amount');

        // Deposits
        $depositsQuery = DepositWithdraw::where('type', 'Deposit')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $depositsQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance += $depositsQuery->sum('amount');

        // Purchases
        $purchasesQuery = Purchase::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $purchasesQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance -= $purchasesQuery->sum('paid_amount');

        // Supplier payments
        $supplierPaymentsQuery = SupplierPayment::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $supplierPaymentsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $supplierPaymentsQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance -= $supplierPaymentsQuery->sum('amount');

        // Expenses
        $expensesQuery = Expense::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $expensesQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance -= $expensesQuery->sum('amount');

        // Staff payments
        $staffPaymentsQuery = StaffPayment::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $staffPaymentsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $staffPaymentsQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance -= $staffPaymentsQuery->sum('amount');

        // Withdraws
        $withdrawsQuery = DepositWithdraw::where('type', 'Withdraw')
            ->where('company_id', $companyId)
            ->whereDate('date', '<', $beforeDate);
        if ($branchId) {
            $withdrawsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $withdrawsQuery->where('payment_method_id', $paymentMethodId);
        }
        $balance -= $withdrawsQuery->sum('amount');

        return $balance;
    }

    /**
     * Get all account transactions
     */
    private function getAccountTransactions($companyId, $branchId, $paymentMethodId, $dateFrom, $dateTo)
    {
        $transactions = [];

        // Sales
        $salesQuery = Sale::with(['customer:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $salesQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $salesQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('order_date', '<=', $dateTo);
        }

        $sales = $salesQuery->get();
        foreach ($sales as $sale) {
            $transactions[] = [
                'date' => $sale->order_date,
                'title' => "Sale\nCustomer: " . ($sale->customer->name ?? 'Walk-in') . "\nInvoice: " . $sale->reference_no,
                'debit' => $sale->total_paid,
                'credit' => 0,
                'balance' => 0,
                'added_by' => $sale->user->name ?? 'N/A',
                'added_date_time' => $sale->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $sale->order_date,
                'sort_time' => $sale->created_at->timestamp,
            ];
        }

        // Customer receives
        $customerReceivesQuery = CustomerReceive::with(['customer:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $customerReceivesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $customerReceivesQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $customerReceivesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $customerReceivesQuery->whereDate('date', '<=', $dateTo);
        }

        $customerReceives = $customerReceivesQuery->get();
        foreach ($customerReceives as $receive) {
            $transactions[] = [
                'date' => $receive->date,
                'title' => "Customer Receive\nCustomer: " . ($receive->customer->name ?? 'N/A') . "\nRef: " . $receive->reference_no,
                'debit' => $receive->amount,
                'credit' => 0,
                'balance' => 0,
                'added_by' => $receive->user->name ?? 'N/A',
                'added_date_time' => $receive->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $receive->date,
                'sort_time' => $receive->created_at->timestamp,
            ];
        }

        // Purchases
        $purchasesQuery = Purchase::with(['supplier:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $purchasesQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $purchasesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('date', '<=', $dateTo);
        }

        $purchases = $purchasesQuery->get();
        foreach ($purchases as $purchase) {
            $transactions[] = [
                'date' => $purchase->date,
                'title' => "Purchase\nSupplier: " . ($purchase->supplier->name ?? 'N/A') . "\nRef: " . $purchase->reference_no,
                'debit' => 0,
                'credit' => $purchase->paid_amount,
                'balance' => 0,
                'added_by' => $purchase->user->name ?? 'N/A',
                'added_date_time' => $purchase->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $purchase->date,
                'sort_time' => $purchase->created_at->timestamp,
            ];
        }

        // Supplier payments
        $supplierPaymentsQuery = SupplierPayment::with(['supplier:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $supplierPaymentsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $supplierPaymentsQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $supplierPaymentsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $supplierPaymentsQuery->whereDate('date', '<=', $dateTo);
        }

        $supplierPayments = $supplierPaymentsQuery->get();
        foreach ($supplierPayments as $payment) {
            $transactions[] = [
                'date' => $payment->date,
                'title' => "Supplier Payment\nSupplier: " . ($payment->supplier->name ?? 'N/A') . "\nRef: " . $payment->reference_no,
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
                'added_by' => $payment->user->name ?? 'N/A',
                'added_date_time' => $payment->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $payment->date,
                'sort_time' => $payment->created_at->timestamp,
            ];
        }

        // Expenses
        $expensesQuery = Expense::with(['employee:id,name', 'category:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $expensesQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $expensesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $expensesQuery->whereDate('date', '<=', $dateTo);
        }

        $expenses = $expensesQuery->get();
        foreach ($expenses as $expense) {
            $transactions[] = [
                'date' => $expense->date,
                'title' => "Expense\nCategory: " . ($expense->category->name ?? 'N/A') . "\nRef: " . $expense->reference_no,
                'debit' => 0,
                'credit' => $expense->amount,
                'balance' => 0,
                'added_by' => $expense->user->name ?? 'N/A',
                'added_date_time' => $expense->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $expense->date,
                'sort_time' => $expense->created_at->timestamp,
            ];
        }

        // Staff payments
        $staffPaymentsQuery = StaffPayment::with(['employee:id,name', 'user:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId);
        if ($branchId) {
            $staffPaymentsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $staffPaymentsQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $staffPaymentsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $staffPaymentsQuery->whereDate('date', '<=', $dateTo);
        }

        $staffPayments = $staffPaymentsQuery->get();
        foreach ($staffPayments as $payment) {
            $transactions[] = [
                'date' => $payment->date,
                'title' => "Staff Payment\nEmployee: " . ($payment->employee->name ?? 'N/A') . "\nRef: " . $payment->reference_no,
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
                'added_by' => $payment->user->name ?? 'N/A',
                'added_date_time' => $payment->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $payment->date,
                'sort_time' => $payment->created_at->timestamp,
            ];
        }

        // Deposits
        $depositsQuery = DepositWithdraw::with(['paymentMethod:id,name', 'user:id,name'])
            ->where('type', 'Deposit')
            ->where('company_id', $companyId);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $depositsQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $depositsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $depositsQuery->whereDate('date', '<=', $dateTo);
        }

        $deposits = $depositsQuery->get();
        foreach ($deposits as $deposit) {
            $transactions[] = [
                'date' => $deposit->date,
                'title' => "Deposit\nRef: " . $deposit->reference_no . "\nNote: " . ($deposit->note ?? 'N/A'),
                'debit' => $deposit->amount,
                'credit' => 0,
                'balance' => 0,
                'added_by' => $deposit->user->name ?? 'N/A',
                'added_date_time' => $deposit->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $deposit->date,
                'sort_time' => $deposit->created_at->timestamp,
            ];
        }

        // Withdraws
        $withdrawsQuery = DepositWithdraw::with(['paymentMethod:id,name', 'user:id,name'])
            ->where('type', 'Withdraw')
            ->where('company_id', $companyId);
        if ($branchId) {
            $withdrawsQuery->where('branch_id', $branchId);
        }
        if ($paymentMethodId) {
            $withdrawsQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($dateFrom) {
            $withdrawsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $withdrawsQuery->whereDate('date', '<=', $dateTo);
        }

        $withdraws = $withdrawsQuery->get();
        foreach ($withdraws as $withdraw) {
            $transactions[] = [
                'date' => $withdraw->date,
                'title' => "Withdraw\nRef: " . $withdraw->reference_no . "\nNote: " . ($withdraw->note ?? 'N/A'),
                'debit' => 0,
                'credit' => $withdraw->amount,
                'balance' => 0,
                'added_by' => $withdraw->user->name ?? 'N/A',
                'added_date_time' => $withdraw->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $withdraw->date,
                'sort_time' => $withdraw->created_at->timestamp,
            ];
        }

        // Sort transactions by date and time
        usort($transactions, function ($a, $b) {
            if ($a['sort_date'] === $b['sort_date']) {
                return $a['sort_time'] - $b['sort_time'];
            }
            return strcmp($a['sort_date'], $b['sort_date']);
        });

        // Add serial numbers
        foreach ($transactions as $index => &$transaction) {
            $transaction['sn'] = $index + 1;
            unset($transaction['sort_date']);
            unset($transaction['sort_time']);
        }

        return $transactions;
    }

    /**
     * Get filter options for account statement report
     */
    public function accountStatementReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get payment methods
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Get transaction history report
     */
    public function transactionHistoryReport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');
        $paymentMethodId = $request->get('payment_method_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Payment method is required
        if (!$paymentMethodId) {
            return $this->errorResponse('Payment method is required', 422);
        }

        $transactions = [];

        // Get payment method name
        $paymentMethod = PaymentMethod::find($paymentMethodId);
        $paymentMethodName = $paymentMethod ? $paymentMethod->name : 'N/A';

        // Sales
        $salesQuery = Sale::with(['customer:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $salesQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('order_date', '<=', $dateTo);
        }

        $sales = $salesQuery->get();
        foreach ($sales as $sale) {
            $transactions[] = [
                'date' => $sale->order_date,
                'reference_no' => $sale->reference_no,
                'type' => 'Sale',
                'payment_account' => $paymentMethodName,
                'amount' => $sale->total_paid,
                'added_by' => $sale->user->name ?? 'N/A',
                'added_date_time' => $sale->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $sale->order_date,
                'sort_time' => $sale->created_at->timestamp,
            ];
        }

        // Customer Receives
        $customerReceivesQuery = CustomerReceive::with(['customer:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $customerReceivesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $customerReceivesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $customerReceivesQuery->whereDate('date', '<=', $dateTo);
        }

        $customerReceives = $customerReceivesQuery->get();
        foreach ($customerReceives as $receive) {
            $transactions[] = [
                'date' => $receive->date,
                'reference_no' => $receive->reference_no,
                'type' => 'Customer Receive',
                'payment_account' => $paymentMethodName,
                'amount' => $receive->amount,
                'added_by' => $receive->user->name ?? 'N/A',
                'added_date_time' => $receive->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $receive->date,
                'sort_time' => $receive->created_at->timestamp,
            ];
        }

        // Deposits
        $depositsQuery = DepositWithdraw::with(['user:id,name', 'paymentMethod:id,name'])
            ->where('type', 'Deposit')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $depositsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $depositsQuery->whereDate('date', '<=', $dateTo);
        }

        $deposits = $depositsQuery->get();
        foreach ($deposits as $deposit) {
            $transactions[] = [
                'date' => $deposit->date,
                'reference_no' => $deposit->reference_no,
                'type' => 'Deposit',
                'payment_account' => $paymentMethodName,
                'amount' => $deposit->amount,
                'added_by' => $deposit->user->name ?? 'N/A',
                'added_date_time' => $deposit->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $deposit->date,
                'sort_time' => $deposit->created_at->timestamp,
            ];
        }

        // Purchases
        $purchasesQuery = Purchase::with(['supplier:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $purchasesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('date', '<=', $dateTo);
        }

        $purchases = $purchasesQuery->get();
        foreach ($purchases as $purchase) {
            $transactions[] = [
                'date' => $purchase->date,
                'reference_no' => $purchase->reference_no,
                'type' => 'Purchase',
                'payment_account' => $paymentMethodName,
                'amount' => $purchase->paid_amount,
                'added_by' => $purchase->user->name ?? 'N/A',
                'added_date_time' => $purchase->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $purchase->date,
                'sort_time' => $purchase->created_at->timestamp,
            ];
        }

        // Supplier Payments
        $supplierPaymentsQuery = SupplierPayment::with(['supplier:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $supplierPaymentsQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $supplierPaymentsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $supplierPaymentsQuery->whereDate('date', '<=', $dateTo);
        }

        $supplierPayments = $supplierPaymentsQuery->get();
        foreach ($supplierPayments as $payment) {
            $transactions[] = [
                'date' => $payment->date,
                'reference_no' => $payment->reference_no,
                'type' => 'Supplier Payment',
                'payment_account' => $paymentMethodName,
                'amount' => $payment->amount,
                'added_by' => $payment->user->name ?? 'N/A',
                'added_date_time' => $payment->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $payment->date,
                'sort_time' => $payment->created_at->timestamp,
            ];
        }

        // Expenses
        $expensesQuery = Expense::with(['employee:id,name', 'category:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $expensesQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $expensesQuery->whereDate('date', '<=', $dateTo);
        }

        $expenses = $expensesQuery->get();
        foreach ($expenses as $expense) {
            $transactions[] = [
                'date' => $expense->date,
                'reference_no' => $expense->reference_no,
                'type' => 'Expense',
                'payment_account' => $paymentMethodName,
                'amount' => $expense->amount,
                'added_by' => $expense->user->name ?? 'N/A',
                'added_date_time' => $expense->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $expense->date,
                'sort_time' => $expense->created_at->timestamp,
            ];
        }

        // Staff Payments
        $staffPaymentsQuery = StaffPayment::with(['employee:id,name', 'user:id,name', 'paymentMethod:id,name'])
            ->where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $staffPaymentsQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $staffPaymentsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $staffPaymentsQuery->whereDate('date', '<=', $dateTo);
        }

        $staffPayments = $staffPaymentsQuery->get();
        foreach ($staffPayments as $payment) {
            $transactions[] = [
                'date' => $payment->date,
                'reference_no' => $payment->reference_no,
                'type' => 'Staff Payment',
                'payment_account' => $paymentMethodName,
                'amount' => $payment->amount,
                'added_by' => $payment->user->name ?? 'N/A',
                'added_date_time' => $payment->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $payment->date,
                'sort_time' => $payment->created_at->timestamp,
            ];
        }

        // Withdraws
        $withdrawsQuery = DepositWithdraw::with(['user:id,name', 'paymentMethod:id,name'])
            ->where('type', 'Withdraw')
            ->where('company_id', $companyId)
            ->where('payment_method_id', $paymentMethodId);
        if ($branchId) {
            $withdrawsQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $withdrawsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $withdrawsQuery->whereDate('date', '<=', $dateTo);
        }

        $withdraws = $withdrawsQuery->get();
        foreach ($withdraws as $withdraw) {
            $transactions[] = [
                'date' => $withdraw->date,
                'reference_no' => $withdraw->reference_no,
                'type' => 'Withdraw',
                'payment_account' => $paymentMethodName,
                'amount' => $withdraw->amount,
                'added_by' => $withdraw->user->name ?? 'N/A',
                'added_date_time' => $withdraw->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $withdraw->date,
                'sort_time' => $withdraw->created_at->timestamp,
            ];
        }

        // Sort transactions by date and time
        usort($transactions, function ($a, $b) {
            if ($a['sort_date'] === $b['sort_date']) {
                return $a['sort_time'] - $b['sort_time'];
            }
            return strcmp($a['sort_date'], $b['sort_date']);
        });

        // Add serial numbers and remove sorting fields
        $totalAmount = 0;
        foreach ($transactions as $index => &$transaction) {
            $transaction['sn'] = $index + 1;
            $totalAmount += $transaction['amount'];
            unset($transaction['sort_date']);
            unset($transaction['sort_time']);
        }

        return $this->successResponse([
            'transactions' => $transactions,
            'total' => count($transactions),
            'summary' => [
                'totalTransactions' => count($transactions),
                'totalAmount' => $totalAmount,
            ],
        ]);
    }

    /**
     * Get filter options for transaction history report
     */
    public function transactionHistoryReportFilters(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Get branches
        $branches = Branch::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'branch_name as name')
            ->get();

        // Get payment methods
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        return $this->successResponse([
            'branches' => $branches,
            'paymentMethods' => $paymentMethods,
        ]);
    }

}
