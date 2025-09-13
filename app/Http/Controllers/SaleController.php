<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer:id,name,phone']);
        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('order_date', 'like', '%' . $request->q . '%');
        }
        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('sales.id', 'desc');
        }
        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $items = $query->paginate($perPage);

        return $this->successResponse([
            'sales' => $items->items(),
            'total' => $items->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return $this->errorResponse('Sale not found', 404);
        }
        try {
            // Soft delete by updating del_status
            $sale->update([
                'del_status' => 'Deleted'
            ]);
            return $this->successResponse(null, 'Sale deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function saleByUser(Request $request)
    {
        $query = Sale::with(['customer:id,name']);
        $query->where('company_id', Auth::user()->company_id);
        $query->where('user_id', Auth::user()->id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('order_date', 'like', '%' . $request->q . '%');
        }
        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('sales.id', 'desc');
        }
        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $items = $query->paginate($perPage);

        return $this->successResponse([
            'sales' => $items->items(),
            'total' => $items->total(),
        ]);
    }
}
