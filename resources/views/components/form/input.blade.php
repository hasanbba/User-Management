@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
])

<div class="mb-3">
    <label class="form-label" for="field-{{ $name }}">{{ $label }}</label>
    <input
        id="field-{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @class(['form-control', 'is-invalid' => $errors->has($name)])
        @required($required)
        {{ $attributes }}
    >
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
