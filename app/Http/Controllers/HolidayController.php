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
     * Display the holiday settings for the company.
     */
    public function index(Request $request)
    {
        $holiday = Holiday::where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->first();

        return $this->successResponse($holiday);
    }

    /**
     * Store or update holiday settings.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'saturday_start' => 'nullable|string|max:25',
            'saturday_end' => 'nullable|string|max:25',
            'saturday_is_holiday' => 'nullable|string|in:Yes,No',
            'sunday_start' => 'nullable|string|max:25',
            'sunday_end' => 'nullable|string|max:25',
            'sunday_is_holiday' => 'nullable|string|in:Yes,No',
            'monday_start' => 'nullable|string|max:25',
            'monday_end' => 'nullable|string|max:25',
            'monday_is_holiday' => 'nullable|string|in:Yes,No',
            'tuesday_start' => 'nullable|string|max:25',
            'tuesday_end' => 'nullable|string|max:25',
            'tuesday_is_holiday' => 'nullable|string|in:Yes,No',
            'wednesday_start' => 'nullable|string|max:25',
            'wednesday_end' => 'nullable|string|max:25',
            'wednesday_is_holiday' => 'nullable|string|in:Yes,No',
            'thursday_start' => 'nullable|string|max:25',
            'thursday_end' => 'nullable|string|max:25',
            'thursday_is_holiday' => 'nullable|string|in:Yes,No',
            'friday_start' => 'nullable|string|max:25',
            'friday_end' => 'nullable|string|max:25',
            'friday_is_holiday' => 'nullable|string|in:Yes,No',
            'holiday_message' => 'nullable|string',
        ];

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

            // Check if holiday settings already exist
            $holiday = Holiday::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->first();

            if ($holiday) {
                // Update existing record
                $holiday->update($validatedData);
                $message = 'Holiday settings updated successfully';
            } else {
                // Create new record
                $holiday = Holiday::create($validatedData);
                $message = 'Holiday settings created successfully';
            }

            DB::commit();
            return $this->successResponse($holiday, $message);
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

        $validationRules = [
            'saturday_start' => 'nullable|string|max:25',
            'saturday_end' => 'nullable|string|max:25',
            'saturday_is_holiday' => 'nullable|string|in:Yes,No',
            'sunday_start' => 'nullable|string|max:25',
            'sunday_end' => 'nullable|string|max:25',
            'sunday_is_holiday' => 'nullable|string|in:Yes,No',
            'monday_start' => 'nullable|string|max:25',
            'monday_end' => 'nullable|string|max:25',
            'monday_is_holiday' => 'nullable|string|in:Yes,No',
            'tuesday_start' => 'nullable|string|max:25',
            'tuesday_end' => 'nullable|string|max:25',
            'tuesday_is_holiday' => 'nullable|string|in:Yes,No',
            'wednesday_start' => 'nullable|string|max:25',
            'wednesday_end' => 'nullable|string|max:25',
            'wednesday_is_holiday' => 'nullable|string|in:Yes,No',
            'thursday_start' => 'nullable|string|max:25',
            'thursday_end' => 'nullable|string|max:25',
            'thursday_is_holiday' => 'nullable|string|in:Yes,No',
            'friday_start' => 'nullable|string|max:25',
            'friday_end' => 'nullable|string|max:25',
            'friday_is_holiday' => 'nullable|string|in:Yes,No',
            'holiday_message' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $validatedData['user_id'] = Auth::id();
            $validatedData['updated_at'] = now();
            $holiday->update($validatedData);
            DB::commit();
            return $this->successResponse($holiday, 'Holiday settings updated successfully');
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
