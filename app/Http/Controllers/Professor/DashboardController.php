<?php

namespace App\Http\Controllers\Professor;

use App\Enums\TopicStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = $request->user()->mentoredTopics();

        return view('dashboards.professor', [
            'statistics' => [
                'ukupno' => (clone $query)->count(),
                'slobodne' => (clone $query)->where('status', TopicStatus::Available)->count(),
                'zauzete' => (clone $query)->where('status', TopicStatus::Reserved)->count(),
                'odbranjene' => (clone $query)->where('status', TopicStatus::Defended)->count(),
            ],
            'topics' => $query->with('student')->latest()->take(8)->get(),
        ]);
    }
}
