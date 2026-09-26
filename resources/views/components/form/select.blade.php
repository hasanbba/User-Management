@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
])

<div class="mb-3">
    <label class="form-label" for="field-{{ $name }}">{{ $label }}</label>
    <select
        id="field-{{ $name }}"
        name="{{ $name }}"
        @class(['form-select', 'is-invalid' => $errors->has($name)])
        {{ $attributes }}
    >
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) old($name, $selected) === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
