<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function step1(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'May be you have entered wrong email',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Email found. Redirecting to step 2',
        ], 200);
    }

    public function step2(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer'   => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'error'   => 'email',
                'message' => 'May be you have entered wrong email',
            ], 404);
        }

        if ($user->question !== $request->question) {
            return response()->json([
                'status'  => 'error',
                'error'   => 'question',
                'message' => 'May be you have entered wrong question',
            ], 404);
        }

        if ($user->answer !== $request->answer) {
            return response()->json([
                'status'  => 'error',
                'error'   => 'answer',
                'message' => 'May be you have entered wrong answer',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Security question and answer are correct. Redirecting to step 3',
        ], 200);
    }

    public function step3(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'May be you have entered wrong email',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password updated successfully',
        ], 200);
    }
}
