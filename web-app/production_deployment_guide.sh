#!/bin/bash

# =================================================================
# HAICHAN PRODUCTION DEPLOYMENT GUIDE
# =================================================================
# 
# This script sets up Haichan for production deployment on a real server.
# Requirements: Ubuntu 22.04 LTS, Nginx, PHP 8.1+, MySQL/PostgreSQL
#
# Usage: Run as root or with sudo privileges
# =================================================================

echo "=== Haichan Production Deployment ==="

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo "This script should NOT be run as root. Please run as a regular user with sudo."
   exit 1
fi

# Variables - UPDATE THESE FOR YOUR SERVER
DOMAIN="your-domain.com"
APP_DIR="/var/www/haichan"
DB_PASSWORD="your_secure_database_password"
APP_KEY=""  # Will be generated
NGINX_AVAILABLE="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"

echo "Domain: $DOMAIN"
echo "App Directory: $APP_DIR"
echo ""

read -p "Are these settings correct? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Please edit the script variables and run again."
    exit 1
fi

# =================================================================
# 1. SYSTEM DEPENDENCIES
# =================================================================

echo "Installing system dependencies..."

# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
    nginx \
    mysql-server \
    php8.1-fpm \
    php8.1-cli \
    php8.1-mysql \
    php8.1-xml \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-zip \
    php8.1-gd \
    php8.1-imagick \
    php8.1-intl \
    php8.1-bcmath \
    composer \
    git \
    unzip \
    supervisor \
    certbot \
    python3-certbot-nginx

echo "✓ System dependencies installed"

# =================================================================
# 2. DATABASE SETUP
# =================================================================

echo "Setting up database..."

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -e "CREATE DATABASE haichan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'haichan'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON haichan.* TO 'haichan'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "✓ Database created"

# =================================================================
# 3. APPLICATION DEPLOYMENT
# =================================================================

echo "Deploying application..."

# Create app directory
sudo mkdir -p $APP_DIR
sudo chown $USER:www-data $APP_DIR

# Clone or copy your application
cd $APP_DIR

# If deploying from your development environment:
# rsync -av --exclude='node_modules' --exclude='.git' /root/haichan/web-app/ ./

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Create environment file
cat > .env << EOF
APP_NAME=Haichan
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=haichan
DB_USERNAME=haichan
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
EOF

# Generate application key
php artisan key:generate

# Create storage directories
mkdir -p storage/app/public/images
mkdir -p storage/app/public/thumbs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set permissions
sudo chown -R $USER:www-data .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache

# Create symlink for storage
php artisan storage:link

echo "✓ Application deployed"

# =================================================================
# 4. CREATE PRODUCTION MODELS AND CONTROLLERS
# =================================================================

echo "Creating production-ready models and controllers..."

