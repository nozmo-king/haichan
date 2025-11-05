@extends('layout')

@section('title', $room->name . ' Chat - Haichan PoW Imageboard')

@section('content')
<div style="margin: 20px auto; max-width: 1200px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px;">
    
    <!-- Room Header -->
    <div style="background: #708B75; color: #F5F5DC; padding: 20px; border-radius: 6px 6px 0 0;">
        <h2 style="margin: 0; font-family: 'Nova Cut', serif;">💬 {{ $room->name }}</h2>
        <p style="margin: 8px 0 0 0; opacity: 0.9;">{{ $room->description }}</p>
    </div>

    <!-- Chat Layout -->
    <div style="display: flex; gap: 20px;">
        <!-- Online Users Sidebar -->
        <div style="width: 200px; background: #F5F5DC; padding: 15px; border-radius: 5px;">
            <h4 style="margin: 0 0 10px 0; color: #3D315B; font-size: 14px;">👥 Online Users</h4>
            <div id="online-users" style="font-size: 12px; line-height: 1.4;">
                <div style="color: #6B7A6B; font-style: italic;">Loading...</div>
            </div>
            
            <!-- Nickname Settings -->
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #708B75;">
                <h5 style="margin: 0 0 8px 0; color: #3D315B; font-size: 12px;">✏️ Display Name</h5>
                <input type="text" id="nickname-input" placeholder="Your nickname..." maxlength="20"
                       style="width: 100%; padding: 5px; font-size: 11px; border: 1px solid #708B75; border-radius: 3px; box-sizing: border-box;">
                <button id="save-nickname" style="width: 100%; margin-top: 5px; padding: 5px; font-size: 10px; background: #708B75; color: white; border: none; border-radius: 3px; cursor: pointer;">
                    <span id="save-nickname-emoji">✏️</span> Save
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div style="flex: 1;">
            <div id="chat-messages" style="height: 400px; padding: 20px; overflow-y: auto; background: #FFFACD; border: 1px solid #708B75; border-radius: 5px;">
                @if(count($messages) > 0)
                    @foreach($messages as $message)
                        @php
                            $diamondColor = null;
                            $diamondEmoji = '';
                            if ($message->user && $message->user->personal_21e8_hash) {
                                $hash = strtolower($message->user->personal_21e8_hash);
                                if (str_starts_with($hash, '21e80000')) {
                                    $diamondColor = '#FF1493';
                                    $diamondEmoji = '💎 ';
                                } elseif (str_starts_with($hash, '21e8000')) {
                                    $diamondColor = '#FF00FF';
                                    $diamondEmoji = '💎 ';
                                } elseif (str_starts_with($hash, '21e800')) {
                                    $diamondColor = '#9370DB';
                                    $diamondEmoji = '💎 ';
                                } elseif (str_starts_with($hash, '21e80')) {
                                    $diamondColor = '#4169E1';
                                    $diamondEmoji = '💎 ';
                                } elseif (str_starts_with($hash, '21e8')) {
                                    $diamondColor = '#00CED1';
                                    $diamondEmoji = '💎 ';
                                }
                            }
                            $usernameColor = $diamondColor ?? '#6B7A6B';
                        @endphp
                        <div style="margin-bottom: 15px; padding: 10px; background: #F5F5DC; border-radius: 5px; border-left: 3px solid #708B75;">
                            <div style="font-size: 11px; margin-bottom: 5px;">
                                <strong style="color: {{ $usernameColor }};">{{ $diamondEmoji }}{{ $message->username ?? $message->user->username ?? 'Anonymous' }}</strong>
                                <span style="margin-left: 10px; color: #6B7A6B;">{{ $message->created_at->format('H:i:s') }}</span>
                            </div>
                            <div style="color: #3D315B;">{{ $message->message }}</div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; color: #6B7A6B; font-style: italic; padding: 40px;">
                        No messages yet. Be the first to chat!
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Message Input -->
    <div style="padding: 20px; background: #F5F5DC;">
        <div style="position: relative;">
            <form id="chat-form" style="display: flex; gap: 10px;">
                @csrf
                <div style="flex: 1; position: relative;">
                    <input type="text" id="message-input" placeholder="Type your message or / for commands..." 
                           style="width: 100%; padding: 10px; border: 1px solid #708B75; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px; box-sizing: border-box;">
                    
                    <!-- Command Palette -->
                    <div id="command-palette" style="position: absolute; bottom: 100%; left: 0; right: 0; background: #1a1a1a; border: 2px solid #00ff9f; border-radius: 6px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none; font-family: 'Courier New', monospace;">
                        <div style="padding: 10px; background: #2a2a2a; color: #00ff9f; font-weight: bold; font-size: 12px; border-bottom: 1px solid #00ff9f;">
                            🖥️ TERMINAL COMMANDS
                        </div>
                        <div id="command-list" style="padding: 5px 0;">
                            <div class="command-item" data-command="/help" style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/help</strong> - Show available commands
                            </div>
                            <div class="command-item" data-command="/join " style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/join [room]</strong> - Join a different room
                            </div>
                            <div class="command-item" data-command="/create " style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/create [name]</strong> - Create new room
                            </div>
                            <div class="command-item" data-command="/list" style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/list</strong> - List all rooms
                            </div>
                            <div class="command-item" data-command="/nick " style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/nick [name]</strong> - Change nickname
                            </div>
                            <div class="command-item" data-command="/users" style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/users</strong> - List online users
                            </div>
                            <div class="command-item" data-command="/clear" style="padding: 8px 12px; color: #00ff9f; cursor: pointer; border-bottom: 1px solid #333;">
                                <strong>/clear</strong> - Clear chat history
                            </div>
                            <div class="command-item" data-command="/quit" style="padding: 8px 12px; color: #00ff9f; cursor: pointer;">
                                <strong>/quit</strong> - Leave room
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" id="send-button"
                        style="padding: 10px 20px; background: #708B75; color: #F5F5DC; border: none; border-radius: 4px; cursor: pointer; font-family: 'Courier New', monospace;">
                    <span id="send-emoji">💬</span> SEND
                </button>
            </form>
            <div id="status" style="margin-top: 10px; font-size: 12px; color: #6B7A6B; font-family: 'Courier New', monospace;"></div>
        </div>
    </div>
