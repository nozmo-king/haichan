<?php

/**
 * Simple HTTPS server for Haichan development
 * Enables crypto.subtle API for PoW mining
 */
$host = '0.0.0.0';
$port = 8443;
$certFile = __DIR__.'/ssl/cert.pem';
$keyFile = __DIR__.'/ssl/key.pem';

// Check if SSL files exist
if (! file_exists($certFile) || ! file_exists($keyFile)) {
    exit("SSL certificate files not found. Run the SSL setup first.\n");
}

echo "Starting HTTPS server on https://{$host}:{$port}\n";
echo 'Document root: '.__DIR__."/public\n";
echo "Press Ctrl+C to stop\n\n";

// Start PHP built-in server with HTTPS context
$context = stream_context_create([
    'ssl' => [
        'local_cert' => $certFile,
        'local_pk' => $keyFile,
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],
]);

// Create socket
$socket = stream_socket_server(
    "ssl://{$host}:{$port}",
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    $context
);

if (! $socket) {
    exit("Failed to create server: $errstr ($errno)\n");
}

echo "HTTPS server started successfully!\n";
echo "Access your site at: https://localhost:8443\n";
echo "Mining dashboard: https://localhost:8443/mining\n\n";

// Simple request handler
while ($connection = stream_socket_accept($socket)) {
    $request = stream_get_contents($connection);

    // Parse basic HTTP request
    $lines = explode("\n", $request);
    $firstLine = $lines[0] ?? '';
    preg_match('/^(\w+)\s+([^\s]+)\s+/', $firstLine, $matches);

    $method = $matches[1] ?? 'GET';
    $path = $matches[2] ?? '/';

    // Remove query string
    $path = strtok($path, '?');

    // Route to Laravel public directory
    $publicPath = __DIR__.'/public';

    if ($path === '/' || $path === '') {
        $path = '/index.php';
    }

    $fullPath = $publicPath.$path;

    // Security check
    if (strpos(realpath($fullPath), realpath($publicPath)) !== 0) {
        $response = "HTTP/1.1 403 Forbidden\r\n\r\n403 Forbidden";
    } elseif (file_exists($fullPath) && ! is_dir($fullPath)) {
        // Serve static file or PHP
        if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
            // Execute PHP file
            ob_start();
            $_SERVER['REQUEST_METHOD'] = $method;
            $_SERVER['REQUEST_URI'] = $path;
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_PORT'] = $port;
            $_SERVER['HTTP_HOST'] = 'localhost:'.$port;

            include $fullPath;
            $content = ob_get_clean();

            $response = "HTTP/1.1 200 OK\r\n";
            $response .= "Content-Type: text/html\r\n";
            $response .= 'Content-Length: '.strlen($content)."\r\n\r\n";
            $response .= $content;
        } else {
            // Serve static file
            $content = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

            $response = "HTTP/1.1 200 OK\r\n";
            $response .= "Content-Type: {$mimeType}\r\n";
            $response .= 'Content-Length: '.strlen($content)."\r\n\r\n";
            $response .= $content;
        }
    } else {
        // 404 Not Found - route to Laravel
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html\r\n\r\n";

        ob_start();
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = $port;
        $_SERVER['HTTP_HOST'] = 'localhost:'.$port;

        include $publicPath.'/index.php';
        $content = ob_get_clean();

        $response .= $content;
    }

    fwrite($connection, $response);
    fclose($connection);
}

fclose($socket);
