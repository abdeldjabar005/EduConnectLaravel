<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form id="login-form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <span id="error-message" class="text-danger"></span> <!-- Add this line -->
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</div>
</body>
</html>
