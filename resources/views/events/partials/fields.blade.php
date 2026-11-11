@php
    $value = fn (string $field, mixed $fallback = '') => old($field, $event?->{$field} ?? $fallback);
@endphp
<div class="mb-3">
    <label class="form-label" for="{{ $prefix }}-title">Título</label>
    <input class="form-control @error('title') is-invalid @enderror" id="{{ $prefix }}-title" name="title" value="{{ $value('title') }}" maxlength="120" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="{{ $prefix }}-color">Cor</label>
    <input class="form-control form-control-color @error('color') is-invalid @enderror" id="{{ $prefix }}-color" name="color" type="color" value="{{ $value('color', '#0071C5') }}" required>
    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}-start">Início</label>
        <input class="form-control @error('start_at') is-invalid @enderror" id="{{ $prefix }}-start" name="start_at" type="datetime-local" value="{{ old('start_at', $event?->start_at?->format('Y-m-d\TH:i')) }}" required>
        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}-end">Fim</label>
        <input class="form-control @error('end_at') is-invalid @enderror" id="{{ $prefix }}-end" name="end_at" type="datetime-local" value="{{ old('end_at', $event?->end_at?->format('Y-m-d\TH:i')) }}">
        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
