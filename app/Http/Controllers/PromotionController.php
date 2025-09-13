<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    use ApiResponse;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Promotion::query();

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('promotions.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $promotions = $query->paginate($perPage);

        return $this->successResponse([
            'promotions' => $promotions->items(),
            'total' => $promotions->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:255',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'type' => 'required|string|in:Discount,Free Item',
            'status' => 'required|string|in:Active,Inactive',
        ];

        // Conditional validation rules
        if ($request->type === 'Discount') {
            $validationRules['discount'] = 'required|numeric|min:0';
            $validationRules['discount_type'] = 'required|string|in:Percentage,Fixed';
            
            // Additional validation for percentage discount
            if ($request->discount_type === 'Percentage') {
                $validationRules['discount'] = 'required|numeric|min:0|max:100';
            }
            
            // Additional validation for fixed discount
            if ($request->discount_type === 'Fixed') {
                $validationRules['discount'] = 'required|numeric|min:0';
            }
        } else if ($request->type === 'Free Item') {
            $validationRules['buy_item_id'] = 'required|exists:items,id';
            $validationRules['buy_qty'] = 'required|integer|min:1';
            $validationRules['get_item_id'] = 'required|exists:items,id';
            $validationRules['get_qty'] = 'required|integer|min:1';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $start = $validatedData['start_date'];
            $end = $validatedData['end_date'];

            // Fetch overlapping promotions in this date range
            $existingPromotions = Promotion::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                        });
                })
                ->get();

            // Rule 1: Check for global discount conflict (highest priority)
            $hasGlobalDiscount = $existingPromotions->contains(function ($promo) {
                return $promo->type === 'Discount' && empty($promo->discount_item_id);
            });

            if ($validatedData['type'] === 'Discount' && empty($request->discount_item_id)) {
                if ($hasGlobalDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A global discount already exists in the selected date range. You cannot create another promotion.');
                }
            } else {
                if ($hasGlobalDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A global discount already exists in the selected date range. Additional promotions cannot be created.');
                }
            }

            // Rule 2: Prevent duplicate item-specific Discount
            if ($validatedData['type'] === 'Discount' && !empty($request->discount_item_id)) {
                $duplicateDiscount = $existingPromotions->contains(function ($promo) use ($request) {
                    return (($promo->type === 'Discount' && $promo->discount_item_id == $request->discount_item_id) || ($promo->type === 'Free Item' && $promo->buy_item_id == $request->discount_item_id));
                });

                if ($duplicateDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A discount for this item already exists in the selected date range.');
                }
            } else {
                $duplicateDiscountOnItem = $existingPromotions->contains(function ($promo) use ($request) {
                    return (($promo->type === 'Discount' && $promo->discount_item_id == $request->buy_item_id) || ($promo->type === 'Free Item' && $promo->buy_item_id == $request->discount_item_id));
                });

                if ($duplicateDiscountOnItem) {
                    DB::rollBack();
                    return $this->errorResponse('A discount on item already exists in the selected date range.');
                }
            }

            // Rule 3: Prevent duplicate Free Item (Buy X Get Y)
            if ($validatedData['type'] === 'Free Item') {
                $duplicateFreeItem = $existingPromotions->contains(function ($promo) use ($request) {
                    return $promo->type === 'Free Item'
                        && $promo->buy_item_id == $request->buy_item_id;
                });

                if ($duplicateFreeItem) {
                    DB::rollBack();
                    return $this->errorResponse('A "Buy X Get Y" promotion with these items already exists in the selected date range.');
                }
            }

            // Add user & company info
            $validatedData['discount_item_id'] = $request->discount_item_id;
            $validatedData['discount_type'] = $request->discount_type;
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;

            $promotion = Promotion::create($validatedData);

            DB::commit();
            return $this->successResponse($promotion, 'Promotion created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        // $promotion = Promotion::find($id);
        // if (!$promotion) {
        //     return $this->errorResponse('Promotion not found', 404);
        // }
        // return $this->successResponse($promotion);


        return $this->successResponse([
            'promotion' => $promotion->load('buyItem:id,name,code,type', 'getItem:id,name,code,type', 'discountItem:id,name,code,type')
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:255',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'type' => 'required|string|in:Discount,Free Item',
            'status' => 'required|string|in:Active,Inactive',
        ];

        // Conditional validation rules
        if ($request->type === 'Discount') {
            $validationRules['discount'] = 'required|numeric|min:0';
            $validationRules['discount_type'] = 'required|string|in:Percentage,Fixed';
            
            // Additional validation for percentage discount
            if ($request->discount_type === 'Percentage') {
                $validationRules['discount'] = 'required|numeric|min:0|max:100';
            }
            
            // Additional validation for fixed discount
            if ($request->discount_type === 'Fixed') {
                $validationRules['discount'] = 'required|numeric|min:0';
            }
        } else if ($request->type === 'Free Item') {
            $validationRules['buy_item_id'] = 'required|exists:items,id';
            $validationRules['buy_qty'] = 'required|integer|min:1';
            $validationRules['get_item_id'] = 'required|exists:items,id';
            $validationRules['get_qty'] = 'required|integer|min:1';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $promotion = Promotion::find($id);
        if (!$promotion) {
            return $this->errorResponse('Promotion not found', 404);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $start = $validatedData['start_date'];
            $end = $validatedData['end_date'];

            // Fetch overlapping promotions in this date range (exclude self)
            $existingPromotions = Promotion::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->where('id', '!=', $promotion->id) // exclude current promotion
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                        });
                })
                ->get();

            // Rule 1: Global discount conflict
            $hasGlobalDiscount = $existingPromotions->contains(function ($promo) {
                return $promo->type === 'Discount' && empty($promo->discount_item_id);
            });

            if ($validatedData['type'] === 'Discount' && empty($request->discount_item_id)) {
                if ($hasGlobalDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A global discount already exists in the selected date range. You cannot create another promotion.');
                }
            } else {
                if ($hasGlobalDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A global discount already exists in the selected date range. Additional promotions cannot be created.');
                }
            }

            // Rule 2: Prevent duplicate item-specific Discount
            if ($validatedData['type'] === 'Discount' && !empty($request->discount_item_id)) {
                $duplicateDiscount = $existingPromotions->contains(function ($promo) use ($request) {
                    return $promo->type === 'Discount' && $promo->discount_item_id == $request->discount_item_id;
                });

                if ($duplicateDiscount) {
                    DB::rollBack();
                    return $this->errorResponse('A discount for this item already exists in the selected date range.');
                }
            } else {
                $duplicateDiscountOnItem = $existingPromotions->contains(function ($promo) use ($request) {
                    return $promo->type === 'Discount' && $promo->discount_item_id == $request->buy_item_id;
                });

                if ($duplicateDiscountOnItem) {
                    DB::rollBack();
                    return $this->errorResponse('A discount on this item already exists in the selected date range.');
                }
            }

            // Rule 3: Prevent duplicate Free Item (Buy X Get Y)
            if ($validatedData['type'] === 'Free Item') {
                $duplicateFreeItem = $existingPromotions->contains(function ($promo) use ($request) {
                    return $promo->type === 'Free Item'
                        && $promo->buy_item_id == $request->buy_item_id;
                });

                if ($duplicateFreeItem) {
                    DB::rollBack();
                    return $this->errorResponse('A "Buy X Get Y" promotion with these items already exists in the selected date range.');
                }
            }

            // Update promotion
            $validatedData['discount_item_id'] = $request->discount_item_id;
            $validatedData['discount_type'] = $request->discount_type;
            $validatedData['branch_id'] = $request->branch_id;
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();

            $promotion->update($validatedData);

            DB::commit();
            return $this->successResponse($promotion, 'Promotion updated successfully');
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
        $promotion = Promotion::find($id);
        if (!$promotion) {
            return $this->errorResponse('Promotion not found', 404);
        }
        $promotion->update([
            'del_status' => 'Deleted'
        ]);
        return $this->successResponse(null, 'Promotion deleted successfully');
    }
}
