@extends('layouts.app')
@section('title', 'Novi student')
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
    <h1 class="h2 mb-4">Kreiranje studenta</h1>
    <form class="card card-body border-0 shadow-sm p-4" method="POST" action="{{ route('admin.students.store') }}">
        @csrf
        @include('admin.students.form', ['editing' => false])
        <div class="d-flex justify-content-end gap-2"><a class="btn btn-light" href="{{ route('admin.students.index') }}">Odustani</a><button class="btn btn-primary">Kreiraj studenta</button></div>
    </form>
</div></div>
@endsection
