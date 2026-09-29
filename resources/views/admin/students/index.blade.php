@extends('layouts.app')
@section('title', 'Studenti')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h2 mb-0">Studenti</h1>
    <a class="btn btn-primary" href="{{ route('admin.students.create') }}"><i class="bi bi-plus-lg"></i> Novi student</a>
</div>
<form class="card card-body border-0 shadow-sm mb-4" method="GET">
    <div class="input-group">
        <input class="form-control" name="search" value="{{ $search }}" placeholder="Ime, prezime, indeks ili email">
        <button class="btn btn-primary">Pretraži</button>
    </div>
</form>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Student</th><th>Indeks</th><th>Nivo</th><th>Tema</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($students as $student)
                <tr>
                    <td><strong>{{ $student->name }}</strong><br><small class="text-secondary">{{ $student->email }}</small></td>
                    <td>{{ $student->studentProfile->index_number }}</td>
                    <td>{{ $student->studentProfile->study_level->label() }}</td>
                    <td>{{ $student->selectedTopic?->title ?? 'Nije odabrana' }}</td>
                    <td><span class="badge {{ $student->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $student->is_active ? 'Aktivan' : 'Neaktivan' }}</span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.students.show', $student) }}">Pregled</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">Nema studenata za zadate kriterijume.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $students->links() }}</div>
@endsection
