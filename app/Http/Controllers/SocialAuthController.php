<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    use ApiResponse;

    public $companySocialAuthConfig;

    public function __construct()
    {
        $this->companySocialAuthConfig = $this->getCompanySocialAuthConfig();
    }

    public function getCompanySocialAuthConfig()
    {
        $settings = [
            'google_enabled'       => Setting::getSetting('google_enabled', false),
            'google_client_id'     => Setting::getSetting('google_client_id'),
            'google_client_secret' => Setting::getSetting('google_client_secret'),
            'google_redirect_url'  => Setting::getSetting('google_redirect_url'),
            'google_app_url'       => Setting::getSetting('google_app_url'),
            'facebook_enabled'       => Setting::getSetting('facebook_enabled', false),
            'facebook_client_id'     => Setting::getSetting('facebook_client_id'),
            'facebook_client_secret' => Setting::getSetting('facebook_client_secret'),
            'facebook_redirect_url'  => Setting::getSetting('facebook_redirect_url'),
            'facebook_app_url'       => Setting::getSetting('facebook_app_url'),
        ];
        return $settings;
    }



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
            
            // Clear any existing OAuth state to force fresh authentication
            session()->forget('oauth_state');
            
            // Force Google to always show account selection screen
            return Socialite::driver('google')
                ->with([
                    'prompt' => 'select_account',
                    'access_type' => 'offline'
                ])
                ->redirect();
                
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
            
            // Log for debugging
            \Log::info('Google OAuth callback received', [
                'user_email' => $googleUser->getEmail(),
                'user_name' => $googleUser->getName(),
                'user_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'avatar_url_length' => strlen($googleUser->getAvatar())
            ]);

            $customer = $this->findOrCreateCustomer($googleUser, 'google');

            $token = $customer->createToken('customer_auth_token')->plainTextToken;

            // Get return URL from session or use default
            $returnUrl = session('social_return_url', '/login');
            session()->forget('social_return_url'); // Clear the return URL

            // Redirect to frontend with token and customer data
            // $frontendUrl = config('app.url', config('app.url'));
            $frontendUrl = $this->companySocialAuthConfig['google_app_url'];
            $customerEncoded = urlencode(json_encode([
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
                'address' => $customer->address ?? ''
            ]));
            
            return redirect($frontendUrl . $returnUrl . '?token=' . $token . '&status=success&customer=' . $customerEncoded);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Google OAuth callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $returnUrl = session('social_return_url', '/login');
            session()->forget('social_return_url');
            
            // $frontendUrl = config('app.url', config('app.url'));
            $frontendUrl = $this->companySocialAuthConfig['google_app_url'];
            return redirect($frontendUrl . $returnUrl . '?status=error&message=' . urlencode('Google authentication failed: ' . $e->getMessage()));
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
            
            // Force Facebook to always show account selection screen
            return Socialite::driver('facebook')
                ->with(['auth_type' => 'reauthenticate'])
                ->redirect();
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
            $returnUrl = session('social_return_url', '/login');
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
            $returnUrl = session('social_return_url', '/login');
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
            
            // Force GitHub to always show account selection screen
            return Socialite::driver('github')
                ->with(['prompt' => 'select_account'])
                ->redirect();
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
            $returnUrl = session('social_return_url', '/login');
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
            $returnUrl = session('social_return_url', '/login');
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

        // Handle photo URL - use a default avatar if URL is too long or invalid
        $photoUrl = $socialUser->getAvatar();
        if (empty($photoUrl) || strlen($photoUrl) > 500) {
            $photoUrl = null; // Use default avatar from frontend
        }

        if ($customer) {
            // Update social provider info if customer exists
            try {
                $customer->update([
                    'photo' => $photoUrl,
                    'email_verified_at' => now(), // Social users are considered verified
                ]);
            } catch (\Exception $e) {
                // If photo URL is too long, update without photo
                if (strpos($e->getMessage(), 'Data too long for column') !== false) {
                    \Log::warning('Photo URL too long for existing customer, updating without photo', [
                        'customer_id' => $customer->id,
                        'email' => $customer->email,
                        'photo_url_length' => strlen($photoUrl)
                    ]);
                    
                    $customer->update([
                        'photo' => null,
                        'email_verified_at' => now(),
                    ]);
                } else {
                    throw $e; // Re-throw if it's a different error
                }
            }
            return $customer;
        }

        // Create new customer if not found
        try {
            $customer = Customer::create([
                'name' => $socialUser->getName() ?: $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(12)), // Random password for social users
                // $provider . '_id' => $socialUser->getId(),
                'photo' => $photoUrl,
                'email_verified_at' => now(), // Social users are considered verified
                'company_id' => 1, // Default company
                'del_status' => 'Live'
            ]);
        } catch (\Exception $e) {
            // If photo URL is still too long, create customer without photo
            if (strpos($e->getMessage(), 'Data too long for column') !== false) {
                \Log::warning('Photo URL too long, creating customer without photo', [
                    'email' => $socialUser->getEmail(),
                    'photo_url_length' => strlen($photoUrl)
                ]);
                
                $customer = Customer::create([
                    'name' => $socialUser->getName() ?: $socialUser->getEmail(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(12)),
                    'photo' => null, // No photo if URL is too long
                    'email_verified_at' => now(),
                    'company_id' => 1,
                    'del_status' => 'Live'
                ]);
            } else {
                throw $e; // Re-throw if it's a different error
            }
        }

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
