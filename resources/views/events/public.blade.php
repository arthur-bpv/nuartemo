@extends('layouts.application')

@section('title', 'Agenda | NUARTE')

@push('head')
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="display-6 fw-semibold mb-1">Agenda</h1>
        <p class="text-secondary mb-0">Acompanhe as próximas atividades do NUARTE.</p>
    </div>
</div>
<div class="card app-card">
    <div class="card-body p-3 p-md-4">
        <div id="calendar" aria-label="Calendário de eventos"></div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/index.global.min.js') }}"></script>
    <script src="{{ asset('js/core/locales-all.global.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                locale: 'pt-br',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: @json(route('events.feed')),
                navLinks: true,
                dayMaxEvents: true,
                height: 'auto'
            });
            calendar.render();
        });
    </script>
@endpush
