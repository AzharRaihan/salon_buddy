<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\SocialAuthController;

// Social authentication routes (need sessions, so placed in web.php)
Route::controller(SocialAuthController::class)->group(function () {
    Route::get('api/social-auth-urls', 'getSocialAuthUrls');
    
    // Google OAuth
    Route::get('api/auth/google', 'redirectToGoogle')->name('auth.google.redirect');
    Route::get('api/auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
    
    // GitHub OAuth
    Route::get('api/auth/github', 'redirectToGithub')->name('auth.github.redirect');
    Route::get('api/auth/github/callback', 'handleGithubCallback')->name('auth.github.callback');
    // Privacy policy rules
    Route::get('privacy-policy', 'privacyPolicy')->name('privacy-policy');

    // Facebook OAuth
    Route::get('api/auth/facebook', 'redirectToFacebook')->name('auth.facebook.redirect');
    Route::get('api/auth/facebook/callback', 'handleFacebookCallback')->name('auth.facebook.callback');

    
});

Route::get('{any?}', function () {
    return view('application');
})->where('any', '.*');

/**
 * Optimize Route
 */
Route::get('/optimize-app', function () {
    Artisan::call('optimize:clear'); // Clears cache, config, route & view cache
    Artisan::call('config:cache');   // Caches configuration
    Artisan::call('route:cache');    // Caches routes
    Artisan::call('view:cache');     // Caches views

    return response()->json(['message' => 'Application optimized successfully!']);
});
