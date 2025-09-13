<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        // Filter by del_status and company
        $query->where('company_id', Auth::user()->company_id);
        $query->where('status', 'Enabled');
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('title', 'like', '%' . $request->q . '%')
                ->orWhere('description', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $faqs = $query->paginate($perPage);

        return $this->successResponse([
            'faqs' => $faqs->items(),
            'total' => $faqs->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:Enabled,Disabled',
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
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['del_status'] = 'Live';

            $faq = Faq::create($validatedData);
            
            DB::commit();
            return $this->successResponse($faq, 'FAQ created successfully');
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
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->errorResponse('FAQ not found', 404);
        }
        return $this->successResponse($faq);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->errorResponse('FAQ not found', 404);
        }

        // Validation rules
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:Enabled,Disabled',
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
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();

            $faq->update($validatedData);
            
            DB::commit();
            return $this->successResponse($faq, 'FAQ updated successfully');
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
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->errorResponse('FAQ not found', 404);
        }
        try {
            $faq->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'FAQ deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
