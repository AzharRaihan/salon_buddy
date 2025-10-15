<?php

namespace App\Http\Controllers;

use App\Models\AboutusPage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\WebsiteSetting;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebsiteSettingController extends Controller
{
    use ApiResponse, FileUploadTrait;

    public function websiteSettings()
    {
        $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)->first();
        return $this->successResponse($websiteSettings, 'Website settings fetched successfully');
    }

    public function websiteSettingsUpdate(Request $request)
    {
        $validationRules = [
            'email' => 'required|email|max:55',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:255',
            // 'languages' => 'required|array|min:1',
            // 'languages.*' => 'required|string|in:en,bn,es,fr,de,zh,ja,ko',
            // 'social_media' => 'required|array',
            'testimonial_title' => 'nullable|string|max:30',
            'testimonial_heading' => 'nullable|string|max:100',
            'google_map_url' => 'nullable|url',
            'open_day_start' => 'nullable|string|max:25',
            'open_day_end' => 'nullable|string|max:25',
            'open_day_start_time' => 'nullable|string|max:25',
            'open_day_end_time' => 'nullable|string|max:25',
            'footer_copyright' => 'nullable|string|max:255',
            'footer_mini_description' => 'nullable|string|max:255',
            'testimonial_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'common_banner_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'login_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'footer_logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website_title' => 'required|string|max:255',
            'favicon' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->first();

            // Prepare data for create/update
            $data = [
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'languages' => is_array($request->languages) ? $request->languages : json_decode($request->languages, true),
                'social_media' => is_array($request->social_media) ? $request->social_media : json_decode($request->social_media, true),
                'testimonial_title' => $request->testimonial_title,
                'testimonial_heading' => $request->testimonial_heading,
                'google_map_url' => $request->google_map_url,
                'open_day_start' => $request->open_day_start,
                'open_day_end' => $request->open_day_end,
                'open_day_start_time' => $request->open_day_start_time,
                'open_day_end_time' => $request->open_day_end_time,
                'footer_copyright' => $request->footer_copyright,
                'footer_mini_description' => $request->footer_mini_description,
                'website_title' => $request->website_title,
                'user_id' => Auth::id()
            ];

            // Handle file uploads
            $imageFields = ['testimonial_image', 'common_banner_image', 'login_image', 'header_logo', 'footer_logo', 'favicon'];
            
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $folderName = str_replace('_', '-', $field);
                    $data[$field] = $this->imageUpload($request->file($field), 
                        $websiteSettings ? $websiteSettings->$field : null, 
                        $folderName);
                }
            }

            if (!$websiteSettings) {
                // Create new settings if none exist
                $data['company_id'] = Auth::user()->company_id;
                $websiteSettings = WebsiteSetting::create($data);
                
                return $this->successResponse($websiteSettings, 'Website settings created successfully');
            } else {
                // Update existing settings
                $websiteSettings->update($data);

                return $this->successResponse($websiteSettings->fresh(), 'Website settings updated successfully');
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update website settings: ' . $e->getMessage());
        }
    }


    public function websiteAboutUs()
    {
        $websiteSettings = AboutusPage::where('company_id', Auth::user()->company_id)->first();
        return $this->successResponse($websiteSettings, 'Website settings fetched successfully');
    }

    public function websiteAboutUsUpdate(Request $request)
    {
        // Check if this is a new record or update
        $isUpdate = AboutusPage::where('company_id', Auth::user()->company_id)
            ->where('del_status', 'Live')
            ->exists();

        $validationRules = [
            'section_1_heading' => 'required|string|max:100',
            'section_1_description' => 'required|string|max:250',
            'section_1_btn_link' => 'required|string|max:250',
            'section_1_experience' => 'required|string|max:100',
            'section_play_title' => 'required|string|max:100',
            'section_play_link' => 'required|string|max:250',
            'section_discover_heading' => 'required|string|max:100',
            'section_discover_description' => 'required|string|max:250',
            'section_discover_item_1_heading' => 'required|string|max:100',
            'section_discover_item_1_description' => 'required|string|max:250',
            'section_discover_item_2_heading' => 'required|string|max:100',
            'section_discover_item_2_description' => 'required|string|max:250',
            'section_discover_item_3_heading' => 'required|string|max:100',
            'section_discover_item_3_description' => 'required|string|max:250',
            'total_services_count' => 'nullable|integer',
            'total_staff_count' => 'nullable|integer',
            'total_customers_count' => 'nullable|integer',
            'total_done_services_count' => 'nullable|integer',
        ];

        // Image validation rules - required for new records, optional for updates
        $imageValidationRule = $isUpdate ? 'nullable' : 'required';
        $imageFields = [
            'section_1_image',
            'section_1_image_2',
            'section_play_image', 
            'section_discover_bg_image', 
            'section_discover_front_image', 
            'section_discover_item_1_image', 
            'section_discover_item_2_image', 
            'section_discover_item_3_image'
        ];

        foreach ($imageFields as $field) {
            $validationRules[$field] = $imageValidationRule . '|file|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $websiteSettings = AboutusPage::where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->first();

            // Prepare data for create/update (excluding image fields initially)
            $data = [
                'section_1_heading' => $request->section_1_heading,
                'section_1_description' => $request->section_1_description,
                'section_1_btn_link' => $request->section_1_btn_link,
                'total_services_count' => $request->total_services_count,
                'total_staff_count' => $request->total_staff_count,
                'total_customers_count' => $request->total_customers_count,
                'total_done_services_count' => $request->total_done_services_count,
                'section_1_experience' => $request->section_1_experience,
                'section_play_title' => $request->section_play_title,
                'section_play_link' => $request->section_play_link,
                'section_discover_heading' => $request->section_discover_heading,
                'section_discover_description' => $request->section_discover_description,
                'section_discover_item_1_heading' => $request->section_discover_item_1_heading,
                'section_discover_item_1_description' => $request->section_discover_item_1_description,
                'section_discover_item_2_heading' => $request->section_discover_item_2_heading,
                'section_discover_item_2_description' => $request->section_discover_item_2_description,
                'section_discover_item_3_heading' => $request->section_discover_item_3_heading,
                'section_discover_item_3_description' => $request->section_discover_item_3_description,
            ];

            // Handle file uploads only if files are provided
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $folderName = str_replace('_', '-', $field);
                    $data[$field] = $this->imageUpload(
                        $request->file($field), 
                        $websiteSettings ? $websiteSettings->$field : null, 
                        $folderName
                    );
                }
            }

            if (!$websiteSettings) {
                // Create new settings if none exist
                $data['company_id'] = Auth::user()->company_id;
                $data['del_status'] = 'Live';
                $websiteSettings = AboutusPage::create($data);
                
                return $this->successResponse($websiteSettings->fresh(), 'About Us content created successfully');
            } else {
                // Update existing settings
                $websiteSettings->update($data);

                return $this->successResponse($websiteSettings->fresh(), 'About Us content updated successfully');
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update About Us content: ' . $e->getMessage());
        }
    }

    public function websiteTermsAndConditions() { 
        try {
            $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)->first();
            return $this->successResponse($websiteSettings, 'Website terms and conditions fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch terms and conditions: ' . $e->getMessage());
        }
    }

    public function websiteTermsAndConditionsUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            'terms_and_conditions' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)->first();
            
            if (!$websiteSettings) {
                return $this->errorResponse('Website settings not found');
            }
            
            $websiteSettings->terms_and_conditions = $request->terms_and_conditions;
            $websiteSettings->save();
            
            return $this->successResponse($websiteSettings->fresh(), 'Website terms and conditions updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update terms and conditions: ' . $e->getMessage());
        }
    }

    public function websitePrivacyPolicy() {
        try {
            $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)->first();
            return $this->successResponse($websiteSettings, 'Website privacy policy fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch privacy policy: ' . $e->getMessage());
        }
    }

    public function websitePrivacyPolicyUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            'privacy_policy' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $websiteSettings = WebsiteSetting::where('company_id', Auth::user()->company_id)->first();
            
            if (!$websiteSettings) {
                return $this->errorResponse('Website settings not found');
            }
            
            $websiteSettings->privacy_policy = $request->privacy_policy;
            $websiteSettings->save();
            
            return $this->successResponse($websiteSettings->fresh(), 'Website privacy policy updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update privacy policy: ' . $e->getMessage());
        }
    }

}
