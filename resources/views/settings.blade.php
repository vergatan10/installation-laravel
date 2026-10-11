@extends('installer::layout')

@section('content')
    <h2>{{ __(config('installer.settings.title') ?: 'installer::installer.settings.title') }}</h2>
    <p class="intro">{{ __(config('installer.settings.description') ?: 'installer::installer.settings.intro') }}</p>

    @if ($errors->any())
        <div class="alert err">{{ $errors->first('settings') ?: __('installer::installer.common.form_errors') }}</div>
    @endif

    <form method="POST" action="{{ route('installer.settings.save') }}">
        @csrf

        @foreach ($groups as $group)
            <fieldset>
                <legend>{{ $group['title'] }}</legend>
                @if ($group['description'])
                    <p class="hint" style="margin-top:0">{{ $group['description'] }}</p>
                @endif
                <div class="grid">
                    @foreach ($group['fields'] as $name => $field)
                        @include('installer::partials.field', ['name' => $name, 'field' => $field, 'value' => $values[$name]])
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div class="actions">
            <a href="{{ $back }}" class="btn secondary">&larr; {{ __('installer::installer.common.back') }}</a>
            <button type="submit" class="btn">{{ __('installer::installer.common.next') }} &rarr;</button>
        </div>
    </form>
@endsection
