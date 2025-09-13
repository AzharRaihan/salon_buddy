<?php

namespace App\Http\Controllers;

use App\Models\WorkingProcess;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkingProcessController extends Controller
{
    use FileUploadTrait;
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkingProcess::query();

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
        $workingProcesses = $query->paginate($perPage);

        return $this->successResponse([
            'workingProcesses' => $workingProcesses->items(),
            'total' => $workingProcesses->total(),
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
                'title' => 'required|string|max:25',
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
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'working_processes');
            }

            // Create banner
            $workingProcess = WorkingProcess::create([
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
                'message' => 'Working process created successfully',
                'data' => $workingProcess
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create working process',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workingProcess = WorkingProcess::find($id);
        if (!$workingProcess) {
            return $this->errorResponse('Working process not found', 404);
        }
        return $this->successResponse($workingProcess);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $workingProcess = WorkingProcess::find($id);
        if (!$workingProcess) {
            return $this->errorResponse('Working process not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:25',
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
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $workingProcess->photo, 'working_processes');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['position'] = $request->position;
            $validatedData['updated_at'] = now();
            $workingProcess->update($validatedData);
            DB::commit();
            return $this->successResponse($workingProcess, 'Working process updated successfully');
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
        $workingProcess = WorkingProcess::find($id);
        if (!$workingProcess) {
            return $this->errorResponse('Working process not found', 404);
        }
        try {
            $this->delete($workingProcess->photo);
            $workingProcess->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Working process deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
