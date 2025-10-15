<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'email',
        'phone', 
        'address',
        'languages',
        'social_media',
        'testimonial_title',
        'testimonial_heading',
        'testimonial_image',
        'common_banner_image',
        'login_image',
        'google_map_url',
        'open_day_start',
        'open_day_end',
        'open_day_start_time',
        'open_day_end_time',
        'footer_copyright',
        'footer_mini_description',
        'header_logo',
        'footer_logo',
        'privacy_policy',
        'terms_and_conditions',
        'website_title',
        'favicon',
        'company_id',
        'user_id',
        'del_status'
    ];

    protected $casts = [
        'languages' => 'array',
        'social_media' => 'array',
    ];

    protected $attributes = [
        'del_status' => 'Live',
    ];


    
    protected $appends = [
        'header_logo_url',
        'footer_logo_url',
        'testimonial_image_url',
        'common_banner_image_url',
        'login_image_url',
        'favicon_url',
    ];

    public function getHeaderLogoUrlAttribute(): string
    {
        if ($this->header_logo && file_exists(public_path('assets/images/' . $this->header_logo))) {
            return asset('assets/images/' . $this->header_logo);
        }

        return asset('assets/images/default-images/logo.png');
    }

    public function getFooterLogoUrlAttribute(): string 
    {
        if ($this->footer_logo && file_exists(public_path('assets/images/' . $this->footer_logo))) {
            return asset('assets/images/' . $this->footer_logo);
        }

        return asset('assets/images/default-images/footer_logo.png');
    } 

    public function getFaviconUrlAttribute(): string
    {
        if ($this->favicon && file_exists(public_path('assets/images/' . $this->favicon))) {
            return asset('assets/images/' . $this->favicon);
        }
        return asset('assets/images/default-images/favicon.ico');
    }

    public function getLoginImageUrlAttribute(): string
    {
        if ($this->login_image && file_exists(public_path('assets/images/' . $this->login_image))) {
            return asset('assets/images/' . $this->login_image);
        }
        return asset('assets/images/system-config/login-card.png');
    }

    

    public function getTestimonialImageUrlAttribute(): string  
    {
        if ($this->testimonial_image && file_exists(public_path('assets/images/' . $this->testimonial_image))) {
            return asset('assets/images/' . $this->testimonial_image);
        }
        return asset('assets/images/default-images/footer_bg.png');
    }
    public function getCommonBannerImageUrlAttribute(): string  
    {
        if ($this->common_banner_image && file_exists(public_path('assets/images/' . $this->common_banner_image))) {
            return asset('assets/images/' . $this->common_banner_image);
        }
        return asset('assets/images/default-images/common_banner.png');
    }

    // Relationship with Company
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Relationship with User  
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
