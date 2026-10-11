{{-- Renders one configured field: $name, $field (normalized), $value --}}
@php
    $required = in_array('required', $field['rules'], true);
    $confirmed = in_array('confirmed', $field['rules'], true);
    $type = $field['type'];
    $oldValue = old($name, $value);
@endphp

@if ($type === 'boolean')
    <div class="full">
        <label class="check">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1" @checked(filter_var($oldValue, FILTER_VALIDATE_BOOLEAN))>
            {{ $field['label'] }}
        </label>
        @if ($field['hint'])<div class="hint">{{ $field['hint'] }}</div>@endif
        @error($name)<div class="field-error">{{ $message }}</div>@enderror
    </div>
@else
    <div class="{{ $confirmed ? '' : 'full' }}">
        <label for="f-{{ $name }}">{{ $field['label'] }}</label>
        @if ($type === 'select')
            <select id="f-{{ $name }}" name="{{ $name }}" @required($required)>
                @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $oldValue === (string) $optionValue)>{{ __($optionLabel) }}</option>
                @endforeach
            </select>
        @else
            <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}"
                   value="{{ ($hidePassword ?? false) && $type === 'password' ? '' : $oldValue }}"
                   @required($required)
                   @if ($type === 'password') autocomplete="new-password" @endif>
        @endif
        @if ($field['hint'])<div class="hint">{{ $field['hint'] }}</div>@endif
        @error($name)<div class="field-error">{{ $message }}</div>@enderror
    </div>

    @if ($confirmed)
        <div>
            <label for="f-{{ $name }}_confirmation">{{ __('installer::installer.admin.confirm', ['field' => $field['label']]) }}</label>
            <input id="f-{{ $name }}_confirmation" type="{{ $type }}" name="{{ $name }}_confirmation"
                   @required($required) @if ($type === 'password') autocomplete="new-password" @endif>
        </div>
    @endif
@endif
