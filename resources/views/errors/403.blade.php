@extends('layouts.app')
@section('title', 'Pristup nije dozvoljen')
@section('content')
<div class="empty-state card border-0 shadow-sm text-center p-5 mx-auto" style="max-width: 680px"><div class="display-2 fw-bold text-primary">403</div><h1 class="h3">Nemate dozvolu za ovu stranicu</h1><p class="text-secondary">Vratite se na svoju kontrolnu tablu ili kontaktirajte administratora.</p><div><a class="btn btn-primary" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Nazad na početak</a></div></div>
@endsection
