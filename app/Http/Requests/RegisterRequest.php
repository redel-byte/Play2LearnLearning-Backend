<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Name is required.',
            'first_name.string' => 'Name must be a string.',
            'first_name.max' => 'Name cannot exceed 50 characters.',
            'last_name.required' => 'Name is required.',
            'last_name.string' => 'Name must be a string.',
            'last_name.max' => 'Name cannot exceed 50 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];
    }
}
