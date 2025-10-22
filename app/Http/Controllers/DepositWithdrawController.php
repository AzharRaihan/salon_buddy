<?php

namespace App\Http\Controllers;

use App\Models\DepositWithdraw;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepositWithdrawController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DepositWithdraw::query();

        // Join with users table
        $query->join('payment_methods', 'deposit_withdraws.payment_method_id', '=', 'payment_methods.id')
              ->select('deposit_withdraws.*', 'payment_methods.name as payment_method_name');

        // Filter by del_status
        $query->where('deposit_withdraws.del_status', 'Live');
        $query->where('deposit_withdraws.branch_id', $request->branch_id);
        $query->where('deposit_withdraws.company_id', Auth::user()->company_id);
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('deposit_withdraws.reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('deposit_withdraws.note', 'like', '%' . $request->q . '%')
                ->orWhere('payment_methods.name', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('deposit_withdraws.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $depositWithdraws = $query->paginate($perPage);

        return $this->successResponse([
            'depositWithdraws' => $depositWithdraws->items(),
            'total' => $depositWithdraws->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'type' => 'required|in:Deposit,Withdraw',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $depositWithdraw = DepositWithdraw::create($validatedData);
            DB::commit();
            return $this->successResponse($depositWithdraw, 'Deposit Withdraw created successfully');
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
        $depositWithdraw = DepositWithdraw::with(['paymentMethod:id,name'])->find($id);
        if (!$depositWithdraw) {
            return $this->errorResponse('Deposit Withdraw not found', 404);
        }
        return $this->successResponse($depositWithdraw);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Base validation rules
        $validationRules = [
            'reference_no' => 'required|string|max:55',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'type' => 'required|in:Deposit,Withdraw',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $depositWithdraw = DepositWithdraw::find($id);
        if (!$depositWithdraw) {
            return $this->errorResponse('Deposit Withdraw not found', 404);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['note'] = $request->note == null ? '' : $request->note;
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $depositWithdraw->update($validatedData);
            DB::commit();
            return $this->successResponse($depositWithdraw, 'Deposit Withdraw updated successfully');
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
        $depositWithdraw = DepositWithdraw::find($id);
        if (!$depositWithdraw) {
            return $this->errorResponse('Deposit Withdraw not found', 404);
        }
        try {
            $depositWithdraw->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Deposit Withdraw deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


     /**
     * Generate reference number
     */
    public function generateReferenceNo()
    {
        $lastDepositWithdraw = DepositWithdraw::where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastDepositWithdraw) {
            $lastNumber = (int) substr($lastDepositWithdraw->reference_no, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $referenceNo = 'DW-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        
        return $this->successResponse($referenceNo);
    }
}
