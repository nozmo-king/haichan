<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haichan - Anonymous Discussion</title>
    <link rel="stylesheet" href="/css/haichan.css">
    @vite(['resources/css/themes.css'])
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <style>
        .glow-text {
            color: #9AB87A;
            text-shadow:
                0 0 5px #9AB87A,
                0 0 10px #9AB87A,
                0 0 15px #9AB87A,
                0 0 20px #9AB87A;
            animation: glow-pulse 2s ease-in-out infinite alternate;
        }

        @keyframes glow-pulse {
            from {
                text-shadow:
                    0 0 5px #9AB87A,
                    0 0 10px #9AB87A,
                    0 0 15px #9AB87A,
                    0 0 20px #9AB87A;
            }
            to {
                text-shadow:
                    0 0 2px #9AB87A,
                    0 0 5px #9AB87A,
                    0 0 8px #9AB87A,
                    0 0 12px #9AB87A;
            }
        }

        .landing-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--secondary-bg), var(--primary-bg));
            padding: 20px;
        }

        .landing-card {
            background: var(--primary-bg);
            padding: 60px;
            border-radius: 12px;
            border: 3px solid var(--border-color);
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            text-align: center;
        }

        .landing-title {
            font-family: 'Nova Cut', serif;
            font-size: 48px;
            color: var(--text-primary);
            margin: 0 0 20px 0;
        }

        .strobe-emoji {
            animation: strobe 1.5s infinite alternate;
            transition: animation-duration 0.3s ease;
        }

        .strobe-emoji:hover {
            animation-duration: 0.3s;
        }

        @keyframes strobe {
            0%, 20% { opacity: 1; }
            50% { opacity: 0.3; }
            80%, 100% { opacity: 1; }
        }

        .landing-message {
            color: var(--text-secondary);
            font-size: 18px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .landing-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .landing-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login {
            background: #9AB87A;
            color: #1A1A1A;
        }

        .btn-login:hover {
            background: #85A366;
            transform: translateY(-2px);
        }

        .btn-register {
            background: transparent;
            color: #9AB87A;
            border: 2px solid #9AB87A;
        }

        .btn-register:hover {
            background: #9AB87A;
            color: #1A1A1A;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="landing-container">
    <div class="landing-card">
        <h1 class="landing-title"><span class="strobe-emoji">🌸</span> HAICHAN <span class="strobe-emoji">🌸</span></h1>

        <div class="landing-message">
            If you want the love, you have to <span class="glow-text">log in</span>.
        </div>

        <div class="landing-buttons">
            <a href="/auth/login" class="landing-btn btn-login">Log In</a>
            <a href="/auth/register" class="landing-btn btn-register">Register</a>
        </div>
    </div>
</div>

</body>
</html>