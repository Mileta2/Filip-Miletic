<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreProfessorRequest;
use App\Http\Requests\Admin\UpdateProfessorRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $professors = User::query()
            ->where('role', UserRole::Professor)
            ->with('professorProfile')
            ->withCount('mentoredTopics')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('professorProfile', fn ($query) => $query
                            ->where('academic_title', 'like', "%{$search}%")
                            ->orWhere('department', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.professors.index', compact('professors', 'search'));
    }

    public function create(): View
    {
        return view('admin.professors.create');
    }

    public function store(StoreProfessorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $professor = DB::transaction(function () use ($data) {
            $professor = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Professor,
                'must_change_password' => true,
                'is_active' => $data['is_active'],
            ]);
            $professor->professorProfile()->create([
                'academic_title' => $data['academic_title'],
                'department' => $data['department'] ?? null,
                'research_area' => $data['research_area'] ?? null,
            ]);

            return $professor;
        });

        return redirect()->route('admin.professors.show', $professor)
            ->with('success', 'Profesor je uspešno kreiran.');
    }

    public function show(User $professor): View
    {
        $this->ensureProfessor($professor);

        return view('admin.professors.show', ['professor' => $professor->load('professorProfile')->loadCount('mentoredTopics')]);
    }

    public function edit(User $professor): View
    {
        $this->ensureProfessor($professor);

        return view('admin.professors.edit', ['professor' => $professor->load('professorProfile')]);
    }

    public function update(UpdateProfessorRequest $request, User $professor): RedirectResponse
    {
        $this->ensureProfessor($professor);
        $data = $request->validated();
        DB::transaction(function () use ($professor, $data) {
            $professor->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ]);
            $professor->professorProfile->update([
                'academic_title' => $data['academic_title'],
                'department' => $data['department'] ?? null,
                'research_area' => $data['research_area'] ?? null,
            ]);
        });

        return redirect()->route('admin.professors.show', $professor)
            ->with('success', 'Podaci o profesoru su sačuvani.');
    }

    public function resetPassword(ResetPasswordRequest $request, User $professor): RedirectResponse
    {
        $this->ensureProfessor($professor);
        $professor->update([
            'password' => $request->validated('password'),
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Privremena lozinka je postavljena.');
    }

    public function toggle(User $professor): RedirectResponse
    {
        $this->ensureProfessor($professor);
        $professor->update(['is_active' => ! $professor->is_active]);

        return back()->with('success', $professor->is_active ? 'Nalog je aktiviran.' : 'Nalog je deaktiviran.');
    }

    public function destroy(User $professor): RedirectResponse
    {
        $this->ensureProfessor($professor);

        if ($professor->mentoredTopics()->exists() || $professor->committeeMemberships()->exists()) {
            return back()->with('error', 'Profesor ima povezane radove i ne može biti obrisan. Deaktivirajte nalog.');
        }

        $professor->delete();

        return redirect()->route('admin.professors.index')->with('success', 'Profesor je obrisan.');
    }

    private function ensureProfessor(User $professor): void
    {
        abort_unless($professor->hasRole(UserRole::Professor), 404);
    }
}
