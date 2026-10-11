@extends('installer::layout')

@section('content')
    <h2>{{ config('installer.admin.title', 'Administrator Account') }}</h2>
    <p class="intro">{{ config('installer.admin.description') }}</p>

    <form method="POST" action="{{ route('installer.admin.save') }}">
        @csrf
        <div class="grid">
            @foreach ($fields as $key => $field)
                @php
                    $rules = is_array($field['rules']) ? $field['rules'] : explode('|', $field['rules']);
                    $required = in_array('required', $rules, true);
                    $confirmed = in_array('confirmed', $rules, true);
                    $isPassword = $field['type'] === 'password';
                @endphp

                <div class="{{ $confirmed ? '' : 'full' }}">
                    <label for="{{ $key }}">{{ $field['label'] }}</label>
                    @if ($field['type'] === 'select')
                        <select id="{{ $key }}" name="{{ $key }}" @required($required)>
                            @foreach ($field['options'] ?? [] as $value => $label)
                                <option value="{{ $value }}" @selected((string) old($key, $values[$key]) === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="{{ $key }}" type="{{ $field['type'] }}" name="{{ $key }}"
                               value="{{ $isPassword ? '' : old($key, $values[$key]) }}"
                               @required($required)
                               @if ($isPassword) autocomplete="new-password" @endif>
                    @endif
                    @if (! empty($field['hint']))<div class="hint">{{ $field['hint'] }}</div>@endif
                    @error($key)<div class="field-error">{{ $message }}</div>@enderror
                </div>

                @if ($confirmed)
                    <div>
                        <label for="{{ $key }}_confirmation">Confirm {{ $field['label'] }}</label>
                        <input id="{{ $key }}_confirmation" type="{{ $field['type'] }}" name="{{ $key }}_confirmation"
                               @required($required) @if ($isPassword) autocomplete="new-password" @endif>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="actions">
            <a href="{{ route('installer.database') }}" class="btn secondary">&larr; Back</a>
            <button type="submit" class="btn">Next &rarr;</button>
        </div>
    </form>
@endsection
