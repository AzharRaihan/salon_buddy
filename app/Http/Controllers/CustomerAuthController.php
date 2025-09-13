<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    use FileUploadTrait;
    use ApiResponse;

    /**
     * Customer registration
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:55',
            'last_name' => 'required|string|max:55',
            'email' => 'required|email|max:55|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            // 'agree_policy' => 'required|accepted',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'agree_policy.accepted' => 'You must agree to the support policy.',
            'email.unique' => 'This email is already registered.'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            
            $customer = Customer::create([
                'name' => trim($validatedData['first_name'] . ' ' . $validatedData['last_name']),
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verified_at' => now(), // Auto-verify for simplicity
                'company_id' => 1, // Default company
                'del_status' => 'Live'
            ]);

            // Create token for the customer
            $token = $customer->createToken('customer_auth_token')->plainTextToken;

            DB::commit();
            
            return $this->successResponse([
                'customer' => $customer->makeHidden(['password']),
                'token' => $token,
            ], 'Registration successful');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Customer login
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validatedData = $validator->validated();
        $customer = Customer::where('email', $validatedData['email'])
                          ->where('del_status', 'Live')
                          ->first();

        if (!$customer) {
            return $this->errorResponse('These credentials do not match our records.', 401);
        }

        if (!Hash::check($validatedData['password'], $customer->password)) {
            return $this->errorResponse('These credentials do not match our records.', 401);
        }

        // Create token for the customer
        $token = $customer->createToken('customer_auth_token')->plainTextToken;

        return $this->successResponse([
            'customer' => $customer->makeHidden(['password']),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Get the authenticated customer
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomer(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return $this->errorResponse('Customer not authenticated', 401);
        }

        return $this->successResponse([
            'customer' => $customer->makeHidden(['password'])
        ], 'Customer data retrieved successfully');
    }

    /**
     * Customer logout
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return $this->errorResponse('Customer not authenticated', 401);
        }

        // Revoke all tokens for the customer
        $customer->tokens()->delete();
        
        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Update customer profile
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return $this->errorResponse('Customer not authenticated', 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:55',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $this->imageUpload($request->file('photo'), null, 'customers');
            }
            $customer->update([
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'date_of_anniversary' => $validatedData['date_of_anniversary'] ?? null,
                'photo' => $validatedData['photo'] ?? null,
            ]);

            return $this->successResponse([
                'customer' => $customer->makeHidden(['password'])
            ], 'Profile updated successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change customer password
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $customer = $request->user();
        if (!$customer) {
            return $this->errorResponse('Customer not authenticated', 401);
        }
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        $validatedData = $validator->validated();
        // Verify current password
        if (!Hash::check($validatedData['current_password'], $customer->password)) {
            return $this->errorResponse('Current password is incorrect.', 401);
        }
        try {
            $customer->update([
                'password' => Hash::make($validatedData['password'])
            ]);
            // Revoke all existing tokens to force re-login
            // $customer->tokens()->delete();
            return $this->successResponse(null, 'Password changed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55',
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        $validatedData = $validator->validated();
        $customer = Customer::where('email', $validatedData['email'])->first();
        if (!$customer) {
            return $this->errorResponse('Email not found.', 401);
        }
        $randomNumber = rand(10000000, 99999999);
        $customer->update([
            'password' => Hash::make($randomNumber),
        ]);
        $other_arr = [
            'tempPassword' => $randomNumber,
        ];
        $emailService = new EmailService();
        $result = $emailService->sendEmail($customer->email, 'Password Reset', $customer->id, 'Forgot Password', $other_arr);
        if($result['status'] === 'Success') {
            return $this->successResponse($customer, 'A temporary password has been sent to your email address.');
        } else {
            return $this->errorResponse('Email sending failed.', 401);
        }
    }
} 