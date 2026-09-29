@extends('layouts.app')
@section('title', 'Nova tema')
@section('content')
<div class="row justify-content-center"><div class="col-xl-9"><h1 class="h2 mb-4">Nova tema</h1><form class="card card-body border-0 shadow-sm p-4" method="POST" action="{{ route('topics.store') }}" enctype="multipart/form-data">@csrf @include('topics.form')<div class="d-flex justify-content-end gap-2"><a class="btn btn-light" href="{{ route('topics.index') }}">Odustani</a><button class="btn btn-primary">Sačuvaj temu</button></div></form></div></div>
@endsection
