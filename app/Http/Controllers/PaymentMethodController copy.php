<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
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
        $sub = DB::table('payment_methods')
            ->leftJoin('sales', function ($join) {
                $join->on('payment_methods.id', '=', 'sales.payment_method_id')
                    ->where('sales.del_status', 'Live');
            })
            ->leftJoin('customer_receives', function ($join) {
                $join->on('payment_methods.id', '=', 'customer_receives.payment_method_id')
                    ->where('customer_receives.del_status', 'Live');
            })
            ->leftJoin('purchases', function ($join) {
                $join->on('payment_methods.id', '=', 'purchases.payment_method_id')
                    ->where('purchases.del_status', 'Live');
            })
            ->leftJoin('supplier_payments', function ($join) {
                $join->on('payment_methods.id', '=', 'supplier_payments.payment_method_id')
                    ->where('supplier_payments.del_status', 'Live');
            })
            ->leftJoin('expenses', function ($join) {
                $join->on('payment_methods.id', '=', 'expenses.payment_method_id')
                    ->where('expenses.del_status', 'Live');
            })
            ->leftJoin('salary_payments', function ($join) {
                $join->on('payment_methods.id', '=', 'salary_payments.payment_method_id')
                    ->where('salary_payments.del_status', 'Live');
            })
            ->where('payment_methods.del_status', 'Live')
            ->where('payment_methods.company_id', Auth::user()->company_id)
            ->groupBy(
                'payment_methods.id',
                'payment_methods.name',
                'payment_methods.account_type',
                'payment_methods.use_in_website',
                'payment_methods.payment_method_icon',
                'payment_methods.created_at',
                'payment_methods.updated_at'
            )
            ->select(
                'payment_methods.id',
                'payment_methods.name',
                'payment_methods.account_type',
                'payment_methods.use_in_website',
                'payment_methods.payment_method_icon',
                'payment_methods.created_at',
                'payment_methods.updated_at',
                DB::raw('MAX(COALESCE(payment_methods.current_balance, 0)) as opening_balance'),
                DB::raw('(
                    MAX(COALESCE(payment_methods.current_balance, 0)) +
                    SUM(COALESCE(sales.total_paid, 0)) +
                    SUM(COALESCE(customer_receives.amount, 0)) -
                    SUM(COALESCE(purchases.paid_amount, 0)) -
                    SUM(COALESCE(supplier_payments.amount, 0)) -
                    SUM(COALESCE(expenses.amount, 0)) -
                    SUM(COALESCE(salary_payments.amount, 0))
                ) as current_balance'),
                DB::raw('SUM(COALESCE(sales.total_paid, 0)) as total_sales'),
                DB::raw('SUM(COALESCE(customer_receives.amount, 0)) as total_receives'),
                DB::raw('SUM(COALESCE(purchases.paid_amount, 0)) as total_purchases'),
                DB::raw('SUM(COALESCE(supplier_payments.amount, 0)) as total_supplier_payments'),
                DB::raw('SUM(COALESCE(expenses.amount, 0)) as total_expenses'),
                DB::raw('SUM(COALESCE(salary_payments.amount, 0)) as total_salary_payments')
            );

        // Optional search
        if ($request->filled('q')) {
            $sub->havingRaw('(payment_methods.name LIKE ? OR payment_methods.description LIKE ?)', [
                '%' . $request->q . '%',
                '%' . $request->q . '%',
            ]);
        }

        // Optional sorting
        if ($request->filled('sortBy')) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $sub->orderBy($request->sortBy, $direction);
        } else {
            $sub->orderBy('payment_methods.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $page = $request->page ?? 1;

        $results = $sub->paginate($perPage, ['*'], 'page', $page);

        dd($results);

        // Calculate current balance correctly for each payment method
        $paymentMethods = collect($results->items())->map(function ($item) {
            $item->current_balance = $item->opening_balance + 
                                   $item->total_sales + 
                                   $item->total_receives - 
                                   $item->total_purchases - 
                                   $item->total_supplier_payments - 
                                   $item->total_expenses - 
                                   $item->total_salary_payments;
            
            // Remove the individual totals from the response
            unset($item->total_sales);
            unset($item->total_receives);
            unset($item->total_purchases);
            unset($item->total_supplier_payments);
            unset($item->total_expenses);
            unset($item->total_salary_payments);
            
            return $item;
        });

        return $this->successResponse([
            'paymentMethods' => $paymentMethods,
            'total' => $results->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'account_type' => 'required|string|max:55',
            'description' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'status' => 'required|string|max:55',
            'use_in_website' => 'required|string|max:55',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg',
        ];

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
            'account_type' => 'required|string|max:55',
            'description' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'status' => 'required|string|max:55',
            'use_in_website' => 'required|string|max:55',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg',
        ];
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
}
