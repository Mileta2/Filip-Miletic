@php
    $savedIndex = $editing ? $student->studentProfile->index_number : '';
    [$savedIndexPrefix, $savedIndexSuffix] = array_pad(explode('/', $savedIndex, 2), 2, '');
@endphp

@if($editing)
    <div class="mb-3">
        <label class="form-label" for="name">Ime i prezime</label>
        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $student->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@else
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="first_name">Ime</label>
            <input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="last_name">Prezime</label>
            <input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Broj indeksa</label>
    <div class="index-number-fields">
        <input
            class="form-control text-center @error('index_number_prefix') is-invalid @enderror"
            id="index_number_prefix"
            name="index_number_prefix"
            value="{{ old('index_number_prefix', $savedIndexPrefix) }}"
            inputmode="numeric"
            autocomplete="off"
            maxlength="4"
            pattern="[0-9]{1,4}"
            placeholder="___"
            aria-label="Prvi deo broja indeksa"
            required
        >
        <span class="index-number-separator" aria-hidden="true">/</span>
        <input
            class="form-control text-center @error('index_number_suffix') is-invalid @enderror"
            id="index_number_suffix"
            name="index_number_suffix"
            value="{{ old('index_number_suffix', $savedIndexSuffix) }}"
            inputmode="numeric"
            autocomplete="off"
            maxlength="2"
            pattern="[0-9]{2}"
            placeholder="__"
            aria-label="Drugi deo broja indeksa"
            required
        >
    </div>
    @error('index_number_prefix')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('index_number_suffix')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('index_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    <div class="form-text">Unesite oba dela indeksa, na primer 108/22.</div>
</div>

<div
    class="mb-3"
    data-student-email-generator
    data-email-domain="{{ config('app.email_domain') }}"
    data-automatic-email="{{ $editing ? 'false' : 'true' }}"
>
    <label class="form-label" for="email">Email</label>
    <div class="input-group">
        <input
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $editing ? $student->email : '') }}"
            readonly
            @required($editing)
        >
        <button class="btn btn-outline-primary" type="button" data-email-edit aria-label="Omogući ručnu izmenu email adrese" title="Izmeni email adresu">
            <i class="bi bi-pencil" aria-hidden="true"></i>
        </button>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-text">Adresa se automatski formira iz imena, prezimena i broja indeksa. Olovkom se omogućava ručna izmena.</div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label" for="study_level">Nivo studija</label>
        <select class="form-select" id="study_level" name="study_level">
            @foreach(\App\Enums\StudyLevel::cases() as $level)
                <option value="{{ $level->value }}" @selected(old('study_level', $editing ? $student->studentProfile->study_level->value : '') === $level->value)>{{ $level->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="study_year">Godina studija</label>
        <input class="form-control @error('study_year') is-invalid @enderror" id="study_year" name="study_year" type="number" min="1" max="4" value="{{ old('study_year', $editing ? $student->studentProfile->study_year : 1) }}" required>
        @error('study_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="is_active">Status naloga</label>
        <select class="form-select" id="is_active" name="is_active">
            <option value="1" @selected(old('is_active', $editing ? (int) $student->is_active : 1) == 1)>Aktivan</option>
            <option value="0" @selected(old('is_active', $editing ? (int) $student->is_active : 1) == 0)>Neaktivan</option>
        </select>
    </div>
</div>

@unless($editing)
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="password">Inicijalna lozinka</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-4">
            <label class="form-label" for="password_confirmation">Potvrda lozinke</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
    </div>
@endunless
