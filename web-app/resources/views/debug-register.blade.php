<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug Registration</title>
</head>
<body>
    <h2>Debug Registration Form</h2>
    
    <form method="POST" action="/debug-register">
        @csrf
        <p>
            <label>Invite Code:</label>
            <input type="text" name="invite_code" value="GENESIS01" required>
        </p>
        <p>
            <label>Username:</label>
            <input type="text" name="username" value="testuser123" required>
        </p>
        <p>
            <label>Password:</label>
            <input type="password" name="password" value="testpass123" required>
        </p>
        <p>
            <label>Mouse Entropy:</label>
            <textarea name="mouse_entropy" id="entropy">[{"x":100,"y":100,"timestamp":1000}]</textarea>
        </p>
        <button type="submit">Test Submit</button>
    </form>

    <hr>
    
    <form method="POST" action="/simple-test-register">
        @csrf
        <h3>Simple Test Registration (Minimal)</h3>
        <button type="submit">Test Simple Create User</button>
    </form>
    
    <hr>
    
    <form method="POST" action="/auth/register">
        @csrf
        <p>
            <label>Invite Code:</label>
            <input type="text" name="invite_code" value="GENESIS01" required>
        </p>
        <p>
            <label>Username:</label>
            <input type="text" name="username" value="testuser456" required>
        </p>
        <p>
            <label>Password:</label>
            <input type="password" name="password" value="testpass456" required>
        </p>
        <p>
            <label>Mouse Entropy:</label>
            <textarea name="mouse_entropy">[{"x":100,"y":100,"timestamp":1000,"deltaX":1,"deltaY":1}]</textarea>
        </p>
        <button type="submit">Real Register</button>
    </form>

    @if($errors->any())
        <div style="color: red;">
            <h3>Errors:</h3>
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div style="color: green;">
            <h3>Success:</h3>
            <p>{{ session('success') }}</p>
        </div>
    @endif
</body>
</html>