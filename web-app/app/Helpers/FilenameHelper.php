<?php

namespace App\Helpers;

class FilenameHelper
{
    /**
     * Generate a randomized filename with various patterns
     */
    public static function randomizeFilename(string $originalName, ?string $style = null): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        switch ($style) {
            case 'aesthetic':
                return self::generateAestheticName() . $extension;
            
            case 'technical':
                return self::generateTechnicalName() . $extension;
            
            case 'mystical':
                return self::generateMysticalName() . $extension;
                
            case 'minimal':
                return self::generateMinimalName() . $extension;
                
            case 'chaos':
                return self::generateChaosName() . $extension;
                
            default:
                return self::generateRandomPattern() . $extension;
        }
    }
    
    /**
     * Generate aesthetic filename with vaporwave/synthwave vibes
     */
    private static function generateAestheticName(): string
    {
        $prefixes = ['neon', 'cyber', 'retro', 'vapor', 'synth', 'dream', 'glow', 'pixel'];
        $suffixes = ['wave', 'core', 'punk', 'grid', 'mind', 'soul', 'vibe', 'flow'];
        $numbers = [80, 85, 88, 90, 95, 99, 2000, 2001, 2049];
        
        $patterns = [
            $prefixes[array_rand($prefixes)] . $suffixes[array_rand($suffixes)] . $numbers[array_rand($numbers)],
            strtoupper($prefixes[array_rand($prefixes)]) . '_' . $numbers[array_rand($numbers)],
            $prefixes[array_rand($prefixes)] . '-' . $suffixes[array_rand($suffixes)] . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
        ];
        
        return $patterns[array_rand($patterns)];
    }
    
    /**
     * Generate technical filename with system-like patterns
     */
    private static function generateTechnicalName(): string
    {
        $systems = ['sys', 'proc', 'kern', 'dev', 'tmp', 'usr', 'var', 'opt'];
        $operations = ['init', 'exec', 'load', 'dump', 'sync', 'fork', 'kill', 'wait'];
        
        $patterns = [
            $systems[array_rand($systems)] . '_' . $operations[array_rand($operations)] . '_' . time(),
            strtoupper($systems[array_rand($systems)]) . sprintf('%04X', rand(0, 65535)),
            $operations[array_rand($operations)] . '.' . rand(100, 999) . '.' . rand(0, 99),
            'img_' . date('Ymd') . '_' . sprintf('%06d', rand(0, 999999)),
        ];
        
        return $patterns[array_rand($patterns)];
    }
    
    /**
     * Generate mystical filename with arcane vibes
     */
    private static function generateMysticalName(): string
    {
        $elements = ['void', 'star', 'moon', 'sun', 'abyss', 'nexus', 'rift', 'orb'];
        $qualities = ['dark', 'bright', 'ancient', 'forbidden', 'hidden', 'sacred', 'cursed', 'blessed'];
        $runes = ['algiz', 'ansuz', 'berkana', 'dagaz', 'ehwaz', 'fehu', 'gebo', 'hagalaz'];
        
        $patterns = [
            $qualities[array_rand($qualities)] . '_' . $elements[array_rand($elements)],
            $runes[array_rand($runes)] . '_' . sprintf('%03d', rand(1, 999)),
            $elements[array_rand($elements)] . 'of' . $qualities[array_rand($qualities)],
        ];
        
        return $patterns[array_rand($patterns)];
    }
    
    /**
     * Generate minimal filename with clean patterns
     */
    private static function generateMinimalName(): string
    {
        $patterns = [
            sprintf('%08x', rand()),
            date('ymdHis') . sprintf('%02d', rand(0, 99)),
            sprintf('%c%c%d', rand(97, 122), rand(97, 122), rand(100, 999)),
            str_repeat(chr(rand(97, 122)), rand(3, 6)) . rand(10, 99),
        ];
        
        return $patterns[array_rand($patterns)];
    }
    
    /**
     * Generate chaotic filename with random patterns
     */
    private static function generateChaosName(): string
    {
        $chaos = '';
        $length = rand(8, 16);
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        
        for ($i = 0; $i < $length; $i++) {
            if (rand(0, 10) === 0) {
                $chaos .= ['!', '@', '#', '$', '%', '^', '&'][array_rand(['!', '@', '#', '$', '%', '^', '&'])];
            } else {
                $chaos .= $chars[rand(0, strlen($chars) - 1)];
            }
        }
        
        return $chaos;
    }
    
    /**
     * Generate random pattern from various styles
     */
    private static function generateRandomPattern(): string
    {
        $styles = ['aesthetic', 'technical', 'mystical', 'minimal', 'chaos'];
        $style = $styles[array_rand($styles)];
        
        switch ($style) {
            case 'aesthetic': return self::generateAestheticName();
            case 'technical': return self::generateTechnicalName();
            case 'mystical': return self::generateMysticalName();
            case 'minimal': return self::generateMinimalName();
            case 'chaos': return self::generateChaosName();
        }
    }
    
    /**
     * Generate secure filename that's filesystem safe
     */
    public static function generateSecureFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $hash = substr(hash('sha256', $originalName . $timestamp), 0, 8);
        
        return "file_{$timestamp}_{$random}_{$hash}{$extension}";
    }
}