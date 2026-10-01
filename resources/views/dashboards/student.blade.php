@extends('layouts.app')
@section('title', 'Student')
@section('content')
<div class="mb-4"><p class="text-primary fw-semibold mb-1">Dobro došli</p><h1 class="h2 mb-1">{{ $student->name }}</h1><p class="text-secondary">{{ $student->studentProfile->index_number }} · {{ $student->studentProfile->study_level->label() }}</p></div>
@unless($profileComplete)<div class="alert alert-warning d-flex justify-content-between align-items-center gap-3"><span>Dopunite profil osnovnim podacima.</span><a class="btn btn-sm btn-warning" href="{{ route('profile.edit') }}">Dopuni profil</a></div>@endunless
@if($student->selectedTopic)
@php($topic = $student->selectedTopic)
<div class="card border-0 shadow-sm"><div class="card-body p-4 p-lg-5"><div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><div><span class="badge {{ $topic->status->badgeClass() }} mb-2">{{ $topic->status->label() }}</span><h2 class="h3 mb-0">{{ $topic->title }}</h2></div><a class="btn btn-outline-primary align-self-start" href="{{ route('topics.show', $topic) }}">Detalji teme</a></div><div class="row g-3 text-secondary"><div class="col-md-4"><small class="d-block">Mentor</small><strong class="text-dark">{{ $topic->mentor->name }}</strong></div><div class="col-md-4"><small class="d-block">Rezervisana</small><strong class="text-dark">{{ $topic->reserved_at?->format('d.m.Y.') }}</strong></div>@if($topic->defended_at)<div class="col-md-4"><small class="d-block">Datum odbrane</small><strong class="text-dark">{{ $topic->defended_at->format('d.m.Y.') }}</strong></div>@endif</div>@if($topic->committeeMembers->isNotEmpty())<hr><h3 class="h6">Komisija</h3>@foreach($topic->committeeMembers as $member)<span class="d-block">{{ $member->role->label() }}: {{ $member->professor->name }}</span>@endforeach @endif</div></div>
@else
<div class="empty-state card border-0 shadow-sm text-center p-5"><i class="bi bi-journal-plus display-4 text-primary"></i><h2 class="h4 mt-3">Još uvek niste odabrali temu</h2><p class="text-secondary">Pregledajte slobodne teme koje odgovaraju vašem nivou studija.</p><div><a class="btn btn-primary" href="{{ route('topics.index', ['type' => $student->studentProfile->study_level->value, 'status' => 'available']) }}">Pronađi temu</a></div></div>
@endif
@endsection
