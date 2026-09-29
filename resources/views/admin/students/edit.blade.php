@extends('layouts.app')
@section('title', 'Izmena studenta')
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
    <h1 class="h2 mb-4">Izmena studenta</h1>
    <form class="card card-body border-0 shadow-sm p-4" method="POST" action="{{ route('admin.students.update', $student) }}">
        @csrf @method('PUT')
        @include('admin.students.form', ['editing' => true])
        <div class="d-flex justify-content-end gap-2"><a class="btn btn-light" href="{{ route('admin.students.show', $student) }}">Odustani</a><button class="btn btn-primary">Sačuvaj izmene</button></div>
    </form>
</div></div>
@endsection
