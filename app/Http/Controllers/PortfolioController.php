<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PortfolioController extends Controller
{
    use FileUploadTrait;
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Portfolio::query();

        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);
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
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $portfolios = $query->paginate($perPage);

        return $this->successResponse([
            'portfolios' => $portfolios->items(),
            'total' => $portfolios->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate request
            $validationRules = [
                'title' => 'required|string|max:55',
                'description' => 'required|string|max:255',
                'status' => 'required|string|in:Enabled,Disabled',
                'photo' => 'required|image|mimes:jpeg,png,jpg',
                'position' => 'required|integer',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validatedData = $validator->validated();

            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'portfolios');
            }

            // Create banner
            $portfolio = Portfolio::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'photo' => $validatedData['photo'],
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
                'position' => $request->position,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Portfolio created successfully',
                'data' => $portfolio
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create portfolio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return $this->errorResponse('Portfolio not found', 404);
        }
        return $this->successResponse($portfolio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return $this->errorResponse('Portfolio not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:55',
            'description' => 'required|string|max:255',
            'status' => 'required|string|in:Enabled,Disabled',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'position' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $portfolio->photo, 'portfolios');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['position'] = $request->position;
            $validatedData['updated_at'] = now();
            $portfolio->update($validatedData);
            DB::commit();
            return $this->successResponse($portfolio, 'Portfolio updated successfully');
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
        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return $this->errorResponse('Portfolio not found', 404);
        }
        try {
            $this->delete($portfolio->photo);
            $portfolio->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Portfolio deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
