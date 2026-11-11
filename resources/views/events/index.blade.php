@extends('layouts.application')

@section('title', 'Administrar eventos | NUARTE')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Administrar eventos</h1>
        <p class="text-secondary mb-0">Cadastre e mantenha a agenda pública.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card app-card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Novo evento</h2>
                <form action="{{ route('admin.events.store') }}" method="POST">
                    @csrf
                    @include('events.partials.fields', ['event' => null, 'prefix' => 'create'])
                    <button class="btn btn-primary w-100" type="submit">Cadastrar evento</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @forelse ($events as $event)
            <div class="card app-card mb-3">
                <div class="card-body p-4">
                    <form action="{{ route('admin.events.update', $event) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('events.partials.fields', ['event' => $event, 'prefix' => 'event-'.$event->id])
                        <button class="btn btn-outline-primary" type="submit">Salvar</button>
                    </form>
                    <form class="d-inline-block mt-2" action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Remover este evento?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Remover</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card app-card"><div class="card-body p-5 text-center text-secondary">Nenhum evento cadastrado.</div></div>
        @endforelse
    </div>
</div>
@endsection
