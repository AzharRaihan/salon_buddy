<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    /**
     *
     * Image Upload
     *
     * @param $query
     * @param $old
     * @param $folder
     * @param $size
     *
     */

    public function imageUpload($query, $old = null, $folder = null, $size = 2048)
    {
        try {
            $allowExt = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];
            $ext      = strtolower($query->getClientOriginalExtension());

            if ($query->getSize() > $size * 1024 * 1024) { // Convert MB to bytes
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File size exceeds ' . $size . 'MB limit',
                ], 406);
            }

            if (! in_array($ext, $allowExt)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Only allowed file types: jpeg, png, jpg, gif, svg',
                ], 406);
            }

            if ($old !== null) {
                $this->delete($old);
            }

            $image_name      = Str::random(20);
            $image_full_name = $image_name . '.' . $ext;
            $upload_path     = 'assets/images/';

            if ($folder !== null) {
                $upload_path = $upload_path . $folder . '/';
            }

            // Ensure directory exists
            if (! file_exists(public_path($upload_path))) {
                mkdir(public_path($upload_path), 0755, true);
            }

            $image_url = $upload_path . $image_full_name;
            $query->move(public_path($upload_path), $image_full_name);
            $image_url = str_replace('assets/images/', '', $image_url);

            return $image_url;
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to upload file',
            ], 500);
        }
    }

    /**
     * Delete Image
     * @param $path
     * @return void
     */
    protected function delete($path)
    {
        if (file_exists(public_path('assets/images/' . $path))) {
            @unlink(public_path('assets/images/' . $path));
        }
    }
}
