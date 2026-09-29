<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.admin', [
            'statistics' => [
                'studenti' => User::where('role', UserRole::Student)->count(),
                'profesori' => User::where('role', UserRole::Professor)->count(),
                'diplomske teme' => Topic::where('type', TopicType::Undergraduate)->count(),
                'master teme' => Topic::where('type', TopicType::Master)->count(),
                'slobodne teme' => Topic::where('status', TopicStatus::Available)->count(),
                'zauzete teme' => Topic::where('status', TopicStatus::Reserved)->count(),
                'odbranjene teme' => Topic::where('status', TopicStatus::Defended)->count(),
            ],
        ]);
    }
}
