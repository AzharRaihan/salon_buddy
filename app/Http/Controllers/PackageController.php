<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\ItemDetail;
use App\Models\SaleDetail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PackageUsagesSummary;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    use ApiResponse;

    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }
    /**
     * Display a listing of sold packages.
     */
    public function index(Request $request)
    {
        // List all sales where the item is a package
        $query = Sale::query();
        $query->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->join('items', 'sale_details.item_id', '=', 'items.id')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.id as sale_id',
                'sales.created_at as purchase_date',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'items.id as package_id',
                'items.name as package_name',
                'items.code as package_code'
            )
            ->where('items.type', 'Package')
            ->where('sales.company_id', Auth::user()->company_id)
            ->where('sales.del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function ($q) use ($request) {
                $q->where('items.name', 'like', '%' . $request->q . '%')
                  ->orWhere('items.code', 'like', '%' . $request->q . '%')
                  ->orWhere('customers.name', 'like', '%' . $request->q . '%')
                  ->orWhere('customers.phone', 'like', '%' . $request->q . '%');
            });
        }

        $query->orderBy('sales.id', 'desc');
        $perPage = $request->itemsPerPage ?? 10;
        $packages = $query->paginate($perPage);

        return $this->successResponse([
            'packages' => $packages->items(),
            'total' => $packages->total(),
            'current_page' => $packages->currentPage(),
            'last_page' => $packages->lastPage(),
            'per_page' => $packages->perPage(),
        ]);
    }

    /**
     * Show details for a specific sold package (with included items, usage, and remaining).
     */
    public function show($sale_id)
    {
        // Get sale, customer, and package info
        $sale = Sale::with(['customer'])->findOrFail($sale_id);
        $saleDetail = SaleDetail::where('sale_id', $sale_id)
            ->whereHas('item', function($q) { $q->where('type', 'Package'); })
            ->first();
        if (!$saleDetail) {
            return $this->errorResponse('Package not found for this sale.', 404);
        }
        $package = Item::find($saleDetail->item_id);
        // Get included items/services in the package
        $includedItems = ItemDetail::where('item_relation_id', $package->id)
            ->with('items:id,name,code')
            ->get();
        // For each included item, get usage and remaining
        $included = $includedItems->map(function($detail) use ($sale, $package) {
            $usedQty = PackageUsagesSummary::where('customer_id', $sale->customer_id)
                ->where('package_id', $package->id)
                ->where('sale_id', $sale->id)
                ->where('package_item_id', $detail->item_id)
                ->sum('usages_qty');
            $remaining = ($detail->quantity ?? 0) - $usedQty;
            return [
                'item_id' => $detail->item_id,
                'service_name' => $detail->items->name. ' (' . $detail->items->code . ')' ?? '',
                'package_qty' => $detail->quantity ?? 0,
                'taken' => $usedQty,
                'remaining' => $remaining,
            ];
        });
        // Usage summary (all usages for this customer/package)
        $usages = PackageUsagesSummary::where('customer_id', $sale->customer_id)
            ->where('package_id', $package->id)
            ->where('sale_id', $sale->id)
            ->with('items:id,name,code')
            ->orderBy('usages_date', 'desc')
            ->get()
            ->map(function($usage) {
                return [
                    'service_item' => $usage->items->name. ' (' . $usage->items->code . ')' ?? '',
                    'usages_date' => $usage->usages_date,
                    'usages_time' => $usage->usages_time,
                    'taken_qty' => $usage->usages_qty,
                ];
            });
        // Compose response
        return $this->successResponse([
            'package_summary' => [
                'package_name' => $package->name,
                'package_code' => $package->code,
                'purchase_date' => $sale->created_at->format('Y-m-d'),
                'duration' => $package->duration,
                'end_date' => $package->duration ? $sale->created_at->addDays((int)$package->duration)->format('Y-m-d') : null,
                'customer' => [
                    'id' => $sale->customer->id ?? '',
                    'name' => $sale->customer->name ?? '',
                    'phone' => $sale->customer->phone ?? '',
                    'email' => $sale->customer->email ?? '',
                    'address' => $sale->customer->address ?? '',
                ],
            ],
            'included_items' => $included,
            'usages_summary' => $usages,
        ]);
    }

    /**
     * Add multiple usages for package items (if remaining > 0).
     */
    public function addUsage(Request $request)
    {
        $validationRules = [
            'customer_id' => 'required|integer',
            'package_id' => 'required|integer',
            'sale_id' => 'required|integer',
            'usages' => 'required|array|min:1',
            'usages.*.package_item_id' => 'required|integer',
            'usages.*.usages_qty' => 'required|integer|min:1',
            'usages.*.usages_date' => 'required|string',
            'usages.*.usages_time' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $errors = [];
            $usagesData = [];

            // Validate all usages first
            foreach ($request->usages as $index => $usageData) {
                $detail = ItemDetail::where('item_relation_id', $request->package_id)
                    ->where('item_id', $usageData['package_item_id'])
                    ->first();

                if (!$detail) {
                    $errors[] = "Package item not found for usage #" . ($index + 1);
                    continue;
                }

                $usedQty = PackageUsagesSummary::where('customer_id', $request->customer_id)
                    ->where('package_id', $request->package_id)
                    ->where('sale_id', $request->sale_id)
                    ->where('package_item_id', $usageData['package_item_id'])
                    ->sum('usages_qty');

                $remaining = ($detail->quantity ?? 0) - $usedQty;

                if ($remaining < $usageData['usages_qty']) {
                    $errors[] = "Not enough remaining quantity for usage #" . ($index + 1) . ". Available: " . $remaining;
                    continue;
                }

                // Prepare data for bulk insert
                $usagesData[] = [
                    'customer_id' => $request->customer_id,
                    'package_id' => $request->package_id,   
                    'package_item_id' => $usageData['package_item_id'],
                    'usages_qty' => $usageData['usages_qty'],
                    'usages_date' => $usageData['usages_date'],
                    'usages_time' => $usageData['usages_time'],
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => $request->branch_id ?? null,
                    'sale_id' => $request->sale_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($errors)) {
                DB::rollBack();
                return $this->errorResponse('Some usages could not be processed: ' . implode(', ', $errors), 400);
            }

            // Bulk insert all usages
            $insertedIds = [];
            if (!empty($usagesData)) {
                $insertedIds = PackageUsagesSummary::insert($usagesData);
                $lastId = PackageUsagesSummary::where('sale_id', $request->sale_id)
                        ->where('package_id', $request->package_id)
                        ->orderBy('id', 'desc')
                            ->value('id');
            }

            // Send notifications
            $notificationData = [
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'send_whatsapp' => $request->send_whatsapp ?? false,
                'usage_id' => $lastId
            ];
            DB::commit();

            $notificationResult = $this->notificationService->sendNotifications($notificationData, 'Package Usage');

            if ($notificationResult['status'] === 'Success') {
                return $this->successResponse($insertedIds, 'Inserted successfully');
            } else {
                return $this->successResponse($insertedIds, 'Inserted successfully but ' . $notificationResult['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack(); 
            return $this->errorResponse('Failed to process usages: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }




}
