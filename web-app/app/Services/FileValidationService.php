<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileValidationService
{
    /**
     * File type signatures (magic numbers) for security validation
     */
    private static array $fileSignatures = [
        // Images
        'jpeg' => [
            'FF D8 FF',
        ],
        'jpg' => [
            'FF D8 FF',
        ],
        'png' => [
            '89 50 4E 47 0D 0A 1A 0A',
        ],
        'gif' => [
            '47 49 46 38 37 61', // GIF87a
            '47 49 46 38 39 61', // GIF89a
        ],
        'webp' => [
            '52 49 46 46 ?? ?? ?? ?? 57 45 42 50', // RIFF...WEBP
        ],
        'bmp' => [
            '42 4D',
        ],
        'tiff' => [
            '49 49 2A 00', // Little endian
            '4D 4D 00 2A', // Big endian
        ],
        'svg' => [
            '3C 73 76 67', // <svg
            '3C 3F 78 6D 6C', // <?xml
        ],
        
        // Video formats
        'webm' => [
            '1A 45 DF A3',
        ],
        'mp4' => [
            '66 74 79 70', // ftyp (at offset 4)
        ],
        'mov' => [
            '66 74 79 70 71 74', // ftyp qt
        ],
        'avi' => [
            '52 49 46 46 ?? ?? ?? ?? 41 56 49 20', // RIFF...AVI
        ],
        
        // Modern formats
        'avif' => [
            '66 74 79 70 61 76 69 66', // ftyp avif
        ],
        'heic' => [
            '66 74 79 70 68 65 69 63', // ftyp heic
        ],
        'heif' => [
            '66 74 79 70 68 65 69 66', // ftyp heif
        ],
    ];

    /**
     * Maximum file sizes in bytes per file type
     */
    private static array $maxFileSizes = [
        // Images (10MB for most, 25MB for high-quality formats)
        'jpeg' => 10 * 1024 * 1024,
        'jpg' => 10 * 1024 * 1024,
        'png' => 15 * 1024 * 1024,
        'gif' => 20 * 1024 * 1024, // GIFs can be large due to animation
        'webp' => 10 * 1024 * 1024,
        'bmp' => 25 * 1024 * 1024, // BMPs are uncompressed
        'tiff' => 25 * 1024 * 1024,
        'svg' => 1 * 1024 * 1024, // SVGs should be small
        'avif' => 10 * 1024 * 1024,
        'heic' => 15 * 1024 * 1024,
        'heif' => 15 * 1024 * 1024,
        
        // Videos (25MB limit)
        'webm' => 25 * 1024 * 1024,
        'mp4' => 25 * 1024 * 1024,
        'mov' => 25 * 1024 * 1024,
        'avi' => 25 * 1024 * 1024,
    ];

    /**
     * Validate uploaded file with comprehensive security checks
     */
    public static function validateFile(UploadedFile $file): array
    {
        try {
            // Basic validation
            if (!$file->isValid()) {
                return [
                    'valid' => false,
                    'error' => 'File upload failed or corrupted'
                ];
            }

            // Get file extension and MIME type
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();

            // Check if file type is allowed
            if (!isset(self::$fileSignatures[$extension])) {
                return [
                    'valid' => false,
                    'error' => 'File type not allowed: ' . $extension
                ];
            }

            // Check file size limits
            $maxSize = self::$maxFileSizes[$extension] ?? (10 * 1024 * 1024);
            if ($fileSize > $maxSize) {
                return [
                    'valid' => false,
                    'error' => sprintf('File too large. Maximum size for %s files: %s', 
                        strtoupper($extension), 
                        self::formatBytes($maxSize))
                ];
            }

            // Validate MIME type matches extension
            if (!self::validateMimeType($extension, $mimeType)) {
                Log::warning('MIME type mismatch detected', [
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'filename' => $file->getClientOriginalName()
                ]);
                
                return [
                    'valid' => false,
                    'error' => 'File type validation failed: MIME type does not match extension'
                ];
            }

            // Validate file signature (magic numbers)
            $signatureValid = self::validateFileSignature($file, $extension);
            if (!$signatureValid) {
                Log::warning('File signature validation failed', [
                    'extension' => $extension,
                    'filename' => $file->getClientOriginalName()
                ]);
                
                return [
                    'valid' => false,
                    'error' => 'File signature validation failed: file may be corrupted or malicious'
                ];
            }

            // Additional security checks
            $securityCheck = self::performSecurityChecks($file, $extension);
            if (!$securityCheck['valid']) {
                return $securityCheck;
            }

            return [
                'valid' => true,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize
            ];

        } catch (\Exception $e) {
            Log::error('File validation error', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName()
            ]);
            
            return [
                'valid' => false,
                'error' => 'File validation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate file signature using magic numbers
     */
    private static function validateFileSignature(UploadedFile $file, string $extension): bool
    {
        $signatures = self::$fileSignatures[$extension] ?? [];
        if (empty($signatures)) {
            return false;
        }

        $fileHandle = fopen($file->getPathname(), 'rb');
        if (!$fileHandle) {
            return false;
        }

        // Read first 32 bytes for signature checking
        $headerBytes = fread($fileHandle, 32);
        fclose($fileHandle);

        if (!$headerBytes) {
            return false;
        }

        // Convert bytes to hex string
        $hexHeader = strtoupper(bin2hex($headerBytes));

        foreach ($signatures as $signature) {
            $signature = str_replace(' ', '', $signature);
            $signature = str_replace('??', '..', $signature); // Wildcard handling
            
            // Check if signature matches (allowing wildcards)
            if (self::matchesSignature($hexHeader, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match file header against signature with wildcard support
     */
    private static function matchesSignature(string $header, string $signature): bool
    {
        $sigLength = strlen($signature);
        if (strlen($header) < $sigLength) {
            return false;
        }

        for ($i = 0; $i < $sigLength; $i += 2) {
            $headerByte = substr($header, $i, 2);
            $sigByte = substr($signature, $i, 2);
            
            if ($sigByte !== '..' && $headerByte !== $sigByte) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate MIME type matches expected types for extension
     */
    private static function validateMimeType(string $extension, string $mimeType): bool
    {
        $validMimeTypes = [
            'jpeg' => ['image/jpeg'],
            'jpg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'bmp' => ['image/bmp', 'image/x-bmp'],
            'tiff' => ['image/tiff', 'image/tif'],
            'svg' => ['image/svg+xml', 'text/xml', 'application/xml'],
            'avif' => ['image/avif'],
            'heic' => ['image/heic'],
            'heif' => ['image/heif'],
            'webm' => ['video/webm'],
            'mp4' => ['video/mp4'],
            'mov' => ['video/quicktime'],
            'avi' => ['video/avi', 'video/msvideo', 'video/x-msvideo'],
        ];

        $allowedMimes = $validMimeTypes[$extension] ?? [];
        return in_array($mimeType, $allowedMimes);
    }

    /**
     * Perform additional security checks
     */
    private static function performSecurityChecks(UploadedFile $file, string $extension): array
    {
        // Check for suspicious filename patterns
        $filename = $file->getClientOriginalName();
        
        // Prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return [
                'valid' => false,
                'error' => 'Invalid filename: directory traversal detected'
            ];
        }

        // Check for executable extensions hidden in filename
        $suspiciousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'phar', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'htm'];
        foreach ($suspiciousExtensions as $suspExt) {
            if (stripos($filename, '.' . $suspExt) !== false) {
                return [
                    'valid' => false,
                    'error' => 'Security violation: suspicious file extension detected'
                ];
            }
        }

        // SVG-specific security checks
        if ($extension === 'svg') {
            return self::validateSvgSecurity($file);
        }

        return ['valid' => true];
    }

    /**
     * Validate SVG files for security (prevent XSS)
     */
    private static function validateSvgSecurity(UploadedFile $file): array
    {
        $content = file_get_contents($file->getPathname());
        if (!$content) {
            return [
                'valid' => false,
                'error' => 'Cannot read SVG file content'
            ];
        }

        // Check for dangerous SVG elements/attributes
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
            '/<embed/i',
            '/<object/i',
            '/<iframe/i',
            '/<link/i',
            '/<meta/i',
            '/xlink:href\s*=\s*["\']javascript:/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return [
                    'valid' => false,
                    'error' => 'SVG security violation: dangerous content detected'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Format bytes to human readable string
     */
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . 'MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . 'KB';
        } else {
            return $bytes . 'B';
        }
    }
}