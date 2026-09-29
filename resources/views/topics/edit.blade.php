@extends('layouts.app')
@section('title', 'Izmena teme')
@section('content')
<div class="row justify-content-center"><div class="col-xl-9"><h1 class="h2 mb-4">Izmena teme</h1><form class="card card-body border-0 shadow-sm p-4" method="POST" action="{{ route('topics.update', $topic) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('topics.form')<div class="d-flex justify-content-end gap-2"><a class="btn btn-light" href="{{ route('topics.show', $topic) }}">Odustani</a><button class="btn btn-primary">Sačuvaj izmene</button></div></form></div></div>
@endsection
