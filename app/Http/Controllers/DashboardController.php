<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function statistics()
    {
        // Get current month and previous month
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        // Total sales count
        $totalSales = Sale::count();
        // Total customers count
        $totalCustomers = Customer::count();
        // Total items count
        $totalItems = Item::count();
        $totalRevenue = Sale::sum('subtotal_without_tax_discount') - Sale::sum('discount');
        return response()->json([
            'totalSales' => $totalSales,
            'totalCustomers' => $totalCustomers,
            'totalItems' => $totalItems,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function totalProfit()
    {
        // Get profit data for the last 6 months
        $profitData = [];
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            // Calculate revenue (sales)
            $revenue = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('subtotal_without_tax_discount') 
                - Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('discount');
            
            // Calculate expenses
            $expenses = Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            
            // Calculate profit
            $profit = $revenue - $expenses;
            
            $profitData[] = max(0, $profit); // Ensure non-negative for chart
            $months[] = $month->format('M');
        }
        
        // Current month profit
        $currentMonthRevenue = Sale::whereMonth('created_at', Carbon::now()->month)
            ->sum('subtotal_without_tax_discount') 
            - Sale::whereMonth('created_at', Carbon::now()->month)
            ->sum('discount');

        $currentMonthExpenses = Expense::whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
        $currentProfit = $currentMonthRevenue - $currentMonthExpenses;
        
        // Previous month profit for percentage calculation
        $previousMonthRevenue = Sale::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('subtotal_without_tax_discount') 
            - Sale::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('discount');

        $previousMonthExpenses = Expense::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');
        $previousProfit = $previousMonthRevenue - $previousMonthExpenses;
        
        $percentageChange = $previousProfit > 0 ? (($currentProfit - $previousProfit) / $previousProfit) * 100 : 0;
        
        return response()->json([
            'series' => $profitData,
            'currentProfit' => number_format($currentProfit, 0),
            'percentageChange' => number_format($percentageChange, 2),
        ]);
    }

    public function totalExpenses()
    {
        $company = Company::select('currency')->find(Auth::user()->company_id);
        // Get current month expenses
        $currentMonthExpenses = Expense::whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
        
        // Get previous month expenses
        $previousMonthExpenses = Expense::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');
        
        // Calculate percentage for progress bar (assuming 100% = previous month expenses)
        $progressPercentage = $previousMonthExpenses > 0 ? 
            min(100, ($currentMonthExpenses / $previousMonthExpenses) * 100) : 78;
        
        // Calculate difference
        $difference = $currentMonthExpenses - $previousMonthExpenses;
        $differenceText = $difference >= 0 ? 
            $company->currency . ' ' . number_format($difference, 0) . ' Expenses more than last month' :
            $company->currency . ' ' . number_format(abs($difference), 0) . ' Expenses less than last month';
        
        return response()->json([
            'currentExpenses' => number_format($currentMonthExpenses, 0),
            'progressPercentage' => round($progressPercentage),
            'differenceText' => $differenceText,
        ]);
    }

    public function revenueReport()
    {
        // Get revenue and expense data for the last 9 months
        $revenueData = [];
        $expenseData = [];
        $categories = [];
        
        for ($i = 8; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            // Revenue
            $revenue = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('subtotal_without_tax_discount') 
                - Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('discount');
            
            // Expenses
            $expenses = Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            
            $revenueData[] = $revenue;
            $expenseData[] = -$expenses; // Negative for chart
            $categories[] = $month->format('M');
        }
        
        // Budget data (line chart)
        $budgetData = [];
        $lastMonthData = [];
        $thisMonthData = [];
        
        for ($i = 10; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $revenue = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('subtotal_without_tax_discount') 
                - Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('discount');
            
            if ($i > 0) {
                $lastMonthData[] = $revenue;
            } else {
                $thisMonthData[] = $revenue;
            }
        }
        
        // Total budget and revenue
        $totalRevenue = Sale::sum('subtotal_without_tax_discount') - Sale::sum('discount');
        $budget = $totalRevenue * 1.2; // 20% more than current revenue as budget
        
        return response()->json([
            'barSeries' => [
                [
                    'name' => 'Earning',
                    'data' => $revenueData,
                ],
                [
                    'name' => 'Expense',
                    'data' => $expenseData,
                ],
            ],
            'lineSeries' => [
                [
                    'name' => 'Last Month',
                    'data' => $lastMonthData,
                ],
                [
                    'name' => 'This Month',
                    'data' => $thisMonthData,
                ],
            ],
            'categories' => $categories,
            'totalRevenue' => number_format($totalRevenue, 0),
            'budget' => number_format($budget, 0),
        ]);
    }

    public function earningReports()
    {
        $company = Company::select('currency')->find(Auth::user()->company_id);

        // Get weekly data for the current week
        $weekData = [];
        $weekLabels = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        
        $startOfWeek = Carbon::now()->startOfWeek();
        
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $startOfDay = $day->copy()->startOfDay();
            $endOfDay = $day->copy()->endOfDay();
            
            $dayRevenue = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('subtotal_without_tax_discount') 
                - Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('discount');
            
            $weekData[] = (int)$dayRevenue;
        }
        
        // Calculate reports
        $netProfit = Sale::sum('subtotal_without_tax_discount') - Sale::sum('discount') - Expense::sum('amount');
        $totalIncome = Sale::sum('subtotal_without_tax_discount') - Sale::sum('discount');
        $totalExpenses = Expense::sum('amount');
        
        // Calculate percentages (mock data for demonstration)
        $netProfitPercentage = 18.6;
        $totalIncomePercentage = 39.6;
        $totalExpensesPercentage = 52.8;
        
        return response()->json([
            'series' => [
                [
                    'data' => $weekData
                ]
            ],
            'reports' => [
                [
                    'avatarIcon' => 'tabler-chart-pie-2',
                    'avatarColor' => 'primary',
                    'title' => 'Net Profit',
                    'subtitle' => number_format(Sale::count()) . ' Sales',
                    'earnings' => $company->currency . ' ' . number_format($netProfit, 0),
                    'percentage' => $netProfitPercentage . '%',
                ],
                [
                    'avatarIcon' => 'tabler-currency-dollar',
                    'avatarColor' => 'success',
                    'title' => 'Total Income',
                    'subtitle' => 'Sales, Affiliation',
                    'earnings' => $company->currency . ' ' . number_format($totalIncome, 0),
                    'percentage' => $totalIncomePercentage . '%',
                ],
                [
                    'avatarIcon' => 'tabler-credit-card',
                    'avatarColor' => 'secondary',
                    'title' => 'Total Expenses',
                    'subtitle' => 'Rent, Salary',
                    'earnings' => $company->currency . ' ' . number_format($totalExpenses, 0),
                    'percentage' => $totalExpensesPercentage . '%',
                ],
            ],
        ]);
    }

    public function popularProducts()
    {
        $company = Company::select('currency')->find(Auth::user()->company_id);

        // Get top selling items
        $popularProducts = Sale::select('items.photo', 'items.name', 'items.code', 'items.id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(sales.subtotal_without_tax_discount) as total_revenue'), DB::raw('SUM(sales.discount) as total_discount'))
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->join('items', 'sale_details.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name', 'items.code', 'items.photo')
            ->orderBy('sales_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($item, $index) use ($company) {
                return [
                    'title' => $item->name,
                    'subtitle' => 'Item: #' . $item->code,
                    'stats' => $company->currency . ' ' . number_format($item->total_revenue - $item->total_discount, 2),
                    'avatarImg' => $item->photo ? asset('assets/images/' . $item->photo) : asset('assets/images/system-config/default-picture.png'),
                ];
            });
        
        return response()->json([
            'products' => $popularProducts,
        ]);
    }
}
