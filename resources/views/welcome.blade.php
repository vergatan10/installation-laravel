@extends('installer::layout')

@section('content')
    <h2>Welcome</h2>
    <p class="intro">This wizard will install <strong>{{ config('app.name', 'Laravel') }}</strong> on your server in a few steps.</p>

    <p>Before you start, please prepare:</p>
    <div class="list">
        <div class="row">An empty database (MySQL / MariaDB / PostgreSQL / SQL Server) or use SQLite</div>
        <div class="row">The database host, port, name, username and password</div>
        @if (config('installer.admin.enabled'))
            <div class="row">The name, email and password for the administrator account</div>
        @endif
    </div>

    <p class="hint">The installer will write your <code>.env</code> file, run the database migrations and seeders, create the storage link and lock itself when finished.</p>

    <div class="actions">
        <span></span>
        <a href="{{ route('installer.requirements') }}" class="btn">Check Requirements &rarr;</a>
    </div>
@endsection
