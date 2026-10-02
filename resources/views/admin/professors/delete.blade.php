@extends('layouts.app')
@section('title', 'Brisanje profesora')
@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <a class="text-decoration-none d-inline-block mb-3" href="{{ route('admin.professors.show', $professor) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Nazad na profesora</a>
        <div class="card border-danger shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h2 text-danger">Brisanje profesora</h1>
                <p class="lead mb-1">{{ $professor->name }}</p>
                <p class="text-secondary mb-4">Brisanjem profesora trajno se brišu i sve teme kojima je mentor.</p>

                <h2 class="h4 mb-3">Povezane teme ({{ $topics->count() }})</h2>
                @forelse($topics->groupBy(fn ($topic) => $topic->type->value) as $typeValue => $typeTopics)
                    @php($topicType = \App\Enums\TopicType::from($typeValue))
                    <section class="mb-4">
                        <h3 class="h5 border-bottom pb-2">{{ $topicType->label() }}</h3>
                        @foreach($typeTopics->groupBy('course') as $course => $courseTopics)
                            <div class="ms-lg-3 mb-3">
                                <h4 class="h6 text-primary mb-2">{{ $course }}</h4>
                                <ul class="mb-0">
                                    @foreach($courseTopics as $topic)
                                        <li class="mb-1">{{ $topic->title }} <span class="badge {{ $topic->status->badgeClass() }} ms-1">{{ $topic->status->label() }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </section>
                @empty
                    <div class="alert alert-light border">Profesor nema povezanih tema.</div>
                @endforelse

                <form method="POST" action="{{ route('admin.professors.destroy', $professor) }}" onsubmit="return confirm('Profesor i sve navedene teme biće trajno obrisani. Da li želite da nastavite?')">
                    @csrf
                    @method('DELETE')
                    <div class="form-check mb-3">
                        <input class="form-check-input" id="confirm_professor_delete" type="checkbox" required>
                        <label class="form-check-label" for="confirm_professor_delete">Razumem da će sve navedene teme biti trajno obrisane.</label>
                    </div>
                    <button class="btn btn-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i> Obriši profesora i povezane teme</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
