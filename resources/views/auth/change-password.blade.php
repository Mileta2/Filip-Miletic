@extends('layouts.app')

@section('title', 'Promena lozinke')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h3 mb-2">{{ $initialChange ? 'Postavite novu lozinku' : 'Promenite lozinku' }}</h1>
                <p class="text-secondary mb-4">Lozinka mora imati najmanje 8 znakova, veliko i malo slovo i broj.</p>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    @unless($initialChange)
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Trenutna lozinka</label>
                            <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" type="password" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endunless
                    <div class="mb-3">
                        <label class="form-label" for="password">Nova lozinka</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">Potvrda nove lozinke</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Sačuvaj lozinku</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
