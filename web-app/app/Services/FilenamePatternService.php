<?php

namespace App\Services;

/**
 * FilenamePatternService
 * 
 * Handles secure filename pattern generation and validation for uploaded files.
 * Focuses on preventing directory traversal and ensuring clean, safe filenames.
 */
class FilenamePatternService
{
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif',
        'webm', 'mp4', 'mov', 'avi', 'svg'
    ];

    private const MAX_FILENAME_LENGTH = 100;
    private const HASH_LENGTH = 8;

    /**
     * Generate a secure filename with hash prefix and original extension
     */
    public function generateSecureFilename(string $originalFilename): string
    {
        $extension = $this->extractExtension($originalFilename);
        $hash = $this->generateHash();
        
        return $hash . '.' . $extension;
    }

    /**
     * Generate filename with timestamp and hash for uniqueness
     */
    public function generateTimestampedFilename(string $originalFilename): string
    {
        $extension = $this->extractExtension($originalFilename);
        $timestamp = date('Y-m-d_H-i-s');
        $hash = $this->generateHash();
        
        return $timestamp . '_' . $hash . '.' . $extension;
    }

    /**
     * Validate that a filename is safe and allowed
     */
    public function validateFilename(string $filename): bool
    {
        // Check length
        if (strlen($filename) > self::MAX_FILENAME_LENGTH) {
            return false;
        }

        // Check for directory traversal attempts
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }

        // Check for null bytes and control characters
        if (preg_match('/[\x00-\x1f\x7f]/', $filename)) {
            return false;
        }

        // Validate extension
        $extension = $this->extractExtension($filename);
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Sanitize a filename by removing dangerous characters
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove directory separators and null bytes
        $filename = str_replace(['/', '\\', "\0"], '', $filename);
        
        // Remove control characters
        $filename = preg_replace('/[\x00-\x1f\x7f]/', '', $filename);
        
        // Replace multiple dots with single dot
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        // Limit length
        if (strlen($filename) > self::MAX_FILENAME_LENGTH) {
            $extension = $this->extractExtension($filename);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);
            $maxBaseLength = self::MAX_FILENAME_LENGTH - strlen($extension) - 1;
            $baseName = substr($baseName, 0, $maxBaseLength);
            $filename = $baseName . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Generate a content-based filename using file hash
     */
    public function generateContentBasedFilename(string $fileContent, string $originalFilename): string
    {
        $extension = $this->extractExtension($originalFilename);
        $contentHash = hash('sha256', $fileContent);
        $shortHash = substr($contentHash, 0, 16);
        
        return $shortHash . '.' . $extension;
    }

    /**
     * Get file type category based on extension
     */
    public function getFileTypeCategory(string $filename): string
    {
        $extension = strtolower($this->extractExtension($filename));
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'svg'];
        $videoExtensions = ['webm', 'mp4', 'mov', 'avi'];
        
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        }
        
        if (in_array($extension, $videoExtensions)) {
            return 'video';
        }
        
        return 'unknown';
    }

    /**
     * Extract file extension from filename
     */
    private function extractExtension(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }

    /**
     * Generate a secure random hash
     */
    private function generateHash(): string
    {
        $bytes = random_bytes(self::HASH_LENGTH);
        return bin2hex($bytes);
    }

    /**
     * Check if extension is in allowed list
     */
    public function isAllowedExtension(string $extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Get list of allowed extensions
     */
    public function getAllowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    public function getThemedFilenameWithExtension(string $originalFilename): string
    {
        $extension = $this->extractExtension($originalFilename);
        $theme = ['cosmic', 'mythic', 'neon', 'archaic', 'cyber'];
        $randomTheme = $theme[array_rand($theme)];
        $hash = $this->generateHash();
        
        return $randomTheme . '_' . $hash . '.' . $extension;
    }

    /**
     * Create a backup filename if original exists
     */
    public function createUniqueFilename(string $baseFilename, string $directory): string
    {
        $originalPath = $directory . DIRECTORY_SEPARATOR . $baseFilename;
        
        if (!file_exists($originalPath)) {
            return $baseFilename;
        }

        $extension = $this->extractExtension($baseFilename);
        $basename = pathinfo($baseFilename, PATHINFO_FILENAME);
        
        $counter = 1;
        do {
            $newFilename = $basename . '_' . $counter . '.' . $extension;
            $newPath = $directory . DIRECTORY_SEPARATOR . $newFilename;
            $counter++;
        } while (file_exists($newPath) && $counter < 1000);
        
        return $newFilename;
    }
}