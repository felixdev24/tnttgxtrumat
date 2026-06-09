<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuizWeek;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedWeekId = $request->query('week_id');

        $quizWeeks = QuizWeek::withCount('questions')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('week_number')
            ->get()
            ->map(fn (QuizWeek $week) => [
                'id' => $week->id,
                'label' => $week->getLabel(),
                'week_number' => $week->week_number,
                'month' => $week->month,
                'year' => $week->year,
                'theme' => $week->theme,
                'questions_count' => $week->questions_count,
            ]);

        $selectedWeek = null;
        $questions = [];

        if ($selectedWeekId) {
            $selectedWeek = QuizWeek::find($selectedWeekId);
        } elseif ($quizWeeks->isNotEmpty()) {
            $selectedWeek = QuizWeek::find($quizWeeks->first()['id']);
        }

        if ($selectedWeek) {
            $questions = $selectedWeek->questions()
                ->orderBy('created_at')
                ->get()
                ->map(fn (Question $q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'content' => $q->content,
                    'options' => $q->options,
                    'correct_answer' => $q->correct_answer,
                    'image_path' => $q->image_path ? Storage::url($q->image_path) : null,
                    'points' => $q->points,
                    'difficulty' => $q->difficulty,
                    'is_active' => $q->is_active,
                ]);
        }

        return Inertia::render('dashboard/quizzes/Index', [
            'quizWeeks' => $quizWeeks,
            'selectedWeek' => $selectedWeek ? [
                'id' => $selectedWeek->id,
                'label' => $selectedWeek->getLabel(),
                'week_number' => $selectedWeek->week_number,
                'month' => $selectedWeek->month,
                'year' => $selectedWeek->year,
                'theme' => $selectedWeek->theme,
            ] : null,
            'questions' => $questions,
        ]);
    }

    public function storeWeek(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'week_number' => ['required', 'integer', 'min:1', 'max:5'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'theme' => ['nullable', 'string', 'max:255'],
        ]);

        $week = QuizWeek::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('dashboard.quizzes.index', ['week_id' => $week->id])
            ->with('success', 'Đã tạo tuần câu hỏi mới!');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_week_id' => ['required', 'exists:quiz_weeks,id'],
            'type' => ['required', Rule::in(['multiple_choice', 'fill_blank', 'image_guess'])],
            'content' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'correct_answer' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'points' => ['required', 'integer', 'min:1'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('quizzes/images', 'public');
        }

        Question::create([
            'quiz_week_id' => $validated['quiz_week_id'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'options' => $validated['options'] ?? null,
            'correct_answer' => $validated['correct_answer'],
            'image_path' => $imagePath,
            'points' => $validated['points'],
            'difficulty' => $validated['difficulty'],
            'is_active' => true,
        ]);

        return redirect()->route('dashboard.quizzes.index', ['week_id' => $validated['quiz_week_id']])
            ->with('success', 'Câu hỏi đã được lưu thành công!');
    }

    public function destroyQuestion(Question $question): RedirectResponse
    {
        $weekId = $question->quiz_week_id;

        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->delete();

        return redirect()->route('dashboard.quizzes.index', ['week_id' => $weekId])
            ->with('success', 'Đã xóa câu hỏi!');
    }
}
