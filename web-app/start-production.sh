#!/bin/bash

echo "🚀 Starting Haichan with Production SSL (Let's Encrypt)..."
echo

# Check if Let's Encrypt certificates exist
if [[ ! -f "/etc/letsencrypt/live/144.202.67.170.nip.io/fullchain.pem" ]]; then
    echo "❌ Let's Encrypt certificates not found!"
    echo "Run: sudo certbot --nginx -d 144.202.67.170.nip.io"
    exit 1
fi

echo "✅ Let's Encrypt certificates found"

# Start services
echo "🔧 Starting PHP-FPM..."
systemctl start php8.2-fpm
if ! systemctl is-active --quiet php8.2-fpm; then
    echo "❌ Failed to start PHP-FPM"
    exit 1
fi
echo "✅ PHP-FPM started"

echo "🌐 Starting Nginx..."
systemctl start nginx
if ! systemctl is-active --quiet nginx; then
    echo "❌ Failed to start Nginx"
    exit 1
fi
echo "✅ Nginx started"

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
cd /root/haichan/web-app
php artisan config:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet

echo
echo "🎉 Haichan is running with PRODUCTION SSL!"
echo
echo "🌍 Public Access URLs:"
echo "   🔐 HTTPS: https://144.202.67.170.nip.io"
echo "   ⛏️ Mining: https://144.202.67.170.nip.io/mining"  
echo "   📋 Boards: https://144.202.67.170.nip.io/anon"
echo "   🔍 Health: https://144.202.67.170.nip.io/api/health"
echo
echo "🔒 SSL Certificate Details:"
echo "   • Issued by: Let's Encrypt Authority"
echo "   • Valid until: $(openssl x509 -enddate -noout -in /etc/letsencrypt/live/144.202.67.170.nip.io/fullchain.pem | cut -d= -f2)"
echo "   • Auto-renewal: Enabled via certbot cron"
echo
echo "✅ Benefits of Real SSL:"
echo "   • No browser security warnings"
echo "   • Trusted certificate chain" 
echo "   • Full Web Crypto API support"
echo "   • Production-ready security"
echo
echo "📊 Service Status:"
systemctl is-active --quiet php8.2-fpm && echo "   ✅ PHP-FPM: Running" || echo "   ❌ PHP-FPM: Stopped"
systemctl is-active --quiet nginx && echo "   ✅ Nginx: Running" || echo "   ❌ Nginx: Stopped"
echo
echo "🛑 To stop services:"
echo "   sudo systemctl stop nginx php8.2-fpm"
echo