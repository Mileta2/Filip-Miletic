@extends('layouts.app')
@section('title', 'Teme')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="text-primary fw-semibold mb-1">Katalog radova</p>
        <h1 class="h2 mb-0">{{ $mine ? 'Moje teme' : ($type === 'diplomski' ? 'Diplomske teme' : ($type === 'master' ? 'Master teme' : 'Sve teme')) }}</h1>
    </div>
    @can('create', \App\Models\Topic::class)
        <a class="btn btn-primary" href="{{ route('topics.create') }}"><i class="bi bi-plus-lg"></i> Nova tema</a>
    @endcan
</div>

<form class="card card-body border-0 shadow-sm mb-4" method="GET" action="{{ request()->url() }}">
    @if($mine)<input type="hidden" name="mine" value="1">@endif
    <div class="row g-3">
        <div class="col-md-6 col-xl-3">
            <label class="form-label" for="topic-search">Tekstualna pretraga</label>
            <input class="form-control" id="topic-search" name="search" value="{{ $search }}" placeholder="Naslov teme ili student">
        </div>
        <div class="col-md-6 col-xl-3">
            <label class="form-label" for="topic-professor">Profesor</label>
            <select class="form-select" id="topic-professor" name="mentor_id">
                <option value="">Svi profesori</option>
                @foreach($professors as $professor)
                    <option value="{{ $professor->id }}" @selected((string) $mentorId === (string) $professor->id)>{{ $professor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-xl-3">
            <label class="form-label" for="topic-course">Predmet</label>
            <select class="form-select" id="topic-course" name="course">
                <option value="">Svi predmeti</option>
                @foreach($courses as $courseOption)
                    <option value="{{ $courseOption }}" @selected($course === $courseOption)>{{ $courseOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-xl-3">
            <label class="form-label" for="topic-status">Status</label>
            <select class="form-select" id="topic-status" name="status">
                <option value="">Svi statusi</option>
                @foreach(\App\Enums\TopicStatus::cases() as $option)
                    <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>
        @if(!in_array($type, ['diplomski', 'master'], true))
            <div class="col-md-6 col-xl-3">
                <label class="form-label" for="topic-type">Nivo rada</label>
                <select class="form-select" id="topic-type" name="type">
                    <option value="">Svi nivoi</option>
                    @foreach(\App\Enums\TopicType::cases() as $option)
                        <option value="{{ $option->value }}" @selected($type === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-6 col-xl-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary flex-grow-1">Primeni filtere</button>
            <a class="btn btn-light" href="{{ request()->url() }}{{ $mine ? '?mine=1' : '' }}" title="Poništi filtere" aria-label="Poništi filtere"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<div class="row g-4">
    @forelse($topics as $topic)
        <div class="col-md-6 col-xl-4">
            <article class="card topic-card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="badge {{ $topic->status->badgeClass() }}">{{ $topic->status->label() }}</span>
                        <span class="small text-secondary">{{ $topic->type->label() }}</span>
                    </div>
                    <p class="small fw-semibold text-primary mb-2">{{ $topic->course }}</p>
                    <h2 class="h5">{{ $topic->title }}</h2>
                    <p class="text-secondary flex-grow-1">{{ \Illuminate\Support\Str::limit($topic->description, 130) }}</p>
                    <div class="small mb-3"><i class="bi bi-person-badge me-1"></i> {{ $topic->mentor->name }}</div>
                    <a class="btn btn-outline-primary stretched-link" href="{{ route('topics.show', $topic) }}">Detalji</a>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="empty-state card border-0 shadow-sm text-center p-5">
                <i class="bi bi-search fs-1 text-secondary"></i>
                <h2 class="h5 mt-3">Nema pronađenih tema</h2>
                <p class="text-secondary mb-0">Promenite kriterijume pretrage ili pokušajte kasnije.</p>
            </div>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $topics->links() }}</div>
@endsection