</div>

<script nonce="{{ app('csp_nonce') }}" src="/js/simple-pow.js"></script>
<script nonce="{{ app('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const statusDiv = document.getElementById('status');
    const chatMessages = document.getElementById('chat-messages');
    const commandPalette = document.getElementById('command-palette');
    
    let lastMessageId = {{ $messages->last()?->id ?? 0 }};
    let selectedCommandIndex = -1;
    
    // Command palette functionality
    messageInput.addEventListener('input', function() {
        const value = this.value;
        
        if (value === '/' || (value.startsWith('/') && value.length > 1 && !value.includes(' '))) {
            showCommandPalette();
        } else {
            hideCommandPalette();
        }
    });
    
    messageInput.addEventListener('keydown', function(e) {
        if (commandPalette.style.display === 'block') {
            const commands = commandPalette.querySelectorAll('.command-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedCommandIndex = Math.min(selectedCommandIndex + 1, commands.length - 1);
                updateCommandSelection(commands);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedCommandIndex = Math.max(selectedCommandIndex - 1, -1);
                updateCommandSelection(commands);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedCommandIndex >= 0) {
                    const selectedCommand = commands[selectedCommandIndex];
                    selectCommand(selectedCommand.dataset.command);
                }
                return;
            } else if (e.key === 'Escape') {
                hideCommandPalette();
            }
        } else if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (messageInput.value.trim()) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
    
    function showCommandPalette() {
        commandPalette.style.display = 'block';
        selectedCommandIndex = -1;
    }
    
    function hideCommandPalette() {
        commandPalette.style.display = 'none';
        selectedCommandIndex = -1;
    }
    
    function updateCommandSelection(commands) {
        commands.forEach((cmd, index) => {
            if (index === selectedCommandIndex) {
                cmd.style.backgroundColor = '#333';
            } else {
                cmd.style.backgroundColor = 'transparent';
            }
        });
    }
    
    function selectCommand(command) {
        messageInput.value = command;
        hideCommandPalette();
        messageInput.focus();
    }
    
    // Click handlers for command palette
    commandPalette.addEventListener('click', function(e) {
        if (e.target.classList.contains('command-item') || e.target.closest('.command-item')) {
            const commandItem = e.target.classList.contains('command-item') ? e.target : e.target.closest('.command-item');
            selectCommand(commandItem.dataset.command);
        }
    });
    
    // Command hover effects
    commandPalette.querySelectorAll('.command-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#333';
        });
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
    });
    
    // Handle chat commands
    async function handleChatCommand(command) {
        console.log('Executing command:', command);
        statusDiv.innerHTML = '<span id="status-emoji">⚡</span> Processing command...';
        
        try {
            const response = await fetch(`/chat/{{ $room->slug }}/command`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    command: command
                })
            });
            
            console.log('Command response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Command failed:', response.status, response.statusText, errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('Command result:', result);
            
            if (result.success) {
                messageInput.value = '';
                statusDiv.innerHTML = '<span id="status-emoji">✅</span> Command executed!';
                
                // Handle different command responses
                if (result.action === 'redirect' && result.url) {
                    window.location.href = result.url;
                } else if (result.action === 'system_message' && result.message) {
                    // Add system message to chat
                    addSystemMessageToChat(result.message);
                } else if (result.redirect_url) {
                    setTimeout(() => {
                        window.location.href = result.redirect_url;
                    }, 1000);
                }
                
                setTimeout(() => statusDiv.textContent = '', 2000);
            } else {
                statusDiv.innerHTML = '<span id="status-emoji">❌</span> Error: ' + (result.error || 'Command failed');
                setTimeout(() => statusDiv.textContent = '', 3000);
            }
            
        } catch (error) {
            console.error('Command error:', error);
            statusDiv.innerHTML = '<span id="status-emoji">❌</span> Error: ' + error.message;
            setTimeout(() => statusDiv.textContent = '', 3000);
        }
    }
    
    // Add system message to chat display
    function addSystemMessageToChat(message) {
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = 'margin-bottom: 15px; padding: 10px; background: #E8F5E8; border-radius: 5px; border-left: 3px solid #00ff9f;';
        
        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = 'color: #2E8B57; font-weight: bold; font-family: monospace;';
        contentDiv.innerHTML = message;
        
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) {
            console.log('Empty message, not sending');
            return;
        }
        
        // Hide command palette if open
        hideCommandPalette();
        
        // Check if this is a command
        if (message.startsWith('/')) {
            return handleChatCommand(message);
        }
        
        sendButton.disabled = true;
        
        // Animate send button
        setTimeout(() => {
            if (window.emojiAnimator) {
                window.emojiAnimator.startAnimation('send-emoji', ['💬', '📨', '✉️', '💬'], 180);
            }
        }, 50);
        statusDiv.innerHTML = '<span id="status-emoji">📤</span> Sending...';
        
        // Animate sending status
        setTimeout(() => {
            if (window.emojiAnimator) {
                window.emojiAnimator.startAnimation('status-emoji', ['📤', '✈️', '🎯', '📤'], 150);
            }
        }, 50);
        
        try {
            // First mine PoW for the message
            statusDiv.innerHTML = '<span id="status-emoji">⛏️</span> Mining proof-of-work...';
            
            if (!window.simplePoW) {
                throw new Error('PoW system not available');
            }
            
            const proof = await window.simplePoW.acquireProofFor({
                target_type: 'chat_message',
                action: 'send',
                difficulty: '21e8',
                board_code: null,
                target_id: '{{ $room->id }}'
            });
            
            statusDiv.innerHTML = '<span id="status-emoji">📤</span> Sending message...';
            
            console.log('🚀 Attempting to send chat message...');
            console.log('📍 URL:', `/chat/{{ $room->slug }}/send`);
            
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('🔑 Using CSRF token:', csrfToken.substring(0, 20) + '...');
            
            // Send message with POW
            const response = await fetch(`/chat/{{ $room->slug }}/send`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    message: message,
                    pow_nonce: String(proof.nonce),
                    pow_hash: proof.hash,
                    pow_challenge_id: proof.challenge_id
                })
            });
            
            let result;
            try {
                const responseText = await response.text();
                console.log('Chat response:', responseText);
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Failed to parse response:', parseError);
                throw new Error('Server returned invalid response: ' + parseError.message);
            }
            
            if (result.success) {
                messageInput.value = '';
                statusDiv.innerHTML = '<span id="status-emoji">✅</span> Message sent!';
                
                // Success animation
                setTimeout(() => {
                    if (window.emojiAnimator) {
                        window.emojiAnimator.startAnimation('status-emoji', ['✅', '🎉', '⭐', '✅'], 200);
                    }
                }, 50);
                
                setTimeout(() => statusDiv.textContent = '', 2000);
                
                // Add message to chat immediately and update lastMessageId
                addMessageToChat(result.message);
                if (result.message.id) {
                    lastMessageId = Math.max(lastMessageId, result.message.id);
                }
                scrollToBottom();
            } else {
                statusDiv.innerHTML = '<span id="status-emoji">❌</span> Error: ' + result.error;
                
                // Error animation
                setTimeout(() => {
                    if (window.emojiAnimator) {
                        window.emojiAnimator.startAnimation('status-emoji', ['❌', '💥', '⚠️', '❌'], 300);
                    }
                }, 50);
                
                setTimeout(() => statusDiv.textContent = '', 3000);
            }
            
        } catch (error) {
            console.error('Error sending message:', error);
            statusDiv.innerHTML = '<span id="status-emoji">❌</span> Error: ' + error.message;
            
            // Error animation
            setTimeout(() => {
                if (window.emojiAnimator) {
                    window.emojiAnimator.startAnimation('status-emoji', ['❌', '💥', '⚠️', '❌'], 300);
                }
            }, 50);
            
            setTimeout(() => statusDiv.textContent = '', 3000);
        } finally {
            sendButton.disabled = false;
        }
    });
    
    // Add message to chat display
    function addMessageToChat(message) {
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = 'margin-bottom: 15px; padding: 10px; background: #F5F5DC; border-radius: 5px; border-left: 3px solid #708B75;';
        
        const headerDiv = document.createElement('div');
        headerDiv.style.cssText = 'font-size: 11px; margin-bottom: 5px;';
        
        // Apply diamond color to username if available
        const usernameColor = message.diamond_color || '#6B7A6B';
        const diamondEmoji = message.diamond_color ? '💎 ' : '';
        headerDiv.innerHTML = `<strong style="color: ${usernameColor};">${diamondEmoji}${message.username}</strong> <span style="margin-left: 10px; color: #6B7A6B;">${message.created_at}</span>`;
        
        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = 'color: #3D315B;';
        contentDiv.textContent = message.message;
        
        messageDiv.appendChild(headerDiv);
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
    }
    
    // Scroll to bottom of chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Poll for new messages
    setInterval(async function() {
        try {
            const response = await fetch(`/chat/{{ $room->slug }}/messages?after=${lastMessageId}`);
            const result = await response.json();
            
            if (result.success && result.messages.length > 0) {
                result.messages.forEach(message => {
                    if (message.id > lastMessageId) {
                        lastMessageId = message.id;
                        addMessageToChat(message);
                    }
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }, 3000); // Poll every 3 seconds
    
    // Load online users
    async function loadOnlineUsers() {
        try {
            console.log('Loading online users for room:', '{{ $room->slug }}');
            const response = await fetch(`/chat/{{ $room->slug }}/users`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Users response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Users request failed:', response.status, response.statusText, errorText);
                const usersDiv = document.getElementById('online-users');
                usersDiv.innerHTML = `<div style="color: #d00; font-style: italic;">HTTP ${response.status}: ${response.statusText}</div>`;
                return;
            }
            
            const result = await response.json();
            console.log('Users response:', result);
            
            if (result.success && result.users) {
                const usersDiv = document.getElementById('online-users');
                if (result.users.length === 0) {
                    usersDiv.innerHTML = '<div style="color: #6B7A6B; font-style: italic;">No users online</div>';
                } else {
                    usersDiv.innerHTML = result.users.map(user => 
                        `<div style="padding: 3px 0; color: #3D315B;">${user.display_name || user.username || 'Anonymous'}</div>`
                    ).join('');
                }
                
                // Show debug info if available
                if (result.debug) {
                    console.log('Debug info:', result.debug);
                }
            } else {
                console.error('Users API returned error:', result);
                const usersDiv = document.getElementById('online-users');
                usersDiv.innerHTML = `<div style="color: #d00; font-style: italic;">API Error: ${result.error || 'Unknown error'}</div>`;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            const usersDiv = document.getElementById('online-users');
            usersDiv.innerHTML = '<div style="color: #d00; font-style: italic;">Connection error: ' + error.message + '</div>';
        }
    }
    
    // Load users initially and refresh periodically
    loadOnlineUsers();
    setInterval(loadOnlineUsers, 10000); // Every 10 seconds
    
    // Handle nickname save
    document.getElementById('save-nickname').addEventListener('click', async function() {
        const nickname = document.getElementById('nickname-input').value.trim();
        if (!nickname) return;
        
        try {
            const response = await fetch(`/chat/{{ $room->slug }}/set-nickname`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ nickname: nickname })
            });
            
            const result = await response.json();
            if (result.success) {
                statusDiv.innerHTML = '<span id="status-emoji">✅</span> Nickname saved!';
                
                // Success animation
                setTimeout(() => {
                    if (window.emojiAnimator) {
                        window.emojiAnimator.startAnimation('status-emoji', ['✅', '🎉', '⭐', '✅'], 200);
                        window.emojiAnimator.startAnimation('save-nickname-emoji', ['✏️', '💾', '✅', '✏️'], 250);
                    }
                }, 50);
                
                setTimeout(() => statusDiv.textContent = '', 2000);
            }
        } catch (error) {
            console.error('Error saving nickname:', error);
        }
    });
});
</script>
@endsection
