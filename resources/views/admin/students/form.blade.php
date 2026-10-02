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
    <div class="input-group" style="max-width: 18rem;">
        <input
            class="form-control text-center @error('index_number_prefix') is-invalid @enderror"
            id="index_number_prefix"
            name="index_number_prefix"
            value="{{ old('index_number_prefix', $savedIndexPrefix) }}"
            inputmode="numeric"
            autocomplete="off"
            maxlength="8"
            pattern="[0-9]{1,8}"
            placeholder="Broj"
            aria-label="Prvi deo broja indeksa"
            required
        >
        <span class="input-group-text fw-bold" aria-hidden="true">/</span>
        <input
            class="form-control text-center @error('index_number_suffix') is-invalid @enderror"
            id="index_number_suffix"
            name="index_number_suffix"
            value="{{ old('index_number_suffix', $savedIndexSuffix) }}"
            inputmode="numeric"
            autocomplete="off"
            maxlength="8"
            pattern="[0-9]{1,8}"
            placeholder="Godina"
            aria-label="Drugi deo broja indeksa"
            required
        >
    </div>
    @error('index_number_prefix')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('index_number_suffix')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('index_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    <div class="form-text">Unesite do osam cifara sa obe strane kose crte, na primer 32009/2025.</div>
</div>

<div
    class="mb-3"
    data-student-form
    data-email-domain="{{ config('app.email_domain') }}"
    data-automatic-email="{{ $editing ? 'false' : 'true' }}"
>
    <label class="form-label" id="email_label">Email</label>
    <div class="input-group">
        <output
            class="form-control bg-body-secondary @error('email') is-invalid @enderror"
            id="email_preview"
            aria-labelledby="email_label"
            aria-live="polite"
        >{{ old('email', $editing ? $student->email : '') }}</output>
        <input
            class="form-control d-none @error('email') is-invalid @enderror"
            id="email_editor"
            type="email"
            value="{{ old('email', $editing ? $student->email : '') }}"
            autocomplete="off"
            aria-labelledby="email_label"
        >
        <input id="email" name="email" type="hidden" value="{{ old('email', $editing ? $student->email : '') }}">
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
    <div class="alert alert-info d-flex gap-2 align-items-start mb-4">
        <i class="bi bi-shield-lock fs-5" aria-hidden="true"></i>
        <div><strong>Inicijalna lozinka se generiše automatski.</strong><br><span class="small">Biće prikazana administratoru nakon uspešnog kreiranja naloga.</span></div>
    </div>
@endunless

@once
    @push('scripts')
        <script>
            (() => {
                const initializeStudentForm = () => {
                    const container = document.querySelector('[data-student-form]');

                    if (!container) {
                        return;
                    }

                    const form = container.closest('form');
                    const firstName = form.querySelector('#first_name');
                    const lastName = form.querySelector('#last_name');
                    const indexPrefix = form.querySelector('#index_number_prefix');
                    const indexSuffix = form.querySelector('#index_number_suffix');
                    const emailPreview = form.querySelector('#email_preview');
                    const emailEditor = form.querySelector('#email_editor');
                    const submittedEmail = form.querySelector('#email');
                    const editEmail = form.querySelector('[data-email-edit]');
                    const studyLevel = form.querySelector('#study_level');
                    const studyYear = form.querySelector('#study_year');
                    const automaticEmail = container.dataset.automaticEmail === 'true';
                    let customEmail = !automaticEmail || submittedEmail.value.trim() !== '';
                    let editingEmail = false;

                    const normalizeEmailPart = (value) => value
                        .trim()
                        .replaceAll('Đ', 'Dj')
                        .replaceAll('đ', 'dj')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                    const setEmail = (value) => {
                        emailPreview.textContent = value;
                        emailEditor.value = value;
                        submittedEmail.value = value;
                    };

                    const refreshEmail = () => {
                        if (!automaticEmail || customEmail) {
                            return;
                        }

                        const completed = firstName.value.trim()
                            && lastName.value.trim()
                            && indexPrefix.value
                            && indexSuffix.value;
                        const generatedEmail = completed
                            ? `${[
                                firstName.value,
                                lastName.value,
                                `${indexPrefix.value}-${indexSuffix.value}`,
                            ].map(normalizeEmailPart).join('.')}@${container.dataset.emailDomain}`
                            : '';

                        setEmail(generatedEmail);
                    };

                    const setEmailEditing = (enabled) => {
                        editingEmail = enabled;
                        emailPreview.classList.toggle('d-none', enabled);
                        emailEditor.classList.toggle('d-none', !enabled);
                        const icon = editEmail.querySelector('i');

                        if (enabled) {
                            customEmail = true;
                            icon.className = 'bi bi-lock';
                            editEmail.setAttribute('aria-label', 'Zaključaj email adresu');
                            editEmail.title = 'Zaključaj email adresu';
                            emailEditor.focus();
                            emailEditor.select();
                        } else {
                            const value = emailEditor.value.trim().toLowerCase();

                            if (value === '' && automaticEmail) {
                                customEmail = false;
                                refreshEmail();
                            } else {
                                setEmail(value);
                            }

                            icon.className = 'bi bi-pencil';
                            editEmail.setAttribute('aria-label', 'Omogući ručnu izmenu email adrese');
                            editEmail.title = 'Izmeni email adresu';
                        }
                    };

                    [indexPrefix, indexSuffix].forEach((field) => {
                        field.addEventListener('input', () => {
                            field.value = field.value.replace(/\D/g, '');
                            refreshEmail();
                        });
                    });

                    if (automaticEmail) {
                        [firstName, lastName].forEach((field) => field.addEventListener('input', refreshEmail));
                    }

                    emailEditor.addEventListener('input', () => {
                        if (editingEmail) {
                            submittedEmail.value = emailEditor.value;
                            emailPreview.textContent = emailEditor.value;
                        }
                    });
                    editEmail.addEventListener('click', () => setEmailEditing(!editingEmail));

                    const synchronizeSubmittedEmail = () => {
                        const value = editingEmail
                            ? emailEditor.value.trim().toLowerCase()
                            : emailPreview.textContent.trim();

                        submittedEmail.value = value;

                        return value;
                    };

                    form.addEventListener('submit', synchronizeSubmittedEmail);
                    form.addEventListener('formdata', (event) => {
                        event.formData.set('email', synchronizeSubmittedEmail());
                    });

                    const refreshStudyYearLimit = () => {
                        const maximumYear = studyLevel.value === 'master' ? 2 : 4;
                        studyYear.max = maximumYear;

                        if (Number(studyYear.value) > maximumYear) {
                            studyYear.value = maximumYear;
                        }
                    };

                    studyLevel.addEventListener('change', refreshStudyYearLimit);
                    refreshStudyYearLimit();
                    refreshEmail();
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeStudentForm, { once: true });
                } else {
                    initializeStudentForm();
                }
            })();
        </script>
    @endpush
@endonce
