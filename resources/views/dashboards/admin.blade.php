@extends('layouts.app')
@section('title', 'Administracija')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-primary fw-semibold mb-1">Super administrator</p>
        <h1 class="h2 mb-0">Pregled sistema</h1>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('admin.students.create') }}">Novi student</a>
        <a class="btn btn-primary" href="{{ route('admin.professors.create') }}">Novi profesor</a>
    </div>
</div>
<div class="row g-3">
    @foreach($statistics as $label => $value)
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $value }}</div>
                    <div class="text-secondary text-capitalize">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