# Create the complete BoardController
cat > app/Http/Controllers/BoardController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;
use Intervention\Image\ImageManagerStatic as Image;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::where('active', true)
            ->orderBy('name')
            ->withCount('threads')
            ->get();

        return view('boards.index', compact('boards'));
    }

    public function show(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $threads = $board->threads()
            ->with(['posts' => function($query) {
                $query->latest()->limit(3);
            }])
            ->orderBy('sticky', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->paginate(20);

        return view('boards.show', compact('board', 'threads'));
    }

    public function showThread(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        $posts = $thread->posts()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('boards.thread', compact('board', 'thread', 'posts'));
    }

    public function storeThread(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        // Rate limiting
        $recentThreads = Thread::where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
            
        if ($recentThreads >= 1) {
            return back()->withErrors(['error' => 'Please wait before creating another thread.']);
        }

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = $this->generatePosterHash($request->ip(), 0);

        $thread = Thread::create([
            'board_id' => $board->id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'content' => $this->sanitizeContent($request->content),
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash,
            'bumped_at' => now()
        ]);

        // Update poster hash with actual thread ID
        $thread->update([
            'poster_hash' => $this->generatePosterHash($request->ip(), $thread->id)
        ]);

        if ($imageData) {
            $thread->increment('image_count');
        }

        $board->incrementPostCount();

        return redirect("/{$board->name}/thread/{$thread->id}");
    }

    public function storePost(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        if ($thread->locked) {
            return back()->withErrors(['error' => 'Thread is locked']);
        }

        $request->validate([
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        // Rate limiting
        $recentPosts = Post::where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subMinutes(1))
            ->count();
            
        if ($recentPosts >= 1) {
            return back()->withErrors(['error' => 'Please wait before posting again.']);
        }

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = $this->generatePosterHash($request->ip(), $thread->id);

        $post = Post::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'content' => $this->sanitizeContent($request->content),
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash
        ]);

        $thread->addReply($post);

        return redirect("/{$board->name}/thread/{$thread->id}#post{$post->id}");
    }

    private function handleImageUpload($file)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        // Store original image
        $file->storeAs('public/images', $filename);

        // Create thumbnail (200x200 max)
        $image = Image::make($file);
        $image->fit(200, 200, function ($constraint) {
            $constraint->upsize();
        });
        
        Storage::put('public/thumbs/' . $filename, (string) $image->encode());

        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size
        ];
    }

    private function generatePosterHash($ip, $threadId)
    {
        return substr(hash('sha256', $ip . $threadId . config('app.key')), 0, 8);
    }

    private function sanitizeContent($content)
    {
        // Basic content sanitization
        $content = trim($content);
        $content = preg_replace('/\r\n?/', "\n", $content); // Normalize line endings
        $content = preg_replace('/\n{3,}/', "\n\n", $content); // Limit consecutive line breaks
        
        return $content;
    }
}
EOF

# Update ProofOfWorkController for thread bumping
cat > app/Http/Controllers/ProofOfWorkController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Board;
use App\Models\Thread;
use App\Models\ProofOfWork;

