<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Filter by del_status
        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('day', 'like', '%' . $request->q . '%');
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
        $holidays = $query->paginate($perPage);

        return $this->successResponse([
            'holidays' => $holidays->items(),
            'total' => $holidays->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Base validation rules
        $validationRules = [
            'day' => 'required|string|max:55',
            'start_time' => 'required|string|max:25',
            'end_time' => 'required|string|max:55',
            'auto_response' => 'nullable|string|max:10',
        ];

        if ($request->auto_response === 'Yes') {
            $validationRules['mail_subject'] = 'required|string|max:255';
            $validationRules['mail_body'] = 'required|string';
        }else{
            $validationRules['mail_subject'] = 'nullable|string|max:255';
            $validationRules['mail_body'] = 'nullable|string';
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
            $holiday = Holiday::create($validatedData);
            DB::commit();
            return $this->successResponse($holiday, 'Holiday created successfully');
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
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return $this->errorResponse('Holiday not found', 404);
        }
        return $this->successResponse($holiday);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return $this->errorResponse('Holiday not found', 404);
        }
        // Base validation rules
        $validationRules = [
            'day' => 'required|string|max:55',
            'start_time' => 'required|string|max:25',
            'end_time' => 'required|string|max:55',
            'auto_response' => 'nullable|string|max:10',
        ];

        if ($request->auto_response === 'Yes') {
            $validationRules['mail_subject'] = 'required|string|max:255';
            $validationRules['mail_body'] = 'required|string';
        }else{
            $validationRules['mail_subject'] = 'nullable|string|max:255';
            $validationRules['mail_body'] = 'nullable|string';
        }
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['mail_subject'] = $request->mail_subject ?? '';
            $validatedData['mail_body'] = $request->mail_body ?? '';
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $holiday->update($validatedData);
            DB::commit();
            return $this->successResponse($holiday, 'Holiday updated successfully');
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
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return $this->errorResponse('Holiday not found', 404);
        }
        try {
            $holiday->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Holiday deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
