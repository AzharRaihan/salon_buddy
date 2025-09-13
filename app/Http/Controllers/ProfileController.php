<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use FileUploadTrait;

    public function setSecurityQuestion(Request $request)
    {

        $user                 = User::find($request->user_id);
        $user->question       = $request->question;
        $user->answer         = $request->answer;
        $user->is_first_login = 1;
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Security question updated successfully',
            'user'    => $user,
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user        = User::find($request->user_id);
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->hasFile('photo')) {
            $user->photo = $this->imageUpload($request->file('photo'), $user->photo, 'users');
        }

        $user->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'user'    => $user,
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = User::find($request->user_id);

        if (! password_verify($request->old_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Old password is incorrect',
            ], 400);
        }

        // check if new password is same as old password
        if ($request->password == $request->old_password) {
            return response()->json([
                'status'  => 'error',
                'message' => 'New password cannot be same as old password',
            ], 400);
        }

        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Password changed successfully',
            'user'    => $user,
        ], 200);
    }
}
