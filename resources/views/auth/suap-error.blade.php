@extends('layouts.application')

@section('title', 'Login SUAP | NUARTE')

@section('content')
<div class="card app-card mx-auto my-5" style="max-width: 32rem">
    <div class="card-body p-4">
        <h1 class="h3">Login SUAP</h1>
        <p role="alert">{{ $message }}</p>
        <a class="btn btn-primary" href="{{ route($retry) }}">Tentar novamente pelo SUAP</a>
    </div>
</div>
@endsection
