@props(['selected' => '', 'globalValue' => ''])
<option value="{{ $globalValue }}" @selected((string)$selected === (string)$globalValue)>🌐 Global</option>
@foreach(\App\Support\StorefrontScopes::options() as $group => $options)
<optgroup label="{{ $group }}">
    @foreach($options as $value => $label)
    <option value="{{ $value }}" @selected((string)$selected === (string)$value)>{{ $label }}</option>
    @endforeach
</optgroup>
@endforeach
