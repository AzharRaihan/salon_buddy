<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Filter by del_status
        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

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
            $query->orderBy('categories.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $categories = $query->paginate($perPage);

        return $this->successResponse([
            'categories' => $categories->items(),
            'total' => $categories->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'type' => 'required|string|in:Service,Product',
            'name' => 'required|string|max:55',
            'description' => 'nullable|string|max:255',
            'status' => 'required|string|in:Enabled,Disabled',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'categories');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $category = Category::create($validatedData);
            DB::commit();
            return $this->successResponse($category, 'Category created successfully');
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
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        return $this->successResponse($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'type' => 'required|string|in:Service,Product',
            'name' => 'required|string|max:55',
            'description' => 'nullable|string|max:255', 
            'status' => 'required|string|in:Enabled,Disabled',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $category->photo, 'categories');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $category->update($validatedData);
            DB::commit();
            return $this->successResponse($category, 'Category updated successfully');
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
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        try {
            $category->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
