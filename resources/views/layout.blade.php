<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Installer &middot; {{ config('app.name', 'Laravel') }}</title>
    <style>
        :root {
            --bg: #f3f4f8; --card: #fff; --text: #1f2430; --muted: #6b7280; --line: #e5e7eb;
            --primary: #4f46e5; --primary-dark: #4338ca; --ok: #16a34a; --ok-bg: #ecfdf3;
            --err: #dc2626; --err-bg: #fef2f2; --warn: #d97706; --warn-bg: #fffbeb;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font: 15px/1.55 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); }
        .wrap { max-width: 780px; margin: 40px auto; padding: 0 16px; }
        .brand { text-align: center; margin-bottom: 24px; }
        .brand h1 { margin: 0; font-size: 24px; }
        .brand p { margin: 4px 0 0; color: var(--muted); }
        .steps { display: flex; list-style: none; padding: 0; margin: 0 0 20px; gap: 6px; }
        .steps li { flex: 1; text-align: center; font-size: 12px; color: var(--muted); position: relative; padding-top: 34px; }
        .steps li::before { content: attr(data-n); position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 28px; height: 28px; border-radius: 50%; background: #dfe1ea; color: #555; font-weight: 600; line-height: 28px; }
        .steps li.done::before { content: "\2713"; background: var(--ok); color: #fff; }
        .steps li.active { color: var(--primary); font-weight: 600; }
        .steps li.active::before { background: var(--primary); color: #fff; }
        .card { background: var(--card); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.05); padding: 28px; }
        .card h2 { margin: 0 0 6px; font-size: 20px; }
        .card .intro { color: var(--muted); margin: 0 0 20px; }
        .list { border: 1px solid var(--line); border-radius: 8px; overflow: hidden; margin: 0 0 20px; }
        .list .row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-top: 1px solid var(--line); }
        .list .row:first-child { border-top: 0; }
        .badge { font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 99px; white-space: nowrap; }
        .badge.ok { background: var(--ok-bg); color: var(--ok); }
        .badge.err { background: var(--err-bg); color: var(--err); }
        .badge.warn { background: var(--warn-bg); color: var(--warn); }
        .alert { padding: 12px 14px; border-radius: 8px; margin: 0 0 18px; }
        .alert.err { background: var(--err-bg); color: var(--err); }
        .alert.ok { background: var(--ok-bg); color: var(--ok); }
        .alert.warn { background: var(--warn-bg); color: var(--warn); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
        .grid .full { grid-column: 1 / -1; }
        label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; }
        input, select { width: 100%; padding: 9px 11px; border: 1px solid #cfd3dc; border-radius: 7px; font: inherit; background: #fff; }
        input:focus, select:focus { outline: 2px solid rgba(79,70,229,.25); border-color: var(--primary); }
        .check { display: flex; align-items: center; gap: 8px; font-weight: 400; }
        .check input { width: auto; }
        .hint { color: var(--muted); font-size: 12px; margin-top: 3px; }
        .field-error { color: var(--err); font-size: 12px; margin-top: 3px; }
        fieldset { border: 1px solid var(--line); border-radius: 8px; padding: 16px; margin: 0 0 18px; }
        legend { font-weight: 600; padding: 0 6px; }
        .actions { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 22px; flex-wrap: wrap; }
        .btn { display: inline-block; border: 0; border-radius: 7px; padding: 10px 20px; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; background: var(--primary); color: #fff; }
        .btn:hover { background: var(--primary-dark); }
        .btn.secondary { background: #eef0f5; color: var(--text); }
        .btn.secondary:hover { background: #e2e5ee; }
        .btn[disabled], .btn.disabled { opacity: .5; pointer-events: none; }
        pre.log { background: #0f172a; color: #cbd5e1; padding: 14px; border-radius: 8px; max-height: 260px; overflow: auto; font-size: 12px; white-space: pre-wrap; margin: 0; }
        .lang { margin-top: 10px; font-size: 13px; }
        .lang a { color: var(--muted); text-decoration: none; margin: 0 6px; }
        .lang a.active { color: var(--primary); font-weight: 600; }
        .footer { text-align: center; color: var(--muted); font-size: 12px; margin-top: 20px; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } .steps li { font-size: 0; } .card { padding: 20px; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <h1>{{ config('app.name', 'Laravel') }}</h1>
        <p>{{ __('installer::installer.wizard') }}</p>
        @if (count(config('installer.locales', [])) > 1)
            <div class="lang">
                @foreach (config('installer.locales') as $code => $name)
                    <a href="{{ route('installer.language', $code) }}" class="{{ app()->getLocale() === $code ? 'active' : '' }}">{{ $name }}</a>
                @endforeach
            </div>
        @endif
    </div>

    @php
        $steps = \Vergatan10\Installer\Support\Installer::stepLabels();
        $keys = array_keys($steps);
        $current = array_search($step ?? 'welcome', $keys);
    @endphp
    <ol class="steps">
        @foreach ($steps as $key => $label)
            @php $i = $loop->index; @endphp
            <li data-n="{{ $i + 1 }}" class="{{ $i < $current ? 'done' : ($i === $current ? 'active' : '') }}">{{ $label }}</li>
        @endforeach
    </ol>

    <div class="card">
        @yield('content')
    </div>

    <div class="footer">Laravel {{ app()->version() }} &middot; PHP {{ PHP_VERSION }}</div>
</div>
@stack('scripts')
</body>
</html>
