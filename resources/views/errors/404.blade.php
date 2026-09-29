@extends('layouts.app')
@section('title', 'Stranica nije pronađena')
@section('content')
<div class="empty-state card border-0 shadow-sm text-center p-5 mx-auto" style="max-width: 680px"><div class="display-2 fw-bold text-primary">404</div><h1 class="h3">Stranica nije pronađena</h1><p class="text-secondary">Traženi sadržaj ne postoji ili više nije dostupan.</p><div><a class="btn btn-primary" href="{{ route('home') }}">Vrati se na početnu</a></div></div>
@endsection
