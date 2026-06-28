<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizWeek;
use App\Models\UserQuizAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicQuizController extends Controller
{
    public function index(Request $request): Response
    {
        // Fetch all weeks to allow selection
        $allWeeks = QuizWeek::withCount(['questions' => function ($query) {
            $query->where('is_active', true);
        }])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('week_number')
            ->get()
            ->map(fn (QuizWeek $week) => [
                'id' => $week->id,
                'label' => $week->getLabel(),
                'theme' => $week->theme,
                'questions_count' => $week->questions_count,
            ]);

        return Inertia::render('quizzes/Index', [
            'allWeeks' => $allWeeks,
        ]);
    }

    public function play(QuizWeek $quizWeek, Request $request): Response
    {
        // Load active questions for this week
        $quizWeek->load(['questions' => function ($query) {
            $query->where('is_active', true)->orderBy('created_at');
        }]);

        // Transform the active quiz week
        $quizData = [
            'id' => $quizWeek->id,
            'label' => $quizWeek->getLabel(),
            'theme' => $quizWeek->theme,
            'questions' => $quizWeek->questions->map(fn ($q) => [
                'id' => $q->id,
                'type' => $q->type,
                'content' => $q->content,
                'options' => $q->options,
                'correct_answer' => $q->correct_answer,
                'image_path' => $q->image_path ? \Storage::url($q->image_path) : null,
                'points' => $q->points,
                'difficulty' => $q->difficulty,
            ]),
        ];

        // Real leaderboard data
        $leaderboardQuery = UserQuizAnswer::where('is_correct', true)
            ->selectRaw('user_id, SUM(points_awarded) as score')
            ->groupBy('user_id')
            ->orderByDesc('score')
            ->with('user:id,name')
            ->get();

        $leaderboard = [];
        $rank = 1;
        $userTotalScore = 0;
        $userId = $request->user()?->id;

        foreach ($leaderboardQuery as $item) {
            if ($item->user_id == $userId) {
                $userTotalScore = $item->score;
            }

            if ($rank <= 10) {
                $leaderboard[] = [
                    'rank' => $rank,
                    'name' => $item->user->name ?? 'Người dùng',
                    'score' => (int) $item->score,
                    'is_me' => $item->user_id == $userId,
                ];
            }
            $rank++;
        }

        return Inertia::render('quizzes/Play', [
            'quizWeek' => $quizData,
            'leaderboard' => $leaderboard,
            'userTotalScore' => (int) $userTotalScore,
        ]);
    }

    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|string',
        ]);

        $user = $request->user();
        $question = Question::findOrFail($validated['question_id']);

        // Check if the user already answered this question correctly
        $existingCorrectAnswer = UserQuizAnswer::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->where('is_correct', true)
            ->first();

        if ($existingCorrectAnswer) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã trả lời đúng câu hỏi này rồi.',
                'is_correct' => true,
            ], 422);
        }

        // Delete previous wrong answers so the user can retry
        UserQuizAnswer::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->where('is_correct', false)
            ->delete();

        $isCorrect = false;

        // Simple exact match evaluation for now. Could be improved for fill-in-the-blank types.
        if (strcasecmp(trim($validated['answer']), trim($question->correct_answer)) === 0) {
            $isCorrect = true;
        }

        $pointsAwarded = $isCorrect ? $question->points : 0;

        $userAnswer = UserQuizAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
        ]);

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'correct_answer' => $question->correct_answer,
        ]);
    }

    public function leaderboard(Request $request): Response
    {
        // Real leaderboard data
        $leaderboardQuery = UserQuizAnswer::where('is_correct', true)
            ->selectRaw('user_id, SUM(points_awarded) as score')
            ->groupBy('user_id')
            ->orderByDesc('score')
            ->with('user:id,name,role,avatar,branch')
            ->get();

        $leaderboard = [];
        $rank = 1;
        $userTotalScore = 0;
        $userRank = null;
        $userId = $request->user()?->id;

        foreach ($leaderboardQuery as $item) {
            $isMe = $item->user_id == $userId;
            if ($isMe) {
                $userTotalScore = $item->score;
                $userRank = $rank;
            }

            $leaderboard[] = [
                'rank' => $rank,
                'name' => $item->user->name ?? 'Người dùng',
                'role' => $item->user->role ?? null,
                'branch' => $item->user->branch ?? null,
                'avatar' => $item->user->avatar ? \Storage::url($item->user->avatar) : null,
                'score' => (int) $item->score,
                'is_me' => $isMe,
            ];

            $rank++;
        }

        return Inertia::render('quizzes/Leaderboard', [
            'leaderboard' => $leaderboard,
            'userTotalScore' => (int) $userTotalScore,
            'userRank' => $userRank,
        ]);
    }
}
