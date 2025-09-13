<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Damage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\DamageDetail;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DamageController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Damage::query();

        // Join with supplier
        $query->join('users', 'damages.employee_id', '=', 'users.id')
              ->select('damages.*', 'users.name as employee_name', 'users.phone as employee_phone');

        // Filter by del_status
        $query->where('damages.del_status', 'Live');
        $query->where('damages.branch_id', $request->branch_id);
        $query->where('damages.company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('damages.reference_no', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('damages.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $damages = $query->paginate($perPage);

        return $this->successResponse([
            'damages' => $damages->items(),
            'total' => $damages->total(),
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
                'reference_no' => 'required|string|unique:damages',
                'employee_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'items' => 'required|json',
                'total_loss' => 'required|numeric|min:0',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $validatedData = $validator->validated();

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

           

            DB::beginTransaction();

            // Create purchase
            $damage = Damage::create([
                'reference_no' => $request->reference_no,
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'total_loss' => $request->total_loss,
                'note' => $request->note,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Create purchase items
            foreach ($items as $item) {
                DamageDetail::create([
                    'damage_id' => $damage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_loss' => $item['quantity'] * $item['unit_price'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);
            }
            DB::commit();
            return $this->successResponse($damage, 'Damage created successfully');
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
        $damage = Damage::with(['DamageDetails', 'DamageDetails.item:id,name', 'employee:id,name'])->find($id);
        if (!$damage) {
            return $this->errorResponse('Damage not found', 404);
        }
        return $this->successResponse($damage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Find the purchase
            $damage = Damage::find($id);
            if (!$damage) {
                return $this->errorResponse('Damage not found', 404);
            }
            // Validate request
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|unique:damages,reference_no,' . $id,
                'employee_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'items' => 'required|json',
                'total_loss' => 'required|numeric|min:0',
                'note' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $validatedData = $validator->validated();
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

            DB::beginTransaction();
        
            // Update damage
            $damage->update([
                'reference_no' => $request->reference_no,
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'total_loss' => $request->total_loss,
                'note' => $request->note,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::user()->id,
                'updated_at' => now(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Delete old damage items
            DamageDetail::where('damage_id', $damage->id)->delete();

            // Create new purchase items
            foreach ($items as $item) {
                DamageDetail::create([
                    'damage_id' => $damage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_loss' => $item['quantity'] * $item['unit_price'],
                    'branch_id' => $request->branch_id,
                    'user_id' => Auth::user()->id,
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            DB::commit();
            return $this->successResponse($damage, 'Damage updated successfully');
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
        $damage = Damage::find($id);
        if (!$damage) {
            return $this->errorResponse('Purchase not found', 404);
        }
        DB::beginTransaction();
        try {
            // Delete damage details
            DamageDetail::where('damage_id', $id)->update([
                'del_status' => 'Deleted'
            ]);
            // Delete main damage
            $damage->update([
                'del_status' => 'Deleted'
            ]);
            DB::commit();
            return $this->successResponse(null, 'Damage deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
