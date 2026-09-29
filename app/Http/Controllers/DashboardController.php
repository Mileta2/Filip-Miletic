<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return match (auth()->user()->role) {
            UserRole::SuperAdmin => redirect()->route('admin.dashboard'),
            UserRole::Professor => redirect()->route('professor.dashboard'),
            UserRole::Student => redirect()->route('student.dashboard'),
        };
    }
}
