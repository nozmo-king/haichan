<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAICHAN - a proof-of-work imageboard</title>
    
    <!-- Main stylesheet -->
    <link rel="stylesheet" href="/css/haichan.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            font-family: 'Courier New', monospace;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            padding: 20px 0;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(154, 184, 122, 0.6);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        @keyframes float {
            0%, 100% { 
                transform: translate(0, 0) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translate(100vw, -100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            text-align: center;
            z-index: 10;
            padding: 40px;
            margin-top: 40px;
            width: 100%;
            max-width: 1200px;
        }

        .logo {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 60px;
            perspective: 1000px;
        }

        .slot-container {
            width: 80px;
            height: 120px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #2a2a3e, #1a1a2e);
            border-radius: 12px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                inset 0 2px 8px rgba(255, 255, 255, 0.1);
            border: 2px solid #708B75;
        }

        .slot-reel {
            position: absolute;
            width: 100%;
            height: 100%;
            animation: spin 3s cubic-bezier(0.17, 0.67, 0.35, 0.96) infinite;
        }

        .slot-item {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            font-weight: bold;
            color: #9AB87A;
            text-shadow: 
                0 0 10px rgba(154, 184, 122, 0.8),
                0 0 20px rgba(154, 184, 122, 0.5),
                0 0 30px rgba(154, 184, 122, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes spin {
            0% { transform: translateY(0); }
            20% { transform: translateY(-120px); }
            40% { transform: translateY(-240px); }
            60% { transform: translateY(-360px); }
            80% { transform: translateY(-480px); }
            100% { transform: translateY(-600px); }
        }

        @keyframes glow {
            from {
                text-shadow: 
                    0 0 10px rgba(154, 184, 122, 0.8),
                    0 0 20px rgba(154, 184, 122, 0.5);
            }
            to {
                text-shadow: 
                    0 0 20px rgba(154, 184, 122, 1),
                    0 0 30px rgba(154, 184, 122, 0.8),
                    0 0 40px rgba(154, 184, 122, 0.6);
            }
        }

        .slot-container:nth-child(1) .slot-reel { animation-delay: 0s; }
        .slot-container:nth-child(2) .slot-reel { animation-delay: 0.3s; }
        .slot-container:nth-child(3) .slot-reel { animation-delay: 0.6s; }
        .slot-container:nth-child(4) .slot-reel { animation-delay: 0.9s; }
        .slot-container:nth-child(5) .slot-reel { animation-delay: 1.2s; }
        .slot-container:nth-child(6) .slot-reel { animation-delay: 1.5s; }
        .slot-container:nth-child(7) .slot-reel { animation-delay: 1.8s; }

        /* Spectacular HAICHAN header with individual letter animations */
        .haichan-title {
            font-family: 'Nova Cut', cursive;
            font-size: 48px;
            font-weight: bold;
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 100;
        }

        .haichan-letter {
            display: inline-block;
            color: #00FF00;
            text-shadow: 
                -1px -1px 0 #FF69B4,
                1px -1px 0 #FF69B4,
                -1px 1px 0 #FF69B4,
                1px 1px 0 #FF69B4;
            margin: 0 2px;
            animation-duration: 3s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        /* Individual letter glow animations */
        .haichan-letter:nth-child(1) { animation-name: shimmer-red; }
        .haichan-letter:nth-child(2) { animation-name: shimmer-orange; animation-delay: 0.2s; }
        .haichan-letter:nth-child(3) { animation-name: shimmer-yellow; animation-delay: 0.4s; }
        .haichan-letter:nth-child(4) { animation-name: shimmer-green; animation-delay: 0.6s; }
        .haichan-letter:nth-child(5) { animation-name: shimmer-blue; animation-delay: 0.8s; }
        .haichan-letter:nth-child(6) { animation-name: shimmer-indigo; animation-delay: 1.0s; }
        .haichan-letter:nth-child(7) { animation-name: shimmer-violet; animation-delay: 1.2s; }

        @keyframes shimmer-red {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #FF0000) drop-shadow(0 0 10px #FF0000) drop-shadow(0 0 15px #FF0000);
            }
            25% { 
                filter: drop-shadow(0 0 10px #FF4500) drop-shadow(0 0 20px #FF4500) drop-shadow(0 0 30px #FF4500);
            }
            50% { 
                filter: drop-shadow(0 0 15px #FF69B4) drop-shadow(0 0 25px #FF69B4) drop-shadow(0 0 35px #FF69B4);
            }
            75% { 
                filter: drop-shadow(0 0 10px #DC143C) drop-shadow(0 0 20px #DC143C) drop-shadow(0 0 30px #DC143C);
            }
        }

        @keyframes shimmer-orange {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #FF8C00) drop-shadow(0 0 10px #FF8C00) drop-shadow(0 0 15px #FF8C00);
            }
            25% { 
                filter: drop-shadow(0 0 10px #FFA500) drop-shadow(0 0 20px #FFA500) drop-shadow(0 0 30px #FFA500);
            }
            50% { 
                filter: drop-shadow(0 0 15px #FFD700) drop-shadow(0 0 25px #FFD700) drop-shadow(0 0 35px #FFD700);
            }
            75% { 
                filter: drop-shadow(0 0 10px #FF6347) drop-shadow(0 0 20px #FF6347) drop-shadow(0 0 30px #FF6347);
            }
        }

        @keyframes shimmer-yellow {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #FFFF00) drop-shadow(0 0 10px #FFFF00) drop-shadow(0 0 15px #FFFF00);
            }
            25% { 
                filter: drop-shadow(0 0 10px #ADFF2F) drop-shadow(0 0 20px #ADFF2F) drop-shadow(0 0 30px #ADFF2F);
            }
            50% { 
                filter: drop-shadow(0 0 15px #00FF00) drop-shadow(0 0 25px #00FF00) drop-shadow(0 0 35px #00FF00);
            }
            75% { 
                filter: drop-shadow(0 0 10px #32CD32) drop-shadow(0 0 20px #32CD32) drop-shadow(0 0 30px #32CD32);
            }
        }

        @keyframes shimmer-green {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #00FF7F) drop-shadow(0 0 10px #00FF7F) drop-shadow(0 0 15px #00FF7F);
            }
            25% { 
                filter: drop-shadow(0 0 10px #00CED1) drop-shadow(0 0 20px #00CED1) drop-shadow(0 0 30px #00CED1);
            }
            50% { 
                filter: drop-shadow(0 0 15px #20B2AA) drop-shadow(0 0 25px #20B2AA) drop-shadow(0 0 35px #20B2AA);
            }
            75% { 
                filter: drop-shadow(0 0 10px #008B8B) drop-shadow(0 0 20px #008B8B) drop-shadow(0 0 30px #008B8B);
            }
        }

        @keyframes shimmer-blue {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #0000FF) drop-shadow(0 0 10px #0000FF) drop-shadow(0 0 15px #0000FF);
            }
            25% { 
                filter: drop-shadow(0 0 10px #4169E1) drop-shadow(0 0 20px #4169E1) drop-shadow(0 0 30px #4169E1);
            }
            50% { 
                filter: drop-shadow(0 0 15px #1E90FF) drop-shadow(0 0 25px #1E90FF) drop-shadow(0 0 35px #1E90FF);
            }
            75% { 
                filter: drop-shadow(0 0 10px #00BFFF) drop-shadow(0 0 20px #00BFFF) drop-shadow(0 0 30px #00BFFF);
            }
        }

        @keyframes shimmer-indigo {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #4B0082) drop-shadow(0 0 10px #4B0082) drop-shadow(0 0 15px #4B0082);
            }
            25% { 
                filter: drop-shadow(0 0 10px #6A5ACD) drop-shadow(0 0 20px #6A5ACD) drop-shadow(0 0 30px #6A5ACD);
            }
            50% { 
                filter: drop-shadow(0 0 15px #9370DB) drop-shadow(0 0 25px #9370DB) drop-shadow(0 0 35px #9370DB);
            }
            75% { 
                filter: drop-shadow(0 0 10px #8A2BE2) drop-shadow(0 0 20px #8A2BE2) drop-shadow(0 0 30px #8A2BE2);
            }
        }

        @keyframes shimmer-violet {
            0%, 100% { 
                filter: drop-shadow(0 0 5px #8B00FF) drop-shadow(0 0 10px #8B00FF) drop-shadow(0 0 15px #8B00FF);
            }
            25% { 
                filter: drop-shadow(0 0 10px #9932CC) drop-shadow(0 0 20px #9932CC) drop-shadow(0 0 30px #9932CC);
            }
            50% { 
                filter: drop-shadow(0 0 15px #DA70D6) drop-shadow(0 0 25px #DA70D6) drop-shadow(0 0 35px #DA70D6);
            }
            75% { 
                filter: drop-shadow(0 0 10px #FF00FF) drop-shadow(0 0 20px #FF00FF) drop-shadow(0 0 30px #FF00FF);
            }
        }

        @media (max-width: 768px) {
            .haichan-title {
                font-size: 32px;
                margin: 20px 0;
            }
        }

        .login-box {
            background: #FFFACD;
            border: 2px solid #708B75;
            border-radius: 16px;
            padding: 40px;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 
                0 8px 32px rgba(112, 139, 117, 0.3);
            animation: float-box 6s ease-in-out infinite;
        }

        .login-buttons {
            display: flex;
            gap: 15px;
            align-items: stretch;
        }

        .key-box {
            background: linear-gradient(145deg, #9AB87A, #708B75);
            border: 2px solid #708B75;
            border-radius: 8px;
            width: 64px;
            min-width: 64px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 
                4px 4px 12px rgba(0, 0, 0, 0.4),
                -2px -2px 8px rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .key-box:hover {
            border-color: #FFFFEE;
            transform: translateY(-5px);
            box-shadow: 
                0 20px 80px rgba(0, 0, 0, 0.8),
                inset 0 2px 16px rgba(255, 255, 255, 0.1);
        }

        .key-box .key-emoji {
            font-size: 24px;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.8));
            animation: key-glow 2s ease-in-out infinite alternate;
        }

        .btn-login {
            flex: 1;
            padding: 16px;
            background: linear-gradient(145deg, #9AB87A, #708B75);
            border: none;
            border-radius: 8px;
            color: #FFFFEE;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 
                4px 4px 12px rgba(0, 0, 0, 0.4),
                -2px -2px 8px rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 
                6px 6px 16px rgba(0, 0, 0, 0.5),
                -3px -3px 10px rgba(255, 255, 255, 0.15);
        }

        @keyframes key-glow {
            from {
                filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.8));
            }
            to {
                filter: drop-shadow(0 0 12px rgba(255, 255, 255, 1));
            }
        }

        @keyframes float-box {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .login-box h2 {
            color: #9AB87A;
            font-size: 24px;
            margin-bottom: 10px;
            text-shadow: 
                -1px -1px 0 #FF69B4,
                1px -1px 0 #FF69B4,
                -1px 1px 0 #FF69B4,
                1px 1px 0 #FF69B4,
                0 0 10px rgba(154, 184, 122, 0.5);
        }

        .login-box p {
            color: #708B75;
            font-size: 14px;
            margin-bottom: 30px;
        }


        .btn-login:active {
            transform: translateY(1px);
            box-shadow: 
                2px 2px 6px rgba(0, 0, 0, 0.4),
                -1px -1px 4px rgba(255, 255, 255, 0.1);
        }

        .btn-register {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(145deg, #444B6E, #3D315B);
            border: none;
            border-radius: 8px;
            color: #FFFFEE;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            box-shadow: 
                4px 4px 12px rgba(0, 0, 0, 0.4),
                -2px -2px 8px rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 
                6px 6px 16px rgba(0, 0, 0, 0.5),
                -3px -3px 10px rgba(255, 255, 255, 0.15);
        }

        .btn-register:active {
            transform: translateY(1px);
            box-shadow: 
                2px 2px 6px rgba(0, 0, 0, 0.4),
                -1px -1px 4px rgba(255, 255, 255, 0.1);
        }

        input:focus {
            outline: none;
            border-color: #9AB87A;
            box-shadow: 0 0 0 2px rgba(154, 184, 122, 0.2);
        }

        .tagline {
            color: #708B75;
            font-size: 14px;
            margin-top: 20px;
            opacity: 0.8;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 0.5; }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(145deg, #2a2a3e, #1a1a2e);
            border: 2px solid #9AB87A;
            border-radius: 16px;
            margin: 10% auto;
            padding: 40px;
            width: 90%;
            max-width: 500px;
            box-shadow: 
                0 20px 80px rgba(0, 0, 0, 0.8),
                inset 0 2px 16px rgba(255, 255, 255, 0.05);
            animation: modal-appear 0.3s ease-out;
        }

        @keyframes modal-appear {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .close {
            color: #708B75;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #9AB87A;
        }

        .modal h3 {
            color: #9AB87A;
            font-size: 20px;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(154, 184, 122, 0.5);
        }

        .modal p {
            color: #708B75;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .privkey-input {
            width: 100%;
            padding: 15px;
            background: rgba(42, 42, 62, 0.8);
            border: 2px solid #708B75;
            border-radius: 8px;
            color: #FFFFEE;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }

        .privkey-input:focus {
            outline: none;
            border-color: #9AB87A;
            box-shadow: 0 0 0 2px rgba(154, 184, 122, 0.2);
        }

        .btn-privkey {
            width: 100%;
            padding: 16px;
            background: linear-gradient(145deg, #9AB87A, #708B75);
            border: none;
            border-radius: 8px;
            color: #FFFFEE;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 
                4px 4px 12px rgba(0, 0, 0, 0.4),
                -2px -2px 8px rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-privkey:hover {
            transform: translateY(-2px);
            box-shadow: 
                6px 6px 16px rgba(0, 0, 0, 0.5),
                -3px -3px 10px rgba(255, 255, 255, 0.15);
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            padding: 20px;
            background: rgba(42, 42, 62, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(112, 139, 117, 0.3);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            color: #9AB87A;
            font-size: 20px;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(154, 184, 122, 0.5);
        }

        .stat-label {
            color: #708B75;
            font-size: 10px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .logo {
                gap: 8px;
            }
            
            .slot-container {
                width: 50px;
                height: 80px;
            }
            
            .slot-item {
                height: 80px;
                font-size: 40px;
            }
            
            @keyframes spin {
                0% { transform: translateY(0); }
                20% { transform: translateY(-80px); }
                40% { transform: translateY(-160px); }
                60% { transform: translateY(-240px); }
                80% { transform: translateY(-320px); }
                100% { transform: translateY(-400px); }
            }
            
            .login-box {
                padding: 30px 20px;
                max-width: 90%;
            }
            
            .login-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .key-box {
                width: 100%;
                height: 48px;
                border-radius: 6px;
            }
            
            .key-box .key-emoji {
                font-size: 20px;
            }
            
            .modal-content {
                margin: 20% auto;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body class="landing-page">
    <script nonce="{{ app('csp_nonce') }}">
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (15 + Math.random() * 10) + 's';
            document.body.appendChild(particle);
        }
    </script>

    <div class="container">
        <div class="logo">
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">H</div>
                    <div class="slot-item">🌊</div>
                    <div class="slot-item">H</div>
                    <div class="slot-item">⚡</div>
                    <div class="slot-item">H</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">A</div>
                    <div class="slot-item">🎲</div>
                    <div class="slot-item">A</div>
                    <div class="slot-item">💎</div>
                    <div class="slot-item">A</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">I</div>
                    <div class="slot-item">🔥</div>
                    <div class="slot-item">I</div>
                    <div class="slot-item">⭐</div>
                    <div class="slot-item">I</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">C</div>
                    <div class="slot-item">🎯</div>
                    <div class="slot-item">C</div>
                    <div class="slot-item">🌟</div>
                    <div class="slot-item">C</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">H</div>
                    <div class="slot-item">⚡</div>
                    <div class="slot-item">H</div>
                    <div class="slot-item">💫</div>
                    <div class="slot-item">H</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">A</div>
                    <div class="slot-item">🎨</div>
                    <div class="slot-item">A</div>
                    <div class="slot-item">✨</div>
                    <div class="slot-item">A</div>
                </div>
            </div>
            
            <div class="slot-container">
                <div class="slot-reel">
                    <div class="slot-item">N</div>
                    <div class="slot-item">🚀</div>
                    <div class="slot-item">N</div>
                    <div class="slot-item">💻</div>
                    <div class="slot-item">N</div>
                </div>
            </div>
        </div>

        <div class="login-box">
            <h2>⛏️ Welcome to Haichan</h2>
            <p>a proof-of-work imageboard</p>
            
            <form action="/login" method="POST" style="margin-bottom: 20px;">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: #3D315B; font-size: 12px; margin-bottom: 5px; text-align: left; font-weight: bold;">Username or Address</label>
                    <input type="text" name="login_identifier" required 
                           style="width: 100%; padding: 12px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 6px; color: #3D315B; font-size: 14px; font-family: 'Courier New', monospace;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #3D315B; font-size: 12px; margin-bottom: 5px; text-align: left; font-weight: bold;">Password</label>
                    <input type="password" name="password" required 
                           style="width: 100%; padding: 12px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 6px; color: #3D315B; font-size: 14px; font-family: 'Courier New', monospace;">
                </div>
                
                <div class="login-buttons">
                    <button type="submit" class="btn-login">Login</button>
                    <div class="key-box" id="privkeyButton">
                        <div class="key-emoji">🔑</div>
                    </div>
                </div>
            </form>

            <div style="text-align: center; margin: 15px 0;">
                <span style="color: #708B75; font-size: 12px;">or</span>
            </div>

            <a href="/register" class="btn-register">
                Register
            </a>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value">{{ $userCount ?? 0 }}/{{ $userCap ?? 256 }}</div>
                    <div class="stat-label">Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($totalProofs ?? 0) }}</div>
                    <div class="stat-label">Proofs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $boardCount ?? 8 }}</div>
                    <div class="stat-label">Boards</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Private Key Login Modal -->
    <div id="privkeyModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closePrivkeyButton">&times;</span>
            <h3>🔑 Emergency Private Key Login</h3>
            <p>Enter your private key from your backup file for emergency access. This works with any private key format and can upgrade your account to real Bitcoin addresses.</p>
            
            <form id="privkeyForm" action="/login-backup" method="POST">
                @csrf
                <textarea 
                       name="private_key" 
                       class="privkey-input" 
                       placeholder="Enter your private key from backup file..."
                       rows="3"
                       style="width: 100%; padding: 10px; border: 2px solid #333; border-radius: 6px; background: #1a1a1a; color: #fff; font-family: monospace; resize: vertical; box-sizing: border-box;"
                       required></textarea>
                
                <button type="submit" class="btn-privkey">🚀 Login with Private Key</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: rgba(255, 165, 0, 0.1); border: 1px solid rgba(255, 165, 0, 0.3); border-radius: 8px;">
                <p style="color: #FFA500; font-size: 12px; margin: 0;">
                    ⚠️ <strong>Security Note:</strong> Only use this on trusted devices. Your private key is sensitive information.
                </p>
            </div>
        </div>
    </div>

    <script nonce="{{ app('csp_nonce') }}">
        function openPrivkeyModal() {
            console.log('Opening private key modal...');
            const modal = document.getElementById('privkeyModal');
            if (modal) {
                modal.style.display = 'block';
                console.log('Modal opened successfully');
                setTimeout(() => {
                    const input = document.querySelector('.privkey-input');
                    if (input) {
                        input.focus();
                        console.log('Input focused');
                    }
                }, 100);
            } else {
                console.error('Modal element not found!');
            }
        }

        function closePrivkeyModal() {
            document.getElementById('privkeyModal').style.display = 'none';
            document.getElementById('privkeyForm').reset();
        }

        // Add event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Open modal button
            const openButton = document.getElementById('privkeyButton');
            if (openButton) {
                openButton.addEventListener('click', openPrivkeyModal);
            }

            // Close modal button
            const closeButton = document.getElementById('closePrivkeyButton');
            if (closeButton) {
                closeButton.addEventListener('click', closePrivkeyModal);
            }

            // Close modal when clicking outside
            const modal = document.getElementById('privkeyModal');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closePrivkeyModal();
                    }
                });
            }

            // Handle private key form submission
            const privkeyForm = document.getElementById('privkeyForm');
            if (privkeyForm) {
                privkeyForm.addEventListener('submit', function(e) {
                    const submitButton = this.querySelector('.btn-privkey');
                    const privateKey = this.querySelector('.privkey-input').value.trim();
                    
                    if (!privateKey) {
                        e.preventDefault();
                        alert('Please enter your private key');
                        return;
                    }
                    
                    if (submitButton) {
                        submitButton.textContent = '🔄 Authenticating...';
                        submitButton.disabled = true;
                    }
                    
                    // Let the form submit normally - no preventDefault()
                });
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePrivkeyModal();
            }
        });
    </script>
</body>
</html>
