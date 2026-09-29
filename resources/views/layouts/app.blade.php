<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem za izbor tema') | Visoka škola</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">Sistem za izbor tema</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Početna</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('topics.undergraduate') }}">Diplomski</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('topics.master') }}">Master</a></li>
                    @auth
                        @unless(auth()->user()->must_change_password)
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Kontrolna tabla</a></li>
                            @if(auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                                <li class="nav-item"><a class="nav-link" href="{{ route('topics.index') }}">Teme</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('admin.students.index') }}">Studenti</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('admin.professors.index') }}">Profesori</a></li>
                            @elseif(auth()->user()->role === \App\Enums\UserRole::Professor)
                                <li class="nav-item"><a class="nav-link" href="{{ route('topics.index', ['mine' => 1]) }}">Moje teme</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('topics.create') }}">Nova tema</a></li>
                            @elseif(auth()->user()->role === \App\Enums\UserRole::Student)
                                <li class="nav-item"><a class="nav-link" href="{{ route('topics.index', ['status' => 'available']) }}">Teme</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('student.topic') }}">Moja tema</a></li>
                            @endif
                            <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Profil</a></li>
                        @endunless
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-link nav-link" type="submit">Odjava</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item"><a class="btn btn-light text-primary ms-lg-2" href="{{ route('login') }}">Prijava</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            @foreach (['success', 'warning', 'error'] as $messageType)
                @if (session($messageType))
                    <div class="alert alert-{{ $messageType === 'error' ? 'danger' : $messageType }} alert-dismissible fade show">
                        {{ session($messageType) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach
            @yield('content')
        </div>
    </main>
</body>
</html>
