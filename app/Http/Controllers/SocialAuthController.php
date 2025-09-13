<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    use ApiResponse;

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            // Store return URL in session if provided
            if ($request->has('return_url')) {
                session(['social_return_url' => $request->get('return_url')]);
            }
            
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to redirect to Google: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $customer = $this->findOrCreateCustomer($googleUser, 'google');

            $token = $customer->createToken('customer_auth_token')->plainTextToken;

            // Get return URL from session or use default
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url'); // Clear the return URL

            // Redirect to frontend with token and customer data
            $frontendUrl = config('app.frontend_url', config('app.url'));
            $customerEncoded = urlencode(json_encode([
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
                'address' => $customer->address ?? ''
            ]));
            
            return redirect($frontendUrl . $returnUrl . '?token=' . $token . '&status=success&customer=' . $customerEncoded);

        } catch (\Exception $e) {
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url');
            
            $frontendUrl = config('app.frontend_url', config('app.url'));
            return redirect($frontendUrl . $returnUrl . '?status=error&message=' . urlencode('Google authentication failed'));
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook(Request $request)
    {
        try {
            // Store return URL in session if provided
            if ($request->has('return_url')) {
                session(['social_return_url' => $request->get('return_url')]);
            }
            
            return Socialite::driver('facebook')->redirect();
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to redirect to Facebook: ' . $e->getMessage());
        }
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $customer = $this->findOrCreateCustomer($facebookUser, 'facebook');

            $token = $customer->createToken('customer_auth_token')->plainTextToken;

            // Get return URL from session or use default
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url'); // Clear the return URL

            // Redirect to frontend with token and customer data
            $frontendUrl = config('app.frontend_url', config('app.url'));
            $customerEncoded = urlencode(json_encode([
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
                'address' => $customer->address ?? ''
            ]));
            
            return redirect($frontendUrl . $returnUrl . '?token=' . $token . '&status=success&customer=' . $customerEncoded);

        } catch (\Exception $e) {
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url');
            
            $frontendUrl = config('app.frontend_url', config('app.url'));
            return redirect($frontendUrl . $returnUrl . '?status=error&message=' . urlencode('Facebook authentication failed'));
        }
    }

    /**
     * Redirect to GitHub OAuth
     */
    public function redirectToGithub(Request $request)
    {
        try {
            // Store return URL in session if provided
            if ($request->has('return_url')) {
                session(['social_return_url' => $request->get('return_url')]);
            }
            
            return Socialite::driver('github')->redirect();
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to redirect to GitHub: ' . $e->getMessage());
        }
    }

    /**
     * Handle GitHub OAuth callback
     */
    public function handleGithubCallback(Request $request)
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $customer = $this->findOrCreateCustomer($githubUser, 'github');

            $token = $customer->createToken('customer_auth_token')->plainTextToken;

            // Get return URL from session or use default
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url'); // Clear the return URL

            // Redirect to frontend with token and customer data
            $frontendUrl = config('app.frontend_url', config('app.url'));
            $customerEncoded = urlencode(json_encode([
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
                'address' => $customer->address ?? ''
            ]));
            
            return redirect($frontendUrl . $returnUrl . '?token=' . $token . '&status=success&customer=' . $customerEncoded);

        } catch (\Exception $e) {
            $returnUrl = session('social_return_url', '/frontend/login');
            session()->forget('social_return_url');
            
            $frontendUrl = config('app.frontend_url', config('app.url'));
            return redirect($frontendUrl . $returnUrl . '?status=error&message=' . urlencode('GitHub authentication failed'));
        }
    }

    /**
     * Find or create customer from social provider
     */
    private function findOrCreateCustomer($socialUser, $provider)
    {
        // First try to find customer by email
        $customer = Customer::where('email', $socialUser->getEmail())
                          ->where('del_status', 'Live')
                          ->first();

        if ($customer) {
            // Update social provider info if customer exists
            $customer->update([
                'photo' => $socialUser->getAvatar(),
                'email_verified_at' => now(), // Social users are considered verified
            ]);
            return $customer;
        }

        // Create new customer if not found
        $customer = Customer::create([
            'name' => $socialUser->getName() ?: $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(12)), // Random password for social users
            // $provider . '_id' => $socialUser->getId(),
            'photo' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social users are considered verified
            'company_id' => 1, // Default company
            'del_status' => 'Live'
        ]);

        return $customer;
    }

    /**
     * API endpoint to get social auth URLs for customers
     */
    public function getCustomerSocialAuthUrls()
    {
        try {
            return $this->successResponse([
                'google' => route('customer.auth.google.redirect'),
                'facebook' => route('customer.auth.facebook.redirect'),
                'github' => route('customer.auth.github.redirect'),
            ], 'Customer social auth URLs fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get customer social auth URLs: ' . $e->getMessage());
        }
    }
}