class ProofOfWorkController extends Controller
{
    public function submitProof(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800,21e8000,000021e8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid proof format'
            ], 422);
        }

        // Verify the proof
        $hash = $request->input('hash');
        $data = $request->input('data');
        $pattern = $request->input('pattern');
        
        // Calculate hash from data
        $calculatedHash = hash('sha256', $data);
        
        // Verify hash matches
        if ($calculatedHash !== strtolower($hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Hash verification failed'
            ], 400);
        }
        
        // Verify pattern exists in hash
        if (strpos(strtolower($hash), strtolower($pattern)) === false) {
            return response()->json([
                'success' => false,
                'message' => 'Pattern not found in hash'
            ], 400);
        }
        
        // Check for duplicate submissions
        if (ProofOfWork::where('hash', $hash)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Proof already submitted'
            ], 400);
        }
        
        // Calculate points
        $points = $this->calculatePoints($pattern);
        
        // Store proof
        ProofOfWork::create([
            'hash' => $hash,
            'nonce' => $request->input('nonce'),
            'data' => $data,
            'pattern' => $pattern,
            'points' => $points,
            'ip_address' => $request->ip(),
            'verified_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Proof accepted!',
            'points' => $points,
            'total_points' => $points
        ]);
    }

    public function bumpThread(Request $request, $boardName, $threadId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof format'
            ], 422);
        }

        $board = Board::where('name', $boardName)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);

        // Rate limiting for bumps
        $recentBumps = ProofOfWork::where('thread_id', $threadId)
            ->where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentBumps >= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before bumping again'
            ], 429);
        }

        // Verify the proof
        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'),
            $request->input('hash'),
            $request->input('pattern')
        );

        if (!$verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error']
            ], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        // Store the proof
        ProofOfWork::create([
            'thread_id' => $threadId,
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'verified_at' => now(),
            'ip_address' => $request->ip()
        ]);

        // Bump the thread
        $thread->increment('bump_score', $points);
        $thread->update(['bumped_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Thread bumped successfully',
            'points' => $points,
            'thread_bump_score' => $thread->bump_score
        ]);
    }

    private function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        $calculatedHash = hash('sha256', $data);

        if ($calculatedHash !== strtolower($submittedHash)) {
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (strpos(strtolower($calculatedHash), strtolower($pattern)) === false) {
            return ['valid' => false, 'error' => 'Pattern not found'];
        }

        if (ProofOfWork::where('hash', $calculatedHash)->exists()) {
            return ['valid' => false, 'error' => 'Duplicate proof'];
        }

        return ['valid' => true];
    }

    private function calculatePoints($pattern)
    {
        $points = [
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 125,
            '000021e8' => 625
        ];
        return $points[$pattern] ?? 1;
    }

    public function getStats()
    {
        return response()->json([
            'total_proofs' => ProofOfWork::count(),
            'top_miners' => []
        ]);
    }

    public function startMiningSession(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function endMiningSession(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
EOF

echo "✓ Controllers created"

# =================================================================
# 5. RUN MIGRATIONS AND SEED DATA
# =================================================================

echo "Running migrations and seeding data..."

# Run migrations
php artisan migrate --force

# Seed boards
php artisan tinker --execute="
App\Models\Board::create(['name' => 'gen', 'title' => '/gen/ - General', 'description' => 'General discussion and random topics']);
App\Models\Board::create(['name' => 'tech', 'title' => '/tech/ - Technology', 'description' => 'Technology, programming, and computing']);
App\Models\Board::create(['name' => 'biz', 'title' => '/biz/ - Business & Finance', 'description' => 'Business, finance, and entrepreneurship']);
App\Models\Board::create(['name' => 'film', 'title' => '/film/ - Film & TV', 'description' => 'Movies, television, and media discussion']);
App\Models\Board::create(['name' => 'x', 'title' => '/x/ - Paranormal', 'description' => 'Paranormal, conspiracy theories, and unexplained']);
App\Models\Board::create(['name' => 'lit', 'title' => '/lit/ - Literature', 'description' => 'Books, writing, and literary discussion']);
echo 'Boards seeded successfully!';
"

# Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Database seeded and cached"

# =================================================================
# 6. NGINX CONFIGURATION
# =================================================================

echo "Configuring Nginx..."

# Create Nginx site configuration
sudo tee $NGINX_AVAILABLE/haichan << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/public;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # File upload size
    client_max_body_size 10M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }
}
EOF

# Enable site
sudo ln -sf $NGINX_AVAILABLE/haichan $NGINX_ENABLED/
sudo rm -f $NGINX_ENABLED/default

# Test Nginx configuration
sudo nginx -t

if [ $? -eq 0 ]; then
    sudo systemctl reload nginx
    echo "✓ Nginx configured successfully"
else
    echo "✗ Nginx configuration error"
    exit 1
fi

# =================================================================
# 7. SSL CERTIFICATE
# =================================================================

echo "Setting up SSL certificate..."

# Get SSL certificate from Let's Encrypt
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# Set up auto-renewal
sudo systemctl enable certbot.timer

echo "✓ SSL certificate configured"

# =================================================================
# 8. SECURITY AND OPTIMIZATION
# =================================================================

echo "Applying security and optimization..."

# Create robots.txt
cat > public/robots.txt << 'EOF'
User-agent: *
Disallow: /storage/
Disallow: /vendor/
Allow: /

Sitemap: https://your-domain.com/sitemap.xml
EOF

# Create basic .htaccess for Apache (if needed)
cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# Set up log rotation
sudo tee /etc/logrotate.d/haichan << EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        php $APP_DIR/artisan queue:restart > /dev/null 2>&1 || true
    endscript
}
EOF

# Create backup script
sudo tee /usr/local/bin/haichan-backup << 'EOF'
#!/bin/bash

BACKUP_DIR="/var/backups/haichan"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/haichan"
DB_NAME="haichan"
DB_USER="haichan"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $APP_DIR storage/app/public

# Keep only last 7 backups
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF

sudo chmod +x /usr/local/bin/haichan-backup

