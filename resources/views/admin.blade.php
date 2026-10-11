@extends('installer::layout')

@section('content')
    <h2>Administrator Account</h2>
    <p class="intro">This account will be created after the database is installed. Use it to log in.</p>

    <form method="POST" action="{{ route('installer.admin.save') }}">
        @csrf
        <div class="grid">
            <div class="full">
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $values['name'] ?? '') }}" required>
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="full">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $values['email'] ?? '') }}" required>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" minlength="8" required autocomplete="new-password">
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password">
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('installer.database') }}" class="btn secondary">&larr; Back</a>
            <button type="submit" class="btn">Next &rarr;</button>
        </div>
    </form>
@endsection
