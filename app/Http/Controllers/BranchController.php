<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        // Filter by del_status
        $query->where('active_status', 'Active');
        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('branch_name', 'like', '%' . $request->q . '%')
                ->orWhere('branch_code', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $branches = $query->paginate($perPage);

        return $this->successResponse([
            'branches' => $branches->items(),
            'total' => $branches->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'branch_name' => 'required|string|max:55',
            'branch_code' => 'required|string|max:10|unique:branches,branch_code',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|string|max:55',
            'active_status' => 'required|in:Active,Inactive',
            'open_day_start' => 'required|string|max:25',
            'open_day_end' => 'required|string|max:25',
            'open_day_start_time' => 'required|string|max:25',
            'open_day_end_time' => 'required|string|max:25',
            'photo' => 'image|mimes:jpeg,png,jpg',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'branches');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $branch = Branch::create($validatedData);
            DB::commit();
            return $this->successResponse($branch, 'Branch created successfully');
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
        $branch = Branch::find($id);
        if (!$branch) {
            return $this->errorResponse('Branch not found', 404);
        }
        return $this->successResponse($branch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $branch = Branch::find($id);
        if (!$branch) {
            return $this->errorResponse('Branch not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'branch_name' => 'required|string|max:55',
            'branch_code' => 'required|string|max:10|unique:branches,branch_code,' . $id,
            'address' => 'required|string|max:255', 
            'phone' => 'required|string|max:25',
            'email' => 'nullable|string|max:55',
            'active_status' => 'required|in:Active,Inactive',
            'open_day_start' => 'required|string|max:25',
            'open_day_end' => 'required|string|max:25',
            'open_day_start_time' => 'required|string|max:25',
            'open_day_end_time' => 'required|string|max:25',
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
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $branch->photo, 'branches');
            }
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $branch->update($validatedData);
            DB::commit();
            return $this->successResponse($branch, 'Branch updated successfully');
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
        $branch = Branch::find($id);
        if (!$branch) {
            return $this->errorResponse('Branch not found', 404);
        }
        try {
            $branch->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Branch deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
