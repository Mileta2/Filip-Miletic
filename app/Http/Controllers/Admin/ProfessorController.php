<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreProfessorRequest;
use App\Http\Requests\Admin\UpdateProfessorRequest;
use App\Models\Topic;
use App\Models\User;
use App\Support\TemporaryPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $professors = User::query()
            ->where('role', UserRole::Professor)
            ->with('professorProfile')
            ->withCount(['mentoredTopics' => fn ($query) => $query->withoutGlobalScope(Topic::ACTIVE_MENTOR_SCOPE)])
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
        $initialPassword = TemporaryPassword::generate();
        $professor = DB::transaction(function () use ($data, $initialPassword) {
            $professor = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $initialPassword,
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
            ->with('success', 'Profesor je uspešno kreiran.')
            ->with('generated_password', $initialPassword);
    }

    public function show(User $professor): View
    {
        $this->ensureProfessor($professor);

        return view('admin.professors.show', [
            'professor' => $professor->load('professorProfile')->loadCount([
                'mentoredTopics' => fn ($query) => $query->withoutGlobalScope(Topic::ACTIVE_MENTOR_SCOPE),
            ]),
        ]);
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

    public function confirmDestroy(User $professor): View
    {
        $this->ensureProfessor($professor);

        $topics = $professor->mentoredTopics()
            ->withoutGlobalScope(Topic::ACTIVE_MENTOR_SCOPE)
            ->orderBy('type')
            ->orderBy('course')
            ->orderBy('title')
            ->get();

        return view('admin.professors.delete', compact('professor', 'topics'));
    }

    public function destroy(User $professor): RedirectResponse
    {
        $this->ensureProfessor($professor);
        $topics = $professor->mentoredTopics()
            ->withoutGlobalScope(Topic::ACTIVE_MENTOR_SCOPE)
            ->get();
        $pdfPaths = $topics->pluck('pdf_path')->filter()->all();

        DB::transaction(function () use ($professor, $topics) {
            $topics->each->delete();
            $professor->delete();
        });

        if ($pdfPaths !== []) {
            Storage::delete($pdfPaths);
        }

        return redirect()->route('admin.professors.index')
            ->with('success', 'Profesor i sve njegove teme su obrisani.');
    }

    private function ensureProfessor(User $professor): void
    {
        abort_unless($professor->hasRole(UserRole::Professor), 404);
    }
}
