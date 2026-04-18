<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttemptQuizRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Http\Resources\QuizResource;
use App\Models\Answer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

final class AttemptController extends Controller
{
    public function joinByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = strtoupper(trim($validated['code']));

        $session = QuizSession::with(['quiz.creator'])->where('join_code', $code)->first();
        if ($session) {
            abort_unless($session->canUserJoin($request->user()), 403, 'This session is not available.');

            return response()->json([
                'type' => 'session',
                'session' => $session,
                'quiz' => new QuizResource($session->quiz->loadMissing('creator')),
            ]);
        }

        $quiz = Quiz::with('creator')->where('access_code', $code)->firstOrFail();
        abort_unless($quiz->isAvailableToUser($request->user()), 403, 'This quiz is not available.');

        return response()->json([
            'type' => 'quiz',
            'quiz' => new QuizResource($quiz),
        ]);
    }

    public function start(AttemptQuizRequest $request, Quiz $quiz): QuizAttemptResource
    {
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'learner_id' => $request->user()->id,
            'quiz_session_id' => $request->input('quiz_session_id'),
            'started_at' => now(),
        ]);

        return new QuizAttemptResource($attempt->load(['quiz.questions.choices', 'learner']));
    }

    public function show(Request $request, QuizAttempt $attempt): QuizAttemptResource
    {
        abort_unless(
            $request->user()->isAdmin()
            || $attempt->learner_id === $request->user()->id
            || $attempt->quiz->creator_id === $request->user()->id,
            403
        );

        return new QuizAttemptResource($attempt->load(['quiz.questions.choices', 'learner', 'answers.selectedChoices']));
    }

    public function submit(SubmitAnswerRequest $request, QuizAttempt $attempt): QuizAttemptResource
    {
        DB::transaction(function () use ($request, $attempt): void {
            foreach ($request->validated('answers') as $payload) {
$answer = $attempt->answers()->updateOrCreate(
                    ['question_id' => $payload['question_id']],
                    [
                        'text_answer' => $payload['text_answer'] ?? null,
                        'answered_at' => now(),
                    ]
                );

                $answer->selectedChoices()->sync($payload['choice_ids'] ?? []);
            }

            if ($request->boolean('finalize')) {
                $attempt->loadMissing('quiz.questions.choices', 'answers.selectedChoices');
                $attempt->submit();
            }
        });

        return new QuizAttemptResource($attempt->fresh(['quiz.questions.choices', 'learner', 'answers.selectedChoices']));
    }

    public function myHistory(Request $request): ResourceCollection
    {
        $attempts = $request->user()
            ->quizAttempts()
            ->with(['quiz.creator', 'answers.selectedChoices'])
            ->latest('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return QuizAttemptResource::collection($attempts);
    }
}

