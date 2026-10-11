@extends('installer::layout')

@section('content')
    <h2>{{ $title }}</h2>
    <p class="intro">{{ $intro }}</p>

    @unless ($passes)
        <div class="alert err">Some checks failed. Fix them on your server (or ask your hosting provider), then click <strong>Check Again</strong>.</div>
    @endunless

    <div class="list">
        @foreach ($items as $item)
            <div class="row">
                <span>{{ $item['label'] }}</span>
                <span>
                    <span class="hint">{{ $item['current'] }}</span>&nbsp;
                    <span class="badge {{ $item['ok'] ? 'ok' : 'err' }}">{{ $item['ok'] ? 'OK' : 'FAILED' }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <div class="actions">
        <a href="{{ $back }}" class="btn secondary">&larr; Back</a>
        @if ($passes)
            <a href="{{ $next }}" class="btn">Next &rarr;</a>
        @else
            <a href="{{ url()->current() }}" class="btn">Check Again</a>
        @endif
    </div>
@endsection
