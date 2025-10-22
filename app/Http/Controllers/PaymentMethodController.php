<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\Purchase;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SalaryPayment;
use App\Models\CustomerReceive;
use App\Models\SupplierPayment;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PaymentMethod::query();
        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('description', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            // Default sorting by sort_id first, then by id
            $query->orderBy('payment_methods.sort_id', 'asc')
                  ->orderBy('payment_methods.id', 'asc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $paymentMethods = $query->paginate($perPage);

        foreach ($paymentMethods->items() as $paymentMethod) {
            $paymentMethod->current_balance = $this->calculateCurrentBalance($paymentMethod->id);
        }

        return $this->successResponse([
            'paymentMethods' => $paymentMethods->items(),
            'total' => $paymentMethods->total(),
        ]);

    }


    // Calculate current balance
    public function calculateCurrentBalance($id)
    {
        $paymentMethod = PaymentMethod::where('id', $id)->first();
        $totalSales = Sale::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()->company_id)
                            ->sum('total_paid');
        $totalReceives = CustomerReceive::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()->company_id)
                            ->sum('amount');
        $totalPurchases = Purchase::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()
                            ->company_id)->sum('paid_amount');
        $totalSupplierPayments = SupplierPayment::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()
                            ->company_id)
                            ->sum('amount');
        $totalExpenses = Expense::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()
                            ->company_id)
                            ->sum('amount');
        $totalSalaryPayments = SalaryPayment::where('payment_method_id', $id)
                            ->where('del_status', 'Live')
                            ->where('company_id', Auth::user()
                            ->company_id)
                            ->sum('amount');
        $currentBalance = $paymentMethod->current_balance
        + $totalSales
        + $totalReceives
        - $totalPurchases
        - $totalSupplierPayments
        - $totalExpenses
        - $totalSalaryPayments;
        return $currentBalance;
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'account_type' => 'required|string|in:Cash,Bank,Paypal,Stripe,Razorpay,PayStack,Paytm',
            'description' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'status' => 'required|string|in:Enable,Disable',
            'use_in_website' => 'required|string|in:Yes,No',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg',
        ];

        // Add conditional validation based on account type
        if ($request->account_type === 'Bank') {
            $validationRules['bank_name'] = 'required|string|max:100';
            $validationRules['account_number'] = 'required|string|max:100';
            $validationRules['branch'] = 'required|string|max:100';
        } elseif ($request->account_type === 'Paypal') {
            $validationRules['client_id'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
            $validationRules['mode'] = 'required|string|in:Sandbox,Live';
        } elseif ($request->account_type === 'Stripe') {
            $validationRules['api_key'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
            $validationRules['mode'] = 'required|string|in:Sandbox,Live';
        } elseif ($request->account_type === 'Razorpay') {
            $validationRules['api_key'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
        } elseif ($request->account_type === 'PayStack') {
            $validationRules['api_key'] = 'required|string|max:255';
        } elseif ($request->account_type === 'Paytm') {
            $validationRules['merchant_id'] = 'required|string|max:255';
            $validationRules['merchant_key'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['payment_method_icon'] = $this->imageUpload($request->file('photo'), null, 'payment-method');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $paymentMethod = PaymentMethod::create($validatedData);
            DB::commit();
            return $this->successResponse($paymentMethod, 'Payment method created successfully');
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
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }
        return $this->successResponse($paymentMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'account_type' => 'required|string|in:Cash,Bank,Paypal,Stripe,Razorpay,PayStack,Paytm',
            'description' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'status' => 'required|string|in:Enable,Disable',
            'use_in_website' => 'required|string|in:Yes,No',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg',
        ];

        // Add conditional validation based on account type
        if ($request->account_type === 'Bank') {
            $validationRules['bank_name'] = 'required|string|max:100';
            $validationRules['account_number'] = 'required|string|max:100';
            $validationRules['branch'] = 'required|string|max:100';
        } elseif ($request->account_type === 'Paypal') {
            $validationRules['client_id'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
            $validationRules['mode'] = 'required|string|in:Sandbox,Live';
        } elseif ($request->account_type === 'Stripe') {
            $validationRules['api_key'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
            $validationRules['mode'] = 'required|string|in:Sandbox,Live';
        } elseif ($request->account_type === 'Razorpay') {
            $validationRules['api_key'] = 'required|string|max:255';
            $validationRules['secret_key'] = 'required|string|max:255';
        } elseif ($request->account_type === 'PayStack') {
            $validationRules['api_key'] = 'required|string|max:255';
        } elseif ($request->account_type === 'Paytm') {
            $validationRules['merchant_id'] = 'required|string|max:255';
            $validationRules['merchant_key'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['payment_method_icon'] = $this->imageUpload($request->file('photo'), $paymentMethod->payment_method_icon, 'payment-method');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $paymentMethod->update($validatedData);
            DB::commit();
            return $this->successResponse($paymentMethod, 'Payment method updated successfully');
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
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }
        $paymentMethod->update([
            'del_status' => 'Deleted'
        ]);
        return $this->successResponse(null, 'Payment method deleted successfully');
    }

    /**
     * Get all payment methods for sorting (without pagination)
     */
    public function getPaymentMethodsForSorting(Request $request)
    {
        $paymentMethods = PaymentMethod::where('del_status', 'Live')
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('sort_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return $this->successResponse([
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update sort order of payment methods
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort_order' => 'required|array',
            'sort_order.*.id' => 'required|integer|exists:payment_methods,id',
            'sort_order.*.sort_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            foreach ($request->sort_order as $item) {
                $paymentMethod = PaymentMethod::where('id', $item['id'])
                    ->where('company_id', Auth::user()->company_id)
                    ->first();
                
                if ($paymentMethod) {
                    $paymentMethod->update([
                        'sort_id' => $item['sort_id'],
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse(null, 'Sort order updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reset sort order to default (by ID)
     */
    public function resetSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $paymentMethods = PaymentMethod::where('del_status', 'Live')
                ->where('company_id', Auth::user()->company_id)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($paymentMethods as $index => $paymentMethod) {
                $paymentMethod->update([
                    'sort_id' => $index + 1,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Sort order reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
