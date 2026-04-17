<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('quiz'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'time_limit_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1440'],
            'max_attempts' => ['sometimes', 'required', 'integer', 'min:1', 'max:20'],
            'pass_percentage' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'questions' => ['sometimes', 'array', 'min:1'],
            'questions.*.id' => ['sometimes', 'uuid'],
            'questions.*.type' => ['required_with:questions', 'in:multiple_choice,single_choice,true_false,short_answer'],
            'questions.*.prompt' => ['required_with:questions', 'string', 'min:3'],
            'questions.*.points' => ['required_with:questions', 'integer', 'min:1', 'max:100'],
            'questions.*.position' => ['required_with:questions', 'integer', 'min:1'],
            'questions.*.choices' => ['nullable', 'array'],
            'questions.*.choices.*.id' => ['sometimes', 'uuid'],
            'questions.*.choices.*.label' => ['required_with:questions.*.choices', 'string', 'min:1'],
            'questions.*.choices.*.is_correct' => ['required_with:questions.*.choices', 'boolean'],
            'questions.*.choices.*.position' => ['required_with:questions.*.choices', 'integer', 'min:1'],
        ];
    }
}
