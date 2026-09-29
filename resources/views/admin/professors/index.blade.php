@extends('layouts.app')
@section('title', 'Profesori')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><h1 class="h2 mb-0">Profesori</h1><a class="btn btn-primary" href="{{ route('admin.professors.create') }}"><i class="bi bi-plus-lg"></i> Novi profesor</a></div>
<form class="card card-body border-0 shadow-sm mb-4" method="GET"><div class="input-group"><input class="form-control" name="search" value="{{ $search }}" placeholder="Ime, email, zvanje ili katedra"><button class="btn btn-primary">Pretraži</button></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Profesor</th><th>Zvanje</th><th>Katedra</th><th>Broj tema</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($professors as $professor)
<tr><td><strong>{{ $professor->name }}</strong><br><small class="text-secondary">{{ $professor->email }}</small></td><td>{{ $professor->professorProfile->academic_title }}</td><td>{{ $professor->professorProfile->department ?: '—' }}</td><td>{{ $professor->mentored_topics_count }}</td><td><span class="badge {{ $professor->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $professor->is_active ? 'Aktivan' : 'Neaktivan' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.professors.show', $professor) }}">Pregled</a></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-5">Nema profesora za zadate kriterijume.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-4">{{ $professors->links() }}</div>
@endsection
