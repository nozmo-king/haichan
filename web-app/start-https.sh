#!/bin/bash

echo "🚀 Starting Haichan with HTTPS support..."
echo

# Check if SSL certificates exist
if [[ ! -f "ssl/cert.pem" || ! -f "ssl/key.pem" ]]; then
    echo "📜 SSL certificates not found. Creating self-signed certificate..."
    mkdir -p ssl
    openssl req -x509 -newkey rsa:4096 -keyout ssl/key.pem -out ssl/cert.pem -days 365 -nodes \
        -subj "/C=US/ST=CA/L=San Francisco/O=Haichan/OU=Development/CN=localhost"
    echo "✅ SSL certificates created"
    echo
fi

# Stop any existing servers
echo "🛑 Stopping existing servers..."
pkill -f "php artisan serve" 2>/dev/null || true
pkill -f "stunnel" 2>/dev/null || true
sleep 2

# Start HTTP server (localhost only)
echo "🌐 Starting HTTP server on localhost:8000..."
php artisan serve --host=127.0.0.1 --port=8000 &
HTTP_PID=$!
sleep 3

# Check if HTTP server started
if ps -p $HTTP_PID > /dev/null 2>&1; then
    echo "✅ HTTP server started successfully"
else
    echo "❌ Failed to start HTTP server"
    exit 1
fi

# Start HTTPS proxy
echo "🔐 Starting HTTPS proxy on port 8443..."
stunnel /etc/stunnel/haichan.conf &
STUNNEL_PID=$!
sleep 2

# Check if HTTPS proxy started
if ps -p $STUNNEL_PID > /dev/null 2>&1; then
    echo "✅ HTTPS proxy started successfully"
else
    echo "❌ Failed to start HTTPS proxy"
    kill $HTTP_PID 2>/dev/null || true
    exit 1
fi

echo
echo "🎉 Haichan is now running with HTTPS support!"
echo
echo "📍 Access URLs:"
echo "   🔓 HTTP:  http://localhost:8000  (limited functionality)"
echo "   🔐 HTTPS: https://localhost:8443 (full mining support)"
echo
echo "🔑 Pages to try:"
echo "   • Mining Dashboard: https://localhost:8443/mining"
echo "   • Anonymous Boards: https://localhost:8443/anon" 
echo "   • API Health:      https://localhost:8443/api/health"
echo
echo "⚠️  Note: You'll see a security warning for the self-signed certificate."
echo "   Click 'Advanced' → 'Proceed to localhost (unsafe)' to continue."
echo
echo "🛑 To stop servers:"
echo "   pkill -f 'php artisan serve'"
echo "   pkill -f 'stunnel'"
echo

# Keep script running to show status
trap 'echo ""; echo "🛑 Stopping servers..."; kill $HTTP_PID $STUNNEL_PID 2>/dev/null || true; exit 0' INT

echo "✨ Servers are running. Press Ctrl+C to stop."
wait