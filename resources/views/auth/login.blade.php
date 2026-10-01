@extends('layouts.app')

@section('title', 'Prijava')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h3 mb-2">Prijava na sistem</h1>
                <p class="text-secondary mb-4">Unesite podatke koje ste dobili od administratora.</p>
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email adresa</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" placeholder="ime.prezime@ftnkm.rs" value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Lozinka</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                        <label class="form-check-label" for="remember">Zapamti me</label>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Prijavi se</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
