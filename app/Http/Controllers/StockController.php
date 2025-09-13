<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\ItemDetail;
use App\Models\SaleDetail;
use App\Models\DamageDetail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    use ApiResponse;

    /**
     * Get stock list for Product type items
     */
    public function index(Request $request)
    {
        $categories = Category::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where('status', 'Enabled')
                ->get()
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                });
        $suppliers = Supplier::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->get()
                ->map(function($supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'phone' => $supplier->phone,
                    ];
                });

        $itemList = Item::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where('type', 'Product')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name . '(' . $item->code . ')',
                    ];
                });

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

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $items = $query->paginate($perPage);

        // Calculate stock for each item
        $itemsWithStock = $items->items();
        foreach ($itemsWithStock as $item) {
            $item->stock = $this->calculateItemStock($item->id);
        }

        return $this->successResponse([
            'items' => $items->items(),
            'total' => $items->total(),
            'categories' => $categories,
            'suppliers' => $suppliers,
            'itemsList' => $itemList,
        ]);
    }

    /**
     * Get list of items with stock less than or equal to alert stock quantity
     */
    public function alertStockList(Request $request)
    {
        $categories = Category::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where('status', 'Enabled')
                ->get()
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                });
        $suppliers = Supplier::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->get()
                ->map(function($supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'phone' => $supplier->phone,
                    ];
                });

        $itemList = Item::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where('type', 'Product')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name . '(' . $item->code . ')',
                    ];
                });


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

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $items = $query->paginate($perPage);

        // Filter for alert stock items
        $alertStockItems = [];
        foreach ($items->items() as $item) {
            $stock = $this->calculateItemStock($item->id);
            if ($stock <= ($item->alert_stock_qty ?? 0)) {
                $item->stock = $stock;
                $alertStockItems[] = $item;
            }
        }

        return $this->successResponse([
            'items' => $alertStockItems,
            'total' => count($alertStockItems),
            'categories' => $categories,
            'suppliers' => $suppliers,
            'itemsList' => $itemList,
        ]);
    }


    /**
     * Get stock details for a specific item
     */
    public function show($id)
    {
        $item = Item::where('id', $id)
                   ->where('company_id', Auth::user()->company_id)
                   ->where('del_status', 'Live')
                   ->where('type', 'Product')
                   ->first();

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        $stockDetails = $this->getItemStockDetails($id);

        return $this->successResponse([
            'item' => $item,
            'stock_details' => $stockDetails
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
        $totalProductUsages = \App\Models\ProductUsages::where('item_id', $itemId)
            ->where('company_id', $companyId)
            ->where('del_status', 'Live')
            ->sum('quantity');

        // Damage stock
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

    /**
     * Get detailed stock information for an item
     */
    public function getItemStockDetails($itemId)
    {
        $companyId = Auth::user()->company_id;

        // Total purchases
        $totalPurchases = PurchaseDetail::join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->where('purchase_details.item_id', $itemId)
            ->where('purchases.company_id', $companyId)
            ->where('purchases.del_status', 'Live')
            ->where('purchase_details.del_status', 'Live')
            ->sum('purchase_details.quantity');

        // Total direct sales
        $totalDirectSales = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sale_details.item_id', $itemId)
            ->where('sales.company_id', $companyId)
            ->where('sales.del_status', 'Live')
            ->where('sale_details.del_status', 'Live')
            ->sum('sale_details.quantity');

        // Total package sales
        $totalPackageSales = $this->calculatePackageSales($itemId, $companyId);

        // Total product usages
        $totalProductUsages = \App\Models\ProductUsages::where('item_id', $itemId)
            ->where('company_id', $companyId)
            ->where('del_status', 'Live')
            ->sum('quantity');

        // Current stock
        $currentStock = max(0, $totalPurchases - $totalDirectSales - $totalPackageSales - $totalProductUsages);

        return [
            'total_purchases' => $totalPurchases,
            'total_direct_sales' => $totalDirectSales,
            'total_package_sales' => $totalPackageSales,
            'total_product_usages' => $totalProductUsages,
            'current_stock' => $currentStock,
        ];
    }

    /**
     * Get stock summary for dashboard
     */
    public function summary(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Start query builder
        $query = Item::where('type', 'Product')
                    ->where('company_id', $companyId)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable');

        if ($request->has('item_id') && !empty($request->item_id)) {
            $query->where('id', $request->item_id);
        }
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Now fetch data
        $productItems = $query->get();


        $totalItems = $productItems->count();
        $totalStock = 0;
        $lowStockItems = 0;
        $totalStockValue = 0;
        foreach ($productItems as $item) {
            $stock = $this->calculateItemStock($item->id);
            $totalStock += $stock;
            if ($stock <= $item->alert_stock_qty) {
                $lowStockItems++;
            }
            $totalStockValue += ($item->purchase_price ?? 0) * $stock;
        }
        return $this->successResponse([
            'total_items' => $totalItems,
            'total_stock' => $totalStock,
            'low_stock_items' => $lowStockItems,
            'total_stock_value' => $totalStockValue,
        ]);
    }
} 