@extends('installer::layout')

@section('content')
    <h2>Application &amp; Database</h2>
    <p class="intro">Create an empty database in your hosting panel (cPanel, Plesk, etc.), then enter its details below and click <strong>Test Connection</strong>.</p>

    @if ($errors->any())
        <div class="alert err">{{ $errors->first('connection') ?: 'Please check the form for errors.' }}</div>
    @endif
    <div id="test-result" class="alert" style="display:none"></div>

    <form method="POST" action="{{ route('installer.database.save') }}" id="db-form">
        @csrf

        <fieldset>
            <legend>Application</legend>
            <div class="grid">
                <div>
                    <label for="app_name">App Name</label>
                    <input id="app_name" name="app_name" value="{{ old('app_name', $values['app_name']) }}" required>
                    @error('app_name')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="app_url">App URL</label>
                    <input id="app_url" type="url" name="app_url" value="{{ old('app_url', $values['app_url']) }}" required>
                    @error('app_url')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="app_env">Environment</label>
                    <select id="app_env" name="app_env">
                        @foreach (['production' => 'Production', 'staging' => 'Staging', 'local' => 'Local / Development'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('app_env', $values['app_env']) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <label class="check"><input type="checkbox" name="app_debug" value="1" @checked(old('app_debug', $values['app_debug']))> Enable debug mode</label>
                    <div class="hint">Keep this off on a live website.</div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Database</legend>
            <div class="grid">
                <div class="full">
                    <label for="connection">Database Type</label>
                    <select id="connection" name="connection" data-db>
                        @foreach ($drivers as $key => $label)
                            <option value="{{ $key }}" @selected(old('connection', $values['connection']) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="full" data-sqlite>
                    <label for="sqlite_path">SQLite File Path</label>
                    <input id="sqlite_path" name="sqlite_path" value="{{ old('sqlite_path', $values['sqlite_path']) }}" data-db>
                    <div class="hint">The file will be created if it does not exist.</div>
                    @error('sqlite_path')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div data-server>
                    <label for="host">Host</label>
                    <input id="host" name="host" value="{{ old('host', $values['host']) }}" data-db>
                    @error('host')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div data-server>
                    <label for="port">Port</label>
                    <input id="port" name="port" value="{{ old('port', $values['port']) }}" data-db>
                    @error('port')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="full" data-server>
                    <label for="database">Database Name</label>
                    <input id="database" name="database" value="{{ old('database', $values['database']) }}" data-db>
                    @error('database')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div data-server>
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username', $values['username']) }}" data-db autocomplete="off">
                    @error('username')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div data-server>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" value="" data-db autocomplete="new-password">
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </fieldset>

        <div class="actions">
            <a href="{{ route('installer.permissions') }}" class="btn secondary">&larr; Back</a>
            <span>
                <button type="button" class="btn secondary" id="btn-test">Test Connection</button>
                <button type="submit" class="btn" id="btn-save" disabled>Save &amp; Continue &rarr;</button>
            </span>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('db-form');
    var conn = document.getElementById('connection');
    var port = document.getElementById('port');
    var btnTest = document.getElementById('btn-test');
    var btnSave = document.getElementById('btn-save');
    var result = document.getElementById('test-result');
    var ports = { mysql: '3306', mariadb: '3306', pgsql: '5432', sqlsrv: '1433' };

    function toggle() {
        var sqlite = conn.value === 'sqlite';
        document.querySelectorAll('[data-sqlite]').forEach(function (el) { el.style.display = sqlite ? '' : 'none'; });
        document.querySelectorAll('[data-server]').forEach(function (el) { el.style.display = sqlite ? 'none' : ''; });
    }

    function invalidate() {
        btnSave.disabled = true;
        result.style.display = 'none';
    }

    function show(ok, message) {
        result.className = 'alert ' + (ok ? 'ok' : 'err');
        result.textContent = message;
        result.style.display = '';
    }

    conn.addEventListener('change', function () {
        if (Object.values(ports).indexOf(port.value) !== -1 || port.value === '') {
            port.value = ports[conn.value] || port.value;
        }
        toggle();
    });
    document.querySelectorAll('[data-db]').forEach(function (el) {
        el.addEventListener('input', invalidate);
        el.addEventListener('change', invalidate);
    });

    btnTest.addEventListener('click', function () {
        btnTest.disabled = true;
        btnTest.textContent = 'Testing...';

        fetch(@json(route('installer.database.test')), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (r) {
            if (r.status === 422 && r.data.errors) {
                show(false, Object.values(r.data.errors)[0][0]);
            } else {
                show(!!r.data.ok, r.data.message);
                btnSave.disabled = !r.data.ok;
            }
        })
        .catch(function (e) { show(false, 'Request failed: ' + e.message); })
        .finally(function () { btnTest.disabled = false; btnTest.textContent = 'Test Connection'; });
    });

    toggle();
})();
</script>
@endpush
