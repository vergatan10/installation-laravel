@extends('installer::layout')

@section('content')
    @if ($needsKey)
        <h2>{{ __('installer::installer.access.title') }}</h2>
        <p class="intro">{{ __('installer::installer.access.intro') }}</p>

        <form method="POST" action="{{ route('installer.access') }}">
            @csrf
            <label for="access_key">{{ __('installer::installer.access.label') }}</label>
            <input id="access_key" type="password" name="access_key" required autofocus autocomplete="off">
            @error('access_key')<div class="field-error">{{ $message }}</div>@enderror

            <div class="actions">
                <span></span>
                <button type="submit" class="btn">{{ __('installer::installer.access.submit') }} &rarr;</button>
            </div>
        </form>
    @else
        @php
            $settingsGroups = \Vergatan10\Installer\Support\Installer::settingsGroups();
            $adminFields = \Vergatan10\Installer\Support\Installer::fields(config('installer.admin.fields', []));
        @endphp

        <h2>{{ __('installer::installer.welcome.title') }}</h2>
        <p class="intro">{{ __('installer::installer.welcome.intro', ['app' => config('app.name', 'Laravel')]) }}</p>

        <p>{{ __('installer::installer.welcome.prepare') }}</p>
        <div class="list">
            <div class="row">{{ __('installer::installer.welcome.prepare_db') }}</div>
            <div class="row">{{ __('installer::installer.welcome.prepare_db_credentials') }}</div>
            @if ($settingsGroups)
                <div class="row">{{ __('installer::installer.welcome.prepare_settings', [
                    'groups' => implode(', ', array_map(fn ($name, $group) => __($group['title'] ?? ucfirst($name)), array_keys($settingsGroups), $settingsGroups)),
                ]) }}</div>
            @endif
            @if (config('installer.admin.enabled'))
                <div class="row">{{ __('installer::installer.welcome.prepare_admin', [
                    'title' => mb_strtolower(__(config('installer.admin.title') ?: 'installer::installer.admin.title')),
                    'fields' => mb_strtolower(implode(', ', array_column($adminFields, 'label'))),
                ]) }}</div>
            @endif
        </div>

        <p class="hint">{{ __('installer::installer.welcome.info') }}</p>

        <div class="actions">
            <span></span>
            <a href="{{ $next }}" class="btn">{{ __('installer::installer.welcome.start') }} &rarr;</a>
        </div>
    @endif
@endsection
