@extends('installer::layout')

@section('content')
    <div id="installing">
        <h2>Install</h2>
        <p class="intro">Everything is ready. Click <strong>Install Now</strong> and keep this page open until it finishes.</p>

        <div class="list" id="tasks">
            @foreach ($tasks as $key => $label)
                <div class="row" data-task="{{ $key }}">
                    <span>{{ $label }}<br><small class="hint" data-message></small></span>
                    <span class="badge" data-badge style="background:#eef0f5;color:#6b7280">Pending</span>
                </div>
            @endforeach
        </div>

        <pre class="log" id="log" style="display:none"></pre>

        <div class="actions">
            <a href="{{ config('installer.admin.enabled') ? route('installer.admin') : route('installer.database') }}" class="btn secondary" id="btn-back">&larr; Back</a>
            <button type="button" class="btn" id="btn-install">Install Now</button>
        </div>
    </div>

    <div id="finished" style="display:none; text-align:center; padding: 20px 0">
        <div style="font-size:52px; color:var(--ok); line-height:1">&#10004;</div>
        <h2 style="margin-top:12px">Installation Complete!</h2>
        <p class="intro">{{ config('app.name', 'Laravel') }} has been installed successfully. The installer is now locked.</p>
        <div id="warnings"></div>
        <div class="actions" style="justify-content:center">
            <a href="#" class="btn secondary" id="btn-login" style="display:none">Admin Login</a>
            <a href="#" class="btn" id="btn-home">Go to Website &rarr;</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var tasks = @json(array_keys($tasks));
    var url = @json(url(config('installer.route_prefix', 'install').'/run')) + '/';
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var btn = document.getElementById('btn-install');
    var back = document.getElementById('btn-back');
    var log = document.getElementById('log');
    var warnings = [];

    var styles = {
        running: ['Running...', '#eef2ff', '#4f46e5'],
        ok: ['Done', '#ecfdf3', '#16a34a'],
        warning: ['Warning', '#fffbeb', '#d97706'],
        error: ['Failed', '#fef2f2', '#dc2626']
    };

    function setState(task, state, message) {
        var row = document.querySelector('[data-task="' + task + '"]');
        var badge = row.querySelector('[data-badge]');
        badge.textContent = styles[state][0];
        badge.style.background = styles[state][1];
        badge.style.color = styles[state][2];
        if (message !== undefined) row.querySelector('[data-message]').textContent = message;
    }

    function appendLog(task, text) {
        if (!text) return;
        log.style.display = '';
        log.textContent += '[' + task + ']\n' + text.trim() + '\n\n';
        log.scrollTop = log.scrollHeight;
    }

    function finish(data) {
        document.getElementById('installing').style.display = 'none';
        document.getElementById('finished').style.display = '';
        document.getElementById('btn-home').href = data.redirect;
        if (data.login) {
            var login = document.getElementById('btn-login');
            login.href = data.login;
            login.style.display = '';
        }
        if (warnings.length) {
            var box = document.getElementById('warnings');
            warnings.forEach(function (w) {
                var div = document.createElement('div');
                div.className = 'alert warn';
                div.textContent = w;
                box.appendChild(div);
            });
        }
    }

    function run(index) {
        if (index >= tasks.length) return;
        var task = tasks[index];
        setState(task, 'running', '');

        fetch(url + task, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            return res.text().then(function (text) {
                try { return JSON.parse(text); }
                catch (e) { return { status: 'error', message: 'Server error (HTTP ' + res.status + '). Check storage/logs/laravel.log.' }; }
            });
        })
        .then(function (data) {
            setState(task, data.status, data.message);
            appendLog(task, data.output);

            if (data.status === 'error') {
                btn.disabled = false;
                btn.textContent = 'Retry';
                btn.dataset.from = index;
                back.classList.remove('disabled');
                return;
            }
            if (data.status === 'warning') warnings.push(data.message);
            if (task === 'finish') return finish(data);
            run(index + 1);
        })
        .catch(function (e) {
            setState(task, 'error', 'Request failed: ' + e.message);
            btn.disabled = false;
            btn.textContent = 'Retry';
            btn.dataset.from = index;
            back.classList.remove('disabled');
        });
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Installing...';
        back.classList.add('disabled');
        run(parseInt(btn.dataset.from || '0', 10));
    });
})();
</script>
@endpush
