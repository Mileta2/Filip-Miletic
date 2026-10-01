<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load(['studentProfile', 'professorProfile']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $common = $request->validate(['name' => ['required', 'string', 'max:255']]);

        DB::transaction(function () use ($request, $user, $common) {
            $user->update($common);

            if ($user->hasRole(UserRole::Student)) {
                $data = $request->validate([
                    'date_of_birth' => ['required', 'date', 'before:today'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                ]);
                $user->studentProfile->update($data);
            }

            if ($user->hasRole(UserRole::Professor)) {
                $data = $request->validate([
                    'academic_title' => ['required', 'string', 'max:150'],
                    'department' => ['nullable', 'string', 'max:255'],
                    'research_area' => ['nullable', 'string', 'max:2000'],
                ]);
                $user->professorProfile->update($data);
            }
        });

        return back()->with('success', 'Profil je uspešno sačuvan.');
    }
}
