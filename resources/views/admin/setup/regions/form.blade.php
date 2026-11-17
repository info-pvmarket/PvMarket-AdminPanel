@extends('layouts.admin')

@section('title', $region ? 'Edit Region' : 'Add Region')

@section('styles')
    @include('admin.setup.regions.form-styles')
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <h1 style="font-size:22px; font-weight:800;">{{ $region ? 'Edit Region' : 'Add Region' }}</h1>
    <a href="{{ route('admin.setup.regions.index') }}" class="btn-back">Back</a>
</div>

@foreach ($errors->all() as $error)
    <div class="alert-error" role="alert">{{ $error }}</div>
@endforeach

<form method="POST" action="{{ $region ? route('admin.setup.regions.update', $region['code']) : route('admin.setup.regions.store') }}">
    @csrf
    @if ($region)
        @method('PUT')
    @endif
    <div class="content-panel">
        <h2 class="section-title">Basic Information</h2>
        <div class="form-grid">
            <div class="form-group">
                <label for="region-name" class="form-label">Region name</label>
                <input id="region-name" name="name" value="{{ old('name', $region['name'] ?? '') }}" maxlength="100" required class="form-input">
            </div>
            <div class="form-group">
                @if ($region)
                    <label for="region-path" class="form-label">Storefront path</label>
                    <input id="region-path" value="{{ $region['path'] }}" readonly class="form-input">
                @else
                    <label for="region-code" class="form-label">URL slug</label>
                    <input id="region-code" name="code" value="{{ old('code') }}" maxlength="48" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="middle-east" required class="form-input">
                    <div class="form-hint">New region URLs start with /region/.</div>
                @endif
            </div>
            <div class="form-group full">
                <div id="region-countries" class="form-label">Country coverage</div>
                <p class="form-hint">The regional catalogue shows listings from warehouses in the selected countries.</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(175px, 1fr)); gap:8px; max-height:19rem; overflow:auto; padding:10px; border:1px solid var(--border); border-radius:8px;" role="group" aria-labelledby="region-countries">
                    @foreach ($countries as $code => $name)
                        <label>
                            <input type="checkbox" name="countries[]" value="{{ $code }}" @checked(in_array($code, (array) old('countries', $region['countries'] ?? []), true))>
                            {{ $name === $code ? $code : "$name ($code)" }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label for="region-status" class="form-label">Status</label>
                <select id="region-status" name="is_active" class="form-input">
                    <option value="1" @selected((bool) old('is_active', $region['is_active'] ?? true))>Active</option>
                    <option value="0" @selected(!(bool) old('is_active', $region['is_active'] ?? true))>Inactive</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Region</button>
    </div>
</form>
@endsection
