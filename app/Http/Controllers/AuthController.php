<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $role = $this->normalizeRegistrationRole($request->input('role'));

        if (Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }

        $token = JWTAuth::fromUser($user);
        $request->setUserResolver(fn () => $user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user->load(['roles.permissions', 'directPermissions'])),
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();

        if (!$user->is_active) {
            return response()->json([
                'error' => 'Your account is inactive. Please contact an administrator.',
                'code' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $request->setUserResolver(fn () => $user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user->fresh()->load(['roles.permissions', 'directPermissions']))
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'error' => 'Failed to logout, please try again'
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource(auth()->user()->load(['roles.permissions', 'directPermissions', 'badges']))
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->fill($request->validated());
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user->fresh()->load(['roles.permissions', 'directPermissions', 'badges']))
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    public function destroyAccount(Request $request)
    {
        $user = $request->user();

        $user->delete();

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // The account is already soft-deleted; the client should still clear local auth state.
        }

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            
            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $newToken
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'error' => 'Failed to refresh token, please login again'
            ], 401);
        }
    }

    private function normalizeRegistrationRole(?string $role): string
    {
        return match ($role) {
            'teacher' => 'teacher',
            default => 'learner',
        };
    }
}
