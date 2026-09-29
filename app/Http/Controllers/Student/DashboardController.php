<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->load(['studentProfile', 'selectedTopic.mentor.professorProfile', 'selectedTopic.committeeMembers.professor']);

        return view('dashboards.student', [
            'student' => $student,
            'profileComplete' => (bool) $student->studentProfile->date_of_birth,
        ]);
    }

    public function topic(Request $request): RedirectResponse
    {
        $topic = $request->user()->selectedTopic;

        return $topic
            ? redirect()->route('topics.show', $topic)
            : redirect()->route('student.dashboard')->with('warning', 'Još uvek niste odabrali temu.');
    }
}
