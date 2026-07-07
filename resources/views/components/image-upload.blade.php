@props([
    'name'         => 'image',
    'label'        => 'Image',
    'currentImage' => null,
    'hint'         => 'Leave blank to keep current image',
    'accept'       => 'image/*',
    'required'     => false,
])

<div class="form-group">
    <label class="form-label">
        {{ $label }}@if($required)<span>*</span>@endif
    </label>

    @php
        $currentImageSrc = $currentImage;
        if ($currentImageSrc && !preg_match('/^https?:\/\//i', $currentImageSrc)) {
            $currentImageSrc = asset('storage/' . ltrim($currentImageSrc, '/'));
        }
    @endphp

    @if($currentImageSrc)
        <img src="{{ $currentImageSrc }}"
             class="img-preview" alt="Current Image"/>
    @endif

    <div class="form-file-wrap">
        <input
            type="file"
            name="{{ $name }}"
            accept="{{ $accept }}"
            {{ $required && !$currentImageSrc ? 'required' : '' }}
        />
    </div>

    @if($currentImageSrc)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>
