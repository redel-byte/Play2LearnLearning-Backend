<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

final class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Quiz::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'pass_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', 'in:multiple_choice,single_choice,true_false,short_answer'],
            'questions.*.prompt' => ['required', 'string', 'min:3'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.position' => ['required', 'integer', 'min:1'],
            'questions.*.choices' => ['nullable', 'array'],
            'questions.*.choices.*.label' => ['required_with:questions.*.choices', 'string', 'min:1'],
            'questions.*.choices.*.is_correct' => ['required_with:questions.*.choices', 'boolean'],
            'questions.*.choices.*.position' => ['required_with:questions.*.choices', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $questions = $this->input('questions', []);
            $positions = collect($questions)->pluck('position');

            if ($positions->count() !== $positions->unique()->count()) {
                $validator->errors()->add('questions', 'Question positions must be unique.');
            }

            foreach ($questions as $index => $question) {
                $type = $question['type'] ?? null;
                $choices = collect($question['choices'] ?? []);
                $correctCount = $choices->where('is_correct', true)->count();

                if (in_array($type, ['multiple_choice', 'single_choice', 'true_false'], true) && $choices->count() < 2) {
                    $validator->errors()->add("questions.{$index}.choices", 'Choice-based questions require at least two choices.');
                }

                if ($type === 'multiple_choice' && $correctCount < 1) {
                    $validator->errors()->add("questions.{$index}.choices", 'Multiple choice questions require at least one correct answer.');
                }

                if ($type === 'single_choice' && $correctCount !== 1) {
                    $validator->errors()->add("questions.{$index}.choices", 'Single choice questions require exactly one correct answer.');
                }

                if ($type === 'true_false' && ($choices->count() !== 2 || $correctCount !== 1)) {
                    $validator->errors()->add("questions.{$index}.choices", 'True/false questions require exactly two choices with one correct answer.');
                }

                if ($type === 'short_answer' && $choices->isNotEmpty()) {
                    $validator->errors()->add("questions.{$index}.choices", 'Short answer questions cannot define choices.');
                }
            }
        });
    }
}
