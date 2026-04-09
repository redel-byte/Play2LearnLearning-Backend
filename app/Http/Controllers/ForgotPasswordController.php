<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;


class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent successfully',
                'status' => __($status)
            ], 200);
        }

        return response()->json([
            'error' => 'Password reset link could not be sent',
            'status' => __($status),
            'debug' => [
                'password_status' => $status,
                'email_provided' => $request->email
            ]
        ], 400);
    }
}
