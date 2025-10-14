<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VacationController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vacation::query();

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
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $vacations = $query->paginate($perPage);

        return $this->successResponse([
            'vacations' => $vacations->items(),
            'total' => $vacations->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:55',
            'start_date' => 'required|string|max:25',
            'end_date' => 'required|string|max:55',
            'auto_response' => 'nullable|string|max:10',
        ];

        if ($request->auto_response === 'Yes') {
            $validationRules['message'] = 'required|string|max:255';
            $validationRules['mail_body'] = 'required|string';
        }else{
            $validationRules['message'] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['del_status'] = 'Live';
            $vacation = Vacation::create($validatedData);
            DB::commit();
            return $this->successResponse($vacation, 'Vacation created successfully');
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
        $vacation = Vacation::find($id);
        if (!$vacation) {
            return $this->errorResponse('Vacation not found', 404);
        }
        return $this->successResponse($vacation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vacation = Vacation::find($id);
        if (!$vacation) {
            return $this->errorResponse('Vacation not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'title' => 'required|string|max:55',
            'start_date' => 'required|string|max:25',
            'end_date' => 'required|string|max:55',
            'auto_response' => 'nullable|string|max:10',
        ];

        if ($request->auto_response === 'Yes') {
            $validationRules['message'] = 'required|string';
        }else{
            $validationRules['message'] = 'nullable|string|max:255';
        }
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['message'] = $request->message ?? '';
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $vacation->update($validatedData);
            DB::commit();
            return $this->successResponse($vacation, 'Vacation updated successfully');
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
        $vacation = Vacation::find($id);
        if (!$vacation) {
            return $this->errorResponse('Vacation not found', 404);
        }
        try {
            $vacation->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Vacation deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