# Set up cron for daily backups
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/haichan-backup") | crontab -

# Configure PHP-FPM for production
sudo sed -i 's/;opcache.enable=1/opcache.enable=1/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=256/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=20000/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 10M/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 10M/' /etc/php/8.1/fpm/php.ini

sudo systemctl reload php8.1-fpm

echo "✓ Security and optimization configured"

# =================================================================
# 9. MONITORING AND MAINTENANCE
# =================================================================

echo "Setting up monitoring..."

# Create basic monitoring script
sudo tee /usr/local/bin/haichan-monitor << 'EOF'
#!/bin/bash

APP_DIR="/var/www/haichan"
LOG_FILE="/var/log/haichan-monitor.log"

# Check if application is responding
if ! curl -f -s -o /dev/null http://localhost; then
    echo "$(date): Application not responding" >> $LOG_FILE
    sudo systemctl reload nginx
    sudo systemctl reload php8.1-fpm
fi

# Check disk usage
DISK_USAGE=$(df $APP_DIR | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): Disk usage high: ${DISK_USAGE}%" >> $LOG_FILE
fi

# Cleanup old logs
find $APP_DIR/storage/logs -name "*.log" -mtime +30 -delete
EOF

sudo chmod +x /usr/local/bin/haichan-monitor

# Set up monitoring cron (every 5 minutes)
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/haichan-monitor") | crontab -

echo "✓ Monitoring configured"

# =================================================================
# 10. FINAL SETUP
# =================================================================

echo "Final setup steps..."

# Create production welcome page redirect
cat > resources/views/welcome.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Haichan</title>
    <meta http-equiv="refresh" content="0;url=/boards">
    <style>
        body { 
            font-family: serif; 
            background: #3D315B; 
            color: #FFFFEE; 
            text-align: center; 
            padding: 100px; 
        }
        h1 { font-size: 48px; margin-bottom: 20px; }
        a { color: #9AB87A; font-size: 24px; }
    </style>
</head>
<body>
    <h1>Haichan</h1>
    <p>A proof-of-work image board</p>
    <p><a href="/boards">Enter Boards</a></p>
</body>
</html>
EOF

# Set final permissions
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Clear all caches one final time
php artisan config:clear
php artisan route:cache
php artisan view:cache
php artisan config:cache

echo "✓ Final setup completed"

# =================================================================
# DEPLOYMENT COMPLETE
# =================================================================

echo ""
echo "================================================================="
echo "🎉 HAICHAN PRODUCTION DEPLOYMENT COMPLETE!"
echo "================================================================="
echo ""
echo "🌐 Your site is now available at:"
echo "   https://$DOMAIN"
echo ""
echo "📋 Available boards:"
echo "   https://$DOMAIN/gen   - General"
echo "   https://$DOMAIN/tech  - Technology"
echo "   https://$DOMAIN/biz   - Business"
echo "   https://$DOMAIN/film  - Film & TV"  
echo "   https://$DOMAIN/x     - Paranormal"
echo "   https://$DOMAIN/lit   - Literature"
echo ""
echo "⛏️  Mining dashboard:"
echo "   https://$DOMAIN/mining"
echo ""
echo "🔧 IMPORTANT NEXT STEPS:"
echo "   1. Update DNS records to point $DOMAIN to this server"
echo "   2. Test image uploads at https://$DOMAIN/gen"
echo "   3. Monitor logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "   4. Backups run daily at 2 AM via cron"
echo "   5. SSL certificate auto-renews via certbot"
echo ""
echo "🛡️  SECURITY:"
echo "   - Change database password in .env if needed"
echo "   - Review firewall settings (UFW recommended)"
echo "   - Monitor /var/log/haichan-monitor.log"
echo ""
echo "📊 PERFORMANCE:"
echo "   - OPcache enabled for PHP"
echo "   - Nginx gzip compression enabled"
echo "   - Laravel config/route/view caching enabled"
echo ""
echo "================================================================="
`
