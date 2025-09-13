<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attendance::query();
        // Join with users table
        $query->join('users', 'attendances.user_id', '=', 'users.id')
              ->select('attendances.*', 'users.name', 'users.phone');

        // Filter by del_status
        $query->where('attendances.company_id', Auth::user()->company_id);
        $query->where('attendances.del_status', 'Live');
        
        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('attendances.date', 'like', '%' . $request->q . '%')
                  ->orWhere('attendances.note', 'like', '%' . $request->q . '%')
                  ->orWhere('users.name', 'like', '%' . $request->q . '%')
                  ->orWhere('users.phone', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            // Default sorting by date DESC (newest first)
            $query->orderBy('attendances.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $attendances = $query->paginate($perPage);

        return $this->successResponse([
            'attendances' => $attendances->items(),
            'total' => $attendances->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'date' => 'nullable|string|max:55',
            'note' => 'nullable|string|max:255', 
            'in_time' => 'nullable|string|max:55',
            'out_time' => 'nullable|string|max:55',
            'total_time' => 'nullable|string|max:55',
            'user_id' => 'nullable|integer'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['del_status'] = 'Live';

            $attendance = Attendance::create($validatedData);
            DB::commit();
            return $this->successResponse($attendance, 'Attendance created successfully');
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
        $attendance = Attendance::with(['user:id,name'])->find($id);
        if (!$attendance) {
            return $this->errorResponse('Attendance not found', 404);
        }
        return $this->successResponse($attendance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return $this->errorResponse('Attendance not found', 404);
        }

        $validationRules = [
            'date' => 'nullable|string|max:55',
            'note' => 'nullable|string|max:255', 
            'in_time' => 'nullable|string|max:55',
            'out_time' => 'nullable|string|max:55',
            'total_time' => 'nullable|string|max:55',
            'user_id' => 'nullable|integer'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['updated_at'] = now();
            $validatedData['company_id'] = Auth::user()->company_id;
            $attendance->update($validatedData);
            DB::commit();
            return $this->successResponse($attendance, 'Attendance updated successfully');
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
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return $this->errorResponse('Attendance not found', 404);
        }
        $attendance->del_status = 'Deleted';
        $attendance->save();
        return $this->successResponse($attendance, 'Attendance deleted successfully');
    }
}
