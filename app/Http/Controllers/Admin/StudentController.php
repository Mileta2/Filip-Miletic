<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteStudentRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\User;
use App\Support\TemporaryPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $students = User::query()
            ->where('role', UserRole::Student)
            ->with(['studentProfile', 'selectedTopic'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('studentProfile', fn ($query) => $query->where('index_number', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create(): View
    {
        return view('admin.students.create');
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $initialPassword = TemporaryPassword::generate();

        $student = DB::transaction(function () use ($data, $initialPassword) {
            $student = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $initialPassword,
                'role' => UserRole::Student,
                'must_change_password' => true,
                'is_active' => $data['is_active'],
            ]);
            $student->studentProfile()->create([
                'index_number' => $data['index_number'],
                'study_level' => $data['study_level'],
                'study_year' => $data['study_year'],
            ]);

            return $student;
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student je uspešno kreiran.')
            ->with('generated_password', $initialPassword);
    }

    public function show(User $student): View
    {
        $this->ensureStudent($student);

        return view('admin.students.show', ['student' => $student->load('studentProfile', 'selectedTopic.mentor')]);
    }

    public function edit(User $student): View
    {
        $this->ensureStudent($student);

        return view('admin.students.edit', ['student' => $student->load('studentProfile')]);
    }

    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $data = $request->validated();

        DB::transaction(function () use ($student, $data) {
            $student->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ]);
            $student->studentProfile->update([
                'index_number' => $data['index_number'],
                'study_level' => $data['study_level'],
                'study_year' => $data['study_year'],
            ]);
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Podaci o studentu su sačuvani.');
    }

    public function resetPassword(ResetPasswordRequest $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $student->update([
            'password' => $request->validated('password'),
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Privremena lozinka je postavljena.');
    }

    public function toggle(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $student->update(['is_active' => ! $student->is_active]);

        return back()->with('success', $student->is_active ? 'Nalog je aktiviran.' : 'Nalog je deaktiviran.');
    }

    public function destroy(DeleteStudentRequest $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $request->validated();
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Studentski nalog je obrisan. Podaci o radovima ostali su sačuvani.');
    }

    private function ensureStudent(User $student): void
    {
        abort_unless($student->hasRole(UserRole::Student), 404);
    }
}
