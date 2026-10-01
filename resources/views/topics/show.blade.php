@extends('layouts.app')
@section('title', $topic->title)
@section('content')
<div class="row g-4"><div class="col-lg-8"><article class="card border-0 shadow-sm"><div class="card-body p-4 p-lg-5"><div class="d-flex flex-wrap gap-2 mb-3"><span class="badge {{ $topic->status->badgeClass() }}">{{ $topic->status->label() }}</span><span class="badge text-bg-light">{{ $topic->type->label() }}</span></div><p class="text-primary fw-semibold mb-2">Predmet: {{ $topic->course }}</p><h1 class="h2 mb-4">{{ $topic->title }}</h1><h2 class="h5">Opis teme</h2><div class="topic-description text-secondary">{!! nl2br(e($topic->description)) !!}</div></div></article></div>
<aside class="col-lg-4"><div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5 mb-3">Podaci o radu</h2><dl class="mb-0"><dt>Mentor</dt><dd>{{ $topic->mentor->professorProfile?->academic_title }} {{ $topic->mentor->name }}</dd>@if($topic->student)<dt>Student</dt><dd>{{ $topic->student->name }}</dd>@endif @if($topic->reserved_at)<dt>Datum rezervacije</dt><dd>{{ $topic->reserved_at->format('d.m.Y.') }}</dd>@endif @if($topic->defended_at)<dt>Datum odbrane</dt><dd>{{ $topic->defended_at->format('d.m.Y.') }}</dd>@endif</dl></div></div>
@auth
@if($topic->pdf_path)<a class="btn btn-outline-primary w-100 mb-3" href="{{ route('topics.pdf.download', $topic) }}"><i class="bi bi-file-earmark-pdf"></i> Preuzmi PDF</a>@endif
@if(auth()->user()->role === \App\Enums\UserRole::Student && $topic->status === \App\Enums\TopicStatus::Available && auth()->user()->studentProfile->study_level->value === $topic->type->value && !auth()->user()->selectedTopic)
<form method="POST" action="{{ route('student.topics.select', $topic) }}" onsubmit="return confirm('Da li želite da odaberete ovu temu?')">@csrf<button class="btn btn-success w-100 mb-3">Odaberi temu</button></form>
@endif
@error('topic')<div class="alert alert-danger">{{ $message }}</div>@enderror
@can('update', $topic)<a class="btn btn-primary w-100 mb-2" href="{{ route('topics.edit', $topic) }}">Izmeni temu</a>@if($topic->pdf_path)<form method="POST" action="{{ route('topics.pdf.destroy', $topic) }}">@csrf @method('DELETE')<button class="btn btn-link text-danger w-100">Obriši PDF</button></form>@endif @endcan
@can('release', $topic)@if($topic->status === \App\Enums\TopicStatus::Reserved)<form method="POST" action="{{ route('topics.release', $topic) }}" onsubmit="return confirm('Da li želite da oslobodite temu?')">@csrf @method('PATCH')<button class="btn btn-outline-warning w-100 mt-2">Oslobodi temu</button></form>@endif @endcan
@can('defend', $topic)@if($topic->status === \App\Enums\TopicStatus::Reserved)<a class="btn btn-success w-100 mt-2" href="{{ route('topics.defense.edit', $topic) }}">Pripremi odbranu</a>@endif @endcan
@endauth
@if($topic->committeeMembers->isNotEmpty())<div class="card border-0 shadow-sm mt-4"><div class="card-body p-4"><h2 class="h5">Komisija</h2>@foreach($topic->committeeMembers as $member)<div class="mb-2"><span class="small text-secondary d-block">{{ $member->role->label() }}</span>{{ $member->professor->name }}</div>@endforeach</div></div>@endif</aside></div>
@endsection
