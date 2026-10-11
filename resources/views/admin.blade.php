@extends('installer::layout')

@section('content')
    <h2>{{ __(config('installer.admin.title') ?: 'installer::installer.admin.title') }}</h2>
    <p class="intro">{{ __(config('installer.admin.description') ?: 'installer::installer.admin.description') }}</p>

    <form method="POST" action="{{ route('installer.admin.save') }}">
        @csrf
        <div class="grid">
            @foreach ($fields as $name => $field)
                @include('installer::partials.field', ['name' => $name, 'field' => $field, 'value' => $values[$name], 'hidePassword' => true])
            @endforeach
        </div>

        <div class="actions">
            <a href="{{ $back }}" class="btn secondary">&larr; {{ __('installer::installer.common.back') }}</a>
            <button type="submit" class="btn">{{ __('installer::installer.common.next') }} &rarr;</button>
        </div>
    </form>
@endsection
