@extends('layouts.app')
@section('title', $student->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">{{ $student->name }}</h1>
        <span class="badge {{ $student->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $student->is_active ? 'Aktivan nalog' : 'Neaktivan nalog' }}</span>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.students.edit', $student) }}">Izmeni podatke</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Osnovni podaci</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $student->email }}</dd>
                    <dt class="col-sm-4">Broj indeksa</dt><dd class="col-sm-8">{{ $student->studentProfile->index_number }}</dd>
                    <dt class="col-sm-4">Nivo studija</dt><dd class="col-sm-8">{{ $student->studentProfile->study_level->label() }}</dd>
                    <dt class="col-sm-4">Godina studija</dt><dd class="col-sm-8">{{ $student->studentProfile->study_year ?: '—' }}</dd>
                    <dt class="col-sm-4">Odabrana tema</dt><dd class="col-sm-8">{{ $student->selectedTopic?->title ?? 'Nije odabrana' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5">Upravljanje nalogom</h2>
                <form method="POST" action="{{ route('admin.students.toggle', $student) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-{{ $student->is_active ? 'danger' : 'success' }} w-100">{{ $student->is_active ? 'Deaktiviraj nalog' : 'Aktiviraj nalog' }}</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5">Nova privremena lozinka</h2>
                <form method="POST" action="{{ route('admin.students.password', $student) }}">
                    @csrf
                    @method('PUT')
                    <input class="form-control mb-2" name="password" type="password" placeholder="Nova lozinka" required>
                    <input class="form-control mb-3" name="password_confirmation" type="password" placeholder="Potvrda lozinke" required>
                    <button class="btn btn-outline-primary w-100">Resetuj lozinku</button>
                </form>
            </div>
        </div>

        <div class="card border-danger shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 text-danger">Brisanje studenta</h2>
                <p class="small text-secondary">Za brisanje unesite lozinku trenutno prijavljenog administratorskog naloga.</p>
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Da li sigurno želite da obrišete studentski nalog?')">
                    @csrf
                    @method('DELETE')
                    <label class="form-label" for="admin_password">Lozinka administratora</label>
                    <input class="form-control @error('admin_password') is-invalid @enderror mb-3" id="admin_password" name="admin_password" type="password" autocomplete="current-password" required>
                    @error('admin_password')<div class="invalid-feedback d-block mb-3">{{ $message }}</div>@enderror
                    <button class="btn btn-danger w-100"><i class="bi bi-trash me-1"></i> Obriši studenta</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
