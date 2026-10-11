@extends('installer::layout')

@section('content')
    <h2>{{ $title }}</h2>
    <p class="intro">{{ $intro }}</p>

    @unless ($passes)
        <div class="alert err">{{ __('installer::installer.check.failed') }}</div>
    @endunless

    <div class="list">
        @foreach ($items as $item)
            <div class="row">
                <span>{{ $item['label'] }}</span>
                <span>
                    <span class="hint">{{ $item['current'] }}</span>&nbsp;
                    <span class="badge {{ $item['ok'] ? 'ok' : 'err' }}">{{ $item['ok'] ? __('installer::installer.common.ok') : __('installer::installer.common.failed') }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <div class="actions">
        <a href="{{ $back }}" class="btn secondary">&larr; {{ __('installer::installer.common.back') }}</a>
        @if ($passes)
            <a href="{{ $next }}" class="btn">{{ __('installer::installer.common.next') }} &rarr;</a>
        @else
            <a href="{{ url()->current() }}" class="btn">{{ __('installer::installer.common.check_again') }}</a>
        @endif
    </div>
@endsection
