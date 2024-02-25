<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <!-- Add CSS files here -->
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Email:</label>
            <label>
                <input type="email" name="email" class="form-control" required>
            </label>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <label>
                <input type="password" name="password" class="form-control" required>
            </label>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    <a href="{{ route('login.get') }}">Login</a>
</div>
</body>
</html>
