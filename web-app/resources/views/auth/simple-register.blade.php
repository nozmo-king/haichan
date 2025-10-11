<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quick Register - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--secondary-bg), var(--primary-bg));">
    <div style="background: var(--primary-bg); padding: 30px; border-radius: 12px; border: 3px solid var(--border-color); max-width: 500px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 25px;">
            <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: var(--text-primary); margin: 0 0 10px 0;">
                🚀 Quick Join Haichan
            </h1>
            <p style="color: var(--text-secondary); font-size: 14px;">
                Simple registration for the Bitcoin imageboard
            </p>
        </div>

        <!-- User Slots Status -->
        <div style="background: var(--content-bg); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <div style="color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">
                🎯 Available Slots: {{ $remainingSlots }}/256
            </div>
            <div style="color: var(--text-secondary); font-size: 12px;">
                Status: {{ $remainingSlots > 0 ? 'OPEN' : 'CLOSED' }}
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div style="background: #FFE6E6; border: 2px solid #FF6B6B; padding: 12px; margin-bottom: 15px; border-radius: 8px;">
            @foreach($errors->all() as $error)
                <div style="color: #D63031; font-size: 13px; margin: 3px 0;">• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        @if(session('success'))
        <div style="background: #E8F5E8; border: 2px solid #4CAF50; padding: 12px; margin-bottom: 15px; border-radius: 8px; color: #2E7D32; font-size: 13px;">
            {{ session('success') }}
        </div>
        @endif

        <!-- Simple Registration Form -->
        <form method="POST" action="/auth/register" id="simple-register-form" style="margin-bottom: 25px;">
            @csrf

            <div style="margin-bottom: 18px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 6px;">
                    🎟️ Friend Code
                </label>
                <input type="text" name="invite_code" id="invite_code" required
                       placeholder="Enter your invite code..."
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 6px;">
                    👤 Username
                </label>
                <input type="text" name="username" id="username" required minlength="3" maxlength="20"
                       placeholder="Choose a username..."
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 6px;">
                    🔒 Password
                </label>
                <input type="password" name="password" id="password" required minlength="6"
                       placeholder="Create a password..."
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
            </div>

            <!-- Mouse Entropy Collection -->
            <div id="entropy-collector" style="background: #F0F8FF; border: 2px solid #4169E1; padding: 15px; border-radius: 8px; margin-bottom: 18px; display: none;">
                <div style="color: #4169E1; font-size: 14px; font-weight: bold; margin-bottom: 10px;">
                    🖱️ Move your mouse to generate entropy
                </div>
                <div id="entropy-canvas-container" style="position: relative; width: 100%; height: 120px; background: #FFF; border: 1px solid #4169E1; border-radius: 4px; cursor: crosshair;">
                    <canvas id="entropy-canvas" width="450" height="120" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
                    <div id="entropy-instructions" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #4169E1; font-size: 12px; text-align: center; pointer-events: none;">
                        Move your mouse randomly in this area
                    </div>
                </div>
                <div id="entropy-progress" style="margin-top: 8px;">
                    <div style="background: #E6E6FA; height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="entropy-bar" style="background: linear-gradient(90deg, #4169E1, #00CED1); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <div id="entropy-text" style="font-size: 11px; color: #4169E1; margin-top: 4px;">Entropy: 0/256 bits</div>
                </div>
            </div>

            <!-- Info -->
            <div style="background: #E3F2FD; border: 2px solid #2196F3; padding: 12px; border-radius: 8px; margin-bottom: 18px;">
                <div style="color: #1565C0; font-size: 12px;">
                    💡 <strong>What happens:</strong><br>
                    • Move your mouse to generate cryptographic entropy<br>
                    • We'll create your Bitcoin address from your movements<br>
                    • You'll get a backup key file to download<br>
                    • Use your username + password to login normally
                </div>
            </div>

            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                🎉 Join Haichan Now!
            </button>
        </form>

        <!-- Advanced Registration Link -->
        <div style="text-align: center; margin-bottom: 15px;">
            <a href="/auth/register-advanced" style="color: var(--text-secondary); text-decoration: none; font-size: 12px;">
                Need advanced options? Use full registration →
            </a>
        </div>

        <!-- Back to Login -->
        <div style="text-align: center;">
            <a href="/auth/login" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
                ← Back to Login
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple form enhancement - show username availability
    const usernameInput = document.getElementById('username');
    let checkTimeout;
    
    usernameInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        const username = this.value.trim();
        
        if (username.length >= 3) {
            checkTimeout = setTimeout(async () => {
                try {
                    const response = await fetch('/auth/check-username', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ username: username })
                    });
                    
                    const result = await response.json();
                    
                    if (result.available) {
                        usernameInput.style.borderColor = '#4CAF50';
                    } else {
                        usernameInput.style.borderColor = '#FF6B6B';
                    }
                } catch (error) {
                    console.log('Username check failed:', error);
                }
            }, 500);
        } else {
            usernameInput.style.borderColor = 'var(--border-color)';
        }
    });
    
    // Mouse entropy collection
    let entropyData = [];
    let entropyComplete = false;
    const canvas = document.getElementById('entropy-canvas');
    const ctx = canvas.getContext('2d');
    const entropyCollector = document.getElementById('entropy-collector');
    const entropyBar = document.getElementById('entropy-bar');
    const entropyText = document.getElementById('entropy-text');
    const submitButton = document.querySelector('button[type="submit"]');
    const form = document.querySelector('form');
    
    // Show entropy collector when user has filled required fields
    function checkFormProgress() {
        const inviteCode = document.getElementById('invite_code').value.trim();
        const username = usernameInput.value.trim();
        const password = document.getElementById('password').value.trim();
        
        if (inviteCode.length >= 8 && username.length >= 3 && password.length >= 6 && !entropyComplete) {
            entropyCollector.style.display = 'block';
        }
    }
    
    [usernameInput, document.getElementById('password'), document.getElementById('invite_code')].forEach(input => {
        input.addEventListener('input', checkFormProgress);
    });
    
    // Track mouse movements for entropy
    canvas.addEventListener('mousemove', function(e) {
        if (entropyComplete) return;
        
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const highPrecisionTime = performance.now();
        
        // Collect multiple entropy sources per movement
        const entropyPoint = {
            // Basic position
            x: Math.floor(x * (canvas.width / rect.width)),
            y: Math.floor(y * (canvas.height / rect.height)),
            
            // High precision timing
            timestamp: highPrecisionTime,
            timestampMod: highPrecisionTime % 1000,
            
            // Movement deltas
            deltaX: e.movementX || 0,
            deltaY: e.movementY || 0,
            
            // Additional browser entropy
            pressure: e.pressure || Math.random(),
            tiltX: e.tiltX || 0,
            tiltY: e.tiltY || 0,
            
            // Calculated entropy
            distance: Math.sqrt(x*x + y*y),
            angle: Math.atan2(y - rect.height/2, x - rect.width/2),
            
            // System state entropy  
            memoryUsage: performance.memory?.usedJSHeapSize || Math.random() * 1000000,
            connectionRtt: navigator.connection?.rtt || Math.random() * 100,
            
            // Random additional entropy
            random1: Math.random(),
            random2: crypto.getRandomValues(new Uint8Array(4))[0],
            random3: Date.now() % 10000,
            userAgent: navigator.userAgent.slice(-8).split('').reduce((a,b) => a + b.charCodeAt(0), 0)
        };
        
        // Add calculated velocity and acceleration if we have previous points
        if (entropyData.length > 0) {
            const lastPoint = entropyData[entropyData.length - 1];
            const timeDiff = Math.max(highPrecisionTime - lastPoint.timestamp, 0.001);
            const distance = Math.sqrt(Math.pow(entropyPoint.x - lastPoint.x, 2) + Math.pow(entropyPoint.y - lastPoint.y, 2));
            
            entropyPoint.velocity = distance / timeDiff;
            entropyPoint.acceleration = entropyData.length > 1 ? 
                (entropyPoint.velocity - (entropyData[entropyData.length - 1].velocity || 0)) / timeDiff : 0;
            
            // Jitter analysis
            entropyPoint.jitterX = Math.abs(entropyPoint.deltaX - (lastPoint.deltaX || 0));
            entropyPoint.jitterY = Math.abs(entropyPoint.deltaY - (lastPoint.deltaY || 0));
        }
        
        entropyData.push(entropyPoint);
        
        // Draw movement trail
        ctx.globalAlpha = 0.8;
        ctx.fillStyle = '#4169E1';
        ctx.beginPath();
        ctx.arc(x * (canvas.width / rect.width), y * (canvas.height / rect.height), 2, 0, 2 * Math.PI);
        ctx.fill();
        
        // Update progress
        const progress = Math.min(entropyData.length / 256, 1);
        entropyBar.style.width = (progress * 100) + '%';
        entropyText.textContent = `Entropy: ${Math.floor(progress * 256)}/256 bits`;
        
        // Check if we have enough entropy
        if (entropyData.length >= 256 && !entropyComplete) {
            entropyComplete = true;
            document.getElementById('entropy-instructions').textContent = '✅ Entropy collection complete!';
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            
            // Fade out the trail
            ctx.globalAlpha = 0.3;
        }
    });
    
    // Enable submit by default, make entropy optional
    submitButton.disabled = false;
    submitButton.style.opacity = '1';
    
    // Add entropy data to form submission (if available)
    form.addEventListener('submit', function(e) {
        // Always add entropy data, even if empty (backend can handle fallback)
        const entropyInput = document.createElement('input');
        entropyInput.type = 'hidden';
        entropyInput.name = 'mouse_entropy';
        entropyInput.value = JSON.stringify(entropyData.length > 0 ? entropyData : []);
        form.appendChild(entropyInput);
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.textContent = '🔄 Creating Account...';
    });
});
</script>

</body>
</html>