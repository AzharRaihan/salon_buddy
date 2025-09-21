<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    use FileUploadTrait;
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        $query->where('del_status', 'Live');
        $query->where('company_id', Auth::user()->company_id);
        $query->where('del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where('banner_tag', 'like', '%' . $request->q . '%')
                ->orWhere('banner_title', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && !empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $banners = $query->paginate($perPage);

        return $this->successResponse([
            'banners' => $banners->items(),
            'total' => $banners->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate request
            $validationRules = [
                'banner_tag' => 'required|string|max:25',
                'banner_title' => 'required|string|max:55',
                'banner_description' => 'required|string|max:255',
                'status' => 'required|string|in:Enabled,Disabled',
                'banner_image' => 'required|image|mimes:jpeg,png,jpg',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validatedData = $validator->validated();

            // Handle photo upload if provided
            if ($request->hasFile('banner_image')) {
                $validatedData['banner_image'] = $this->imageUpload($request->file('banner_image'), null, 'banners');
            }

            // Create banner
            $banner = Banner::create([
                'banner_tag' => $request->banner_tag,
                'banner_title' => $request->banner_title,
                'banner_description' => $request->banner_description,
                'status' => $request->status,
                'banner_image' => $validatedData['banner_image'],
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Update other banner is disabled except the current banner
            Banner::where('id', '!=', $banner->id)->update(['status' => 'Disabled']);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Banner created successfully',
                'data' => $banner
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return $this->errorResponse('Banner not found', 404);
        }
        return $this->successResponse($banner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $banner = Banner::find($id);
        if (!$banner) {
            return $this->errorResponse('Banner not found', 404);
        }

        // Base validation rules
        $validationRules = [
            'banner_tag' => 'required|string|max:25',
            'banner_title' => 'required|string|max:55',
            'banner_description' => 'required|string|max:255',
            'status' => 'required|string|in:Enabled,Disabled',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            if ($request->hasFile('banner_image')) {
                $validatedData['banner_image'] = $this->imageUpload($request->file('banner_image'), $banner->banner_image, 'banners');
            }
            // Add user and company information
            $validatedData['user_id'] = Auth::id();
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['updated_at'] = now();
            $banner->update($validatedData);

            // Update other banner is disabled except the current banner
            Banner::where('id', '!=', $banner->id)->update(['status' => 'Disabled']);

            DB::commit();
            return $this->successResponse($banner, 'Banner updated successfully');
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
        $banner = Banner::find($id);
        if (!$banner) {
            return $this->errorResponse('Banner not found', 404);
        }
        try {
            $this->delete($banner->banner_image);
            $banner->update(['del_status' => 'Deleted']);
            return $this->successResponse(null, 'Banner deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
