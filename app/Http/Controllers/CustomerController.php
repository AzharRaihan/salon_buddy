<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    use ApiResponse;
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('phone', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            // Default sorting by date DESC (newest first)
            $query->orderBy('customers.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $customers = $query->paginate($perPage);

        return $this->successResponse([
            'customers' => $customers->items(),
            'total' => $customers->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = Company::where('id', Auth::user()->company_id)->select('tax_is_gst')->first();
        if($company->tax_is_gst == 'Yes'){
            $validationRules = [
                'name' => 'required|string|max:55',
                'phone' => 'required|string|max:25',
                'email' => 'required|string|max:55|unique:customers,email',
                'address' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'date_of_birth' => 'nullable|string|max:25',
                'date_of_anniversary' => 'nullable|string|max:25',
                'same_or_diff_state' => 'required|string|max:10',
                'gst_number' => 'required|string|max:55',
            ];
        }else{
            $validationRules = [
                'name' => 'required|string|max:55',
                'phone' => 'required|string|max:25',
                'email' => 'required|string|max:55|unique:customers,email',
                'address' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'date_of_birth' => 'nullable|string|max:25',
                'date_of_anniversary' => 'nullable|string|max:25',
            ];
        }
        
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo') && !str_contains($request->photo, 'default-picture.png')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'customers');
            }
            if($request->date_of_birth){
                $validatedData['date_of_birth'] = $validatedData['date_of_birth'];
            }
            if($request->date_of_anniversary){
                $validatedData['date_of_anniversary'] = $validatedData['date_of_anniversary'];
            }
            if($request->gst_number && $company->tax_is_gst == 'Yes'){
                $validatedData['gst_number'] = $validatedData['gst_number'];
            }
            if($request->same_or_diff_state && $company->tax_is_gst == 'Yes'){
                $validatedData['same_or_diff_state'] = $validatedData['same_or_diff_state'];
            }
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['del_status'] = 'Live';
            $customer = Customer::create($validatedData);
            DB::commit();
            return $this->successResponse($customer, 'Customer created successfully');
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
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        return $this->successResponse($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = Company::where('id', Auth::user()->company_id)->select('tax_is_gst')->first();
        if($company->tax_is_gst == 'Yes'){
            $validationRules = [
                'name' => 'required|string|max:55',
                'phone' => 'required|string|max:25',
                'email' => 'required|string|max:55|unique:customers,email,'.$id.',id',
                'address' => 'nullable|string|max:255', 
                'photo' => 'nullable',
                'date_of_birth' => 'nullable|string|max:25',
                'date_of_anniversary' => 'nullable|string|max:25',
                'same_or_diff_state' => 'required|string|max:10',
                'gst_number' => 'required|string|max:55',
            ];  
        }else{
            $validationRules = [
                'name' => 'required|string|max:55',
                'phone' => 'required|string|max:25',
                'email' => 'required|string|max:55|unique:customers,email,'.$id.',id',
                'address' => 'nullable|string|max:255',     
                'photo' => 'nullable',
                'date_of_birth' => 'nullable|string|max:25',
                'date_of_anniversary' => 'nullable|string|max:25',
            ];
        }
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), $customer->photo, 'customers');
            }
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $customer->update($validatedData);
            DB::commit();
            return $this->successResponse($customer, 'Customer updated successfully');
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
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        try {
            $customer->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
