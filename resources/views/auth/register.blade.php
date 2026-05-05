<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body>
@include('partials.header')
<div class="auth-container">
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <h2 class="auth-title">Sign up</h2>

        <input type="text" name="username" placeholder="Username" required autofocus value="{{ old('username') }}">
        <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}">
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        <input type="number" name="age" placeholder="Age" required value="{{ old('age') }}">

        <button type="submit" class="auth-button">SIGN UP</button>

        @if($errors->any())
            <div class="error-messages">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="auth-link">
            <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </form>
</div>
</body>
</html>
