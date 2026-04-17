<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Services\QuizQueryService;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class QuizController extends Controller
{
    public function __construct(
        private readonly QuizQueryService $quizQueryService,
        private readonly QuizService $quizService,
    ) {
    }

    public function index(Request $request): ResourceCollection
    {
        $quizzes = $this->quizQueryService
            ->buildIndexQuery($request, $request->user())
            ->paginate((int) $request->input('per_page', 15));

        return QuizResource::collection($quizzes);
    }

    public function store(StoreQuizRequest $request): QuizResource
    {
        $quiz = $this->quizService->create($request->validated(), (string) $request->user()->id);

        return new QuizResource($quiz->load(['creator', 'questions.choices']));
    }

    public function show(Request $request, Quiz $quiz): QuizResource
    {
        $this->authorize('view', $quiz);

        return new QuizResource($this->quizQueryService->loadForShow($request, $quiz));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): QuizResource
    {
        $this->authorize('update', $quiz);

        $quiz = $this->quizService->update($quiz, $request->validated());

        return new QuizResource($quiz->fresh(['creator', 'questions.choices']));
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('delete', $quiz);
        $this->quizService->delete($quiz);

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    public function duplicate(Request $request, Quiz $quiz): QuizResource
    {
        $this->authorize('view', $quiz);
        $this->authorize('create', Quiz::class);

        $newQuiz = $this->quizService->duplicate($quiz, (string) $request->user()->id);

        return new QuizResource($newQuiz->fresh(['creator', 'questions.choices']));
    }

    public function share(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('share', $quiz);

        $quiz = $this->quizService->share($quiz, $request->boolean('featured', false));

        return response()->json([
            'message' => 'Quiz shared successfully',
            'quiz' => new QuizResource($quiz->fresh(['creator'])),
        ]);
    }

    public function publish(Quiz $quiz): QuizResource
    {
        $this->authorize('update', $quiz);

        return new QuizResource($this->quizService->setPublicationStatus($quiz, true)->fresh(['creator']));
    }

    public function unpublish(Quiz $quiz): QuizResource
    {
        $this->authorize('update', $quiz);

        return new QuizResource($this->quizService->setPublicationStatus($quiz, false)->fresh(['creator']));
    }

    public function results(Request $request, Quiz $quiz): ResourceCollection
    {
        abort_unless(
            $request->user()->isAdmin() || $quiz->creator_id === $request->user()->id,
            403
        );

        $attempts = $quiz->attempts()
            ->with(['learner', 'answers.selectedChoices'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate((int) $request->input('per_page', 15));

        return QuizAttemptResource::collection($attempts);
    }
}
