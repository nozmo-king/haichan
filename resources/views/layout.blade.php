<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... existing head ... -->
    <link rel="icon" type="image/png" href="/logo.png">
    <title>@yield('title', 'Haichan - Crypto Forum')</title>
    <!-- ... other head content ... -->
</head>
<body>
    <!-- ... existing layout ... -->

    @yield('content')

    <footer style="margin-top:40px; text-align:center;">
        <img src="/logo.png" alt="Haichan Logo" style="height:48px; width:auto; opacity:0.95;">
    </footer>
</body>
</html>