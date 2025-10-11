<?php

namespace App\Helpers;

class GeoIP
{
    private static $ipToCountry = [
        // Common IP ranges for demo purposes
        // US ranges
        '8.' => 'US',
        '15.' => 'US',
        '24.' => 'US',
        '35.' => 'US',
        '52.' => 'US',
        '54.' => 'US',
        '72.' => 'US',
        '98.' => 'US',
        '173.' => 'US',
        '192.168.' => 'LAN', // Private network
        '10.' => 'LAN',
        '172.16.' => 'LAN',
        '127.' => 'LO', // Localhost
        
        // International ranges (simplified)
        '2.' => 'FR',
        '5.' => 'DE',
        '31.' => 'NL',
        '37.' => 'RU',
        '41.' => 'ZA',
        '43.' => 'JP',
        '46.' => 'RU',
        '47.' => 'NO',
        '51.' => 'UK',
        '58.' => 'CN',
        '61.' => 'AU',
        '62.' => 'ES',
        '77.' => 'RU',
        '78.' => 'CZ',
        '79.' => 'IT',
        '80.' => 'UK',
        '81.' => 'DE',
        '82.' => 'NL',
        '83.' => 'PL',
        '84.' => 'TR',
        '85.' => 'SE',
        '86.' => 'RO',
        '87.' => 'BE',
        '88.' => 'FR',
        '89.' => 'UK',
        '91.' => 'RU',
        '92.' => 'UK',
        '93.' => 'DE',
        '94.' => 'GR',
        '95.' => 'RU',
        '103.' => 'KR',
        '106.' => 'JP',
        '109.' => 'RU',
        '110.' => 'AU',
        '111.' => 'JP',
        '112.' => 'CN',
        '113.' => 'CN',
        '114.' => 'CN',
        '115.' => 'KR',
        '116.' => 'KR',
        '117.' => 'CN',
        '118.' => 'JP',
        '119.' => 'CN',
        '120.' => 'CN',
        '121.' => 'KR',
        '122.' => 'CN',
        '123.' => 'CN',
        '124.' => 'CN',
        '125.' => 'JP',
        '134.' => 'US',
        '138.' => 'BR',
        '139.' => 'AU',
        '140.' => 'US',
        '141.' => 'US',
        '142.' => 'US',
        '143.' => 'CA',
        '144.' => 'US',
        '145.' => 'IT',
        '146.' => 'IT',
        '147.' => 'US',
        '148.' => 'US',
        '149.' => 'US',
        '150.' => 'AU',
        '151.' => 'US',
        '152.' => 'US',
        '153.' => 'JP',
        '154.' => 'CA',
        '155.' => 'US',
        '156.' => 'US',
        '157.' => 'JP',
        '158.' => 'US',
        '159.' => 'US',
        '160.' => 'TW',
        '161.' => 'US',
        '162.' => 'US',
        '163.' => 'US',
        '164.' => 'US',
        '165.' => 'US',
        '166.' => 'US',
        '167.' => 'US',
        '168.' => 'US',
        '169.' => 'US',
        '170.' => 'US',
        '171.' => 'US',
        '172.' => 'US',
        '174.' => 'CA',
        '175.' => 'KR',
        '176.' => 'EU',
        '177.' => 'BR',
        '178.' => 'DE',
        '179.' => 'BR',
        '180.' => 'BR',
        '181.' => 'BR',
        '182.' => 'AU',
        '183.' => 'CN',
        '184.' => 'US',
        '185.' => 'EU',
        '186.' => 'BR',
        '187.' => 'BR',
        '188.' => 'EU',
        '189.' => 'BR',
        '190.' => 'AR',
        '191.' => 'BR',
        '192.' => 'US',
        '193.' => 'EU',
        '194.' => 'EU',
        '195.' => 'EU',
        '196.' => 'CA',
        '197.' => 'US',
        '198.' => 'US',
        '199.' => 'US',
        '200.' => 'BR',
        '201.' => 'BR',
        '202.' => 'AU',
        '203.' => 'AU',
        '204.' => 'CA',
        '205.' => 'CA',
        '206.' => 'CA',
        '207.' => 'CA',
        '208.' => 'CA',
        '209.' => 'US',
        '210.' => 'JP',
        '211.' => 'KR',
        '212.' => 'EU',
        '213.' => 'EU',
        '214.' => 'US',
        '215.' => 'US',
        '216.' => 'US',
        '217.' => 'EU',
        '218.' => 'CN',
        '219.' => 'JP',
        '220.' => 'AU',
        '221.' => 'CN',
        '222.' => 'CN',
        '223.' => 'AU',
    ];
    
    private static $countryFlags = [
        'US' => '🇺🇸',
        'UK' => '🇬🇧',
        'GB' => '🇬🇧',
        'CA' => '🇨🇦',
        'AU' => '🇦🇺',
        'NZ' => '🇳🇿',
        'JP' => '🇯🇵',
        'CN' => '🇨🇳',
        'KR' => '🇰🇷',
        'IN' => '🇮🇳',
        'DE' => '🇩🇪',
        'FR' => '🇫🇷',
        'IT' => '🇮🇹',
        'ES' => '🇪🇸',
        'NL' => '🇳🇱',
        'BE' => '🇧🇪',
        'CH' => '🇨🇭',
        'SE' => '🇸🇪',
        'NO' => '🇳🇴',
        'DK' => '🇩🇰',
        'FI' => '🇫🇮',
        'PL' => '🇵🇱',
        'RU' => '🇷🇺',
        'UA' => '🇺🇦',
        'CZ' => '🇨🇿',
        'SK' => '🇸🇰',
        'HU' => '🇭🇺',
        'RO' => '🇷🇴',
        'BG' => '🇧🇬',
        'GR' => '🇬🇷',
        'TR' => '🇹🇷',
        'IL' => '🇮🇱',
        'SA' => '🇸🇦',
        'AE' => '🇦🇪',
        'EG' => '🇪🇬',
        'ZA' => '🇿🇦',
        'NG' => '🇳🇬',
        'KE' => '🇰🇪',
        'BR' => '🇧🇷',
        'AR' => '🇦🇷',
        'MX' => '🇲🇽',
        'CL' => '🇨🇱',
        'CO' => '🇨🇴',
        'PE' => '🇵🇪',
        'VE' => '🇻🇪',
        'TW' => '🇹🇼',
        'TH' => '🇹🇭',
        'VN' => '🇻🇳',
        'PH' => '🇵🇭',
        'ID' => '🇮🇩',
        'MY' => '🇲🇾',
        'SG' => '🇸🇬',
        'HK' => '🇭🇰',
        'PK' => '🇵🇰',
        'BD' => '🇧🇩',
        'LAN' => '🏠', // Local network
        'LO' => '💻', // Localhost
        'EU' => '🇪🇺', // Europe general
        'TOR' => '🧅', // Tor network
        'VPN' => '🔒', // VPN detected
        'UNK' => '🌍', // Unknown
    ];
    
    /**
     * Get country code from IP address
     */
    public static function getCountryFromIP($ip)
    {
        // Check for localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'LO';
        }
        
        // Check for private networks
        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip)) {
            return 'LAN';
        }
        
        // Check Tor exit nodes (simplified check)
        if (strpos($ip, '.tor.') !== false) {
            return 'TOR';
        }
        
        // Simple prefix matching
        foreach (self::$ipToCountry as $prefix => $country) {
            if (strpos($ip, $prefix) === 0) {
                return $country;
            }
        }
        
        // Random assignment for demo purposes
        $countries = ['US', 'UK', 'CA', 'AU', 'DE', 'FR', 'JP', 'BR', 'RU', 'CN', 'IN', 'MX'];
        $hash = crc32($ip);
        return $countries[$hash % count($countries)];
    }
    
    /**
     * Get flag emoji for country code
     */
    public static function getFlagEmoji($countryCode)
    {
        return self::$countryFlags[$countryCode] ?? self::$countryFlags['UNK'];
    }
    
    /**
     * Get flag emoji from IP address
     */
    public static function getFlagFromIP($ip)
    {
        $country = self::getCountryFromIP($ip);
        return self::getFlagEmoji($country);
    }
    
    /**
     * Format IP with flag for display
     */
    public static function formatIPWithFlag($ip, $boardCode = null)
    {
        // Only show flags on /pol/ board
        if ($boardCode !== 'pol') {
            return '';
        }
        
        $flag = self::getFlagFromIP($ip);
        $country = self::getCountryFromIP($ip);
        
        // Hash the IP for privacy
        $hashedIP = substr(hash('sha256', $ip . 'haichan-salt'), 0, 8);
        
        return sprintf(
            '<span class="ip-flag" title="%s - ID: %s">%s</span>',
            $country,
            $hashedIP,
            $flag
        );
    }
}