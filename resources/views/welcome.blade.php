@extends('layouts.app')

@section('title', 'Početna')

@section('content')
<section class="hero-panel rounded-4 p-4 p-lg-5 mb-5 text-white overflow-hidden">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <span class="badge rounded-pill bg-light text-primary mb-3">Akademski portal</span>
            <h1 class="display-5 fw-bold">Izaberite temu koja pokreće vašu budućnost</h1>
            <p class="lead text-white-50 mb-4">Jedno mesto za pregled, izbor i praćenje diplomskih i master radova.</p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-light btn-lg text-primary" href="{{ route('topics.index', ['status' => 'available']) }}">Pregledaj slobodne teme</a>
                @guest<a class="btn btn-outline-light btn-lg" href="{{ route('login') }}">Prijavi se</a>@endguest
            </div>
        </div>
        <div class="col-lg-4 text-center"><i class="bi bi-mortarboard display-1 opacity-75"></i></div>
    </div>
</section>
<div class="row g-4">
    <div class="col-md-6"><a class="topic-type-card card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('topics.undergraduate') }}"><div class="card-body p-4"><i class="bi bi-journal-text fs-1 text-primary"></i><h2 class="h4 mt-3 text-dark">Diplomski radovi</h2><p class="text-secondary mb-0">Istražite teme namenjene završetku osnovnih studija.</p></div></a></div>
    <div class="col-md-6"><a class="topic-type-card card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('topics.master') }}"><div class="card-body p-4"><i class="bi bi-lightbulb fs-1 text-danger"></i><h2 class="h4 mt-3 text-dark">Master radovi</h2><p class="text-secondary mb-0">Pronađite naprednu temu za nastavak akademskog razvoja.</p></div></a></div>
</div>
@endsection
