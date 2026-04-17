<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt->learner_id === $this->user()->id && is_null($attempt->submitted_at);
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'uuid', 'exists:questions,id'],
            'answers.*.choice_ids' => ['sometimes', 'array'],
            'answers.*.choice_ids.*' => ['uuid', 'exists:choices,id'],
            'answers.*.text_answer' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'finalize' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $attempt = $this->route('attempt');
            $questionIds = collect($this->input('answers', []))->pluck('question_id');

            if ($questionIds->count() !== $questionIds->unique()->count()) {
                $validator->errors()->add('answers', 'Duplicate question answers are not allowed.');
            }

            foreach ($this->input('answers', []) as $index => $answer) {
                $question = $attempt->quiz->questions()->with('choices')->find($answer['question_id'] ?? null);

                if (!$question) {
                    $validator->errors()->add("answers.{$index}.question_id", 'This question does not belong to the quiz.');
                    continue;
                }

                $choiceIds = $answer['choice_ids'] ?? [];
                $textAnswer = $answer['text_answer'] ?? null;

                if ($question->isShortAnswer()) {
                    if (empty($textAnswer)) {
                        $validator->errors()->add("answers.{$index}.text_answer", 'A text answer is required.');
                    }
                    if (!empty($choiceIds)) {
                        $validator->errors()->add("answers.{$index}.choice_ids", 'Choice selections are not allowed for this question.');
                    }
                    continue;
                }

                if (empty($choiceIds)) {
                    $validator->errors()->add("answers.{$index}.choice_ids", 'At least one choice must be selected.');
                }

                $validChoiceIds = $question->choices->pluck('id')->all();
                foreach ($choiceIds as $choiceId) {
                    if (!in_array($choiceId, $validChoiceIds, true)) {
                        $validator->errors()->add("answers.{$index}.choice_ids", 'One or more selected choices do not belong to the question.');
                        break;
                    }
                }

                if (($question->isSingleChoice() || $question->isTrueFalse()) && count($choiceIds) !== 1) {
                    $validator->errors()->add("answers.{$index}.choice_ids", 'Exactly one choice must be selected for this question.');
                }
            }
        });
    }
}
