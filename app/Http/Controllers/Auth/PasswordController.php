<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'initialChange' => $request->user()->must_change_password,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];

        if (! $request->user()->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        $request->session()->regenerate();

        if ($user->hasRole(UserRole::Student) && ! $user->studentProfile->date_of_birth) {
            return redirect()->route('profile.edit')->with('success', 'Lozinka je promenjena. Dopunite svoj profil.');
        }

        return redirect()->route('dashboard')->with('success', 'Lozinka je uspešno promenjena.');
    }
}
