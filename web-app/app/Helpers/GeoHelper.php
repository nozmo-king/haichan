<?php

namespace App\Helpers;

class GeoHelper
{
    /**
     * Get country flag emoji from IP address
     */
    public static function getCountryFlag($ip)
    {
        // Validate IP address first
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return '🌍';
        }
        
        // Real IP geolocation using free service (HTTPS for security)
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'HaichanApp/1.0'
                ]
            ]);
            $geoData = @file_get_contents("https://ip-api.com/json/{$ip}?fields=countryCode", false, $context);
            if ($geoData) {
                $data = json_decode($geoData, true);
                $countryCode = $data['countryCode'] ?? null;

                if ($countryCode && preg_match('/^[A-Z]{2}$/', $countryCode)) {
                    return self::getEmojiFlag($countryCode);
                }
            }
        } catch (\Exception $e) {
            // Fallback for localhost/private IPs
            if (in_array($ip, ['127.0.0.1', '::1']) ||
                preg_match('/^192\.168\./', $ip) ||
                preg_match('/^10\./', $ip) ||
                preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $ip)) {
                return '🏳️'; // Local flag
            }
        }

        return '🌍'; // Default world flag
    }

    /**
     * Convert country code to flag emoji
     */
    private static function getEmojiFlag($countryCode)
    {
        $flags = [
            'US' => '🇺🇸', 'CA' => '🇨🇦', 'GB' => '🇬🇧', 'FR' => '🇫🇷', 'DE' => '🇩🇪',
            'JP' => '🇯🇵', 'KR' => '🇰🇷', 'CN' => '🇨🇳', 'RU' => '🇷🇺', 'IN' => '🇮🇳',
            'BR' => '🇧🇷', 'MX' => '🇲🇽', 'AR' => '🇦🇷', 'AU' => '🇦🇺', 'NZ' => '🇳🇿',
            'IT' => '🇮🇹', 'ES' => '🇪🇸', 'NL' => '🇳🇱', 'BE' => '🇧🇪', 'CH' => '🇨🇭',
            'AT' => '🇦🇹', 'SE' => '🇸🇪', 'NO' => '🇳🇴', 'DK' => '🇩🇰', 'FI' => '🇫🇮',
            'PL' => '🇵🇱', 'CZ' => '🇨🇿', 'HU' => '🇭🇺', 'RO' => '🇷🇴', 'BG' => '🇧🇬',
            'GR' => '🇬🇷', 'TR' => '🇹🇷', 'IL' => '🇮🇱', 'SA' => '🇸🇦', 'AE' => '🇦🇪',
            'EG' => '🇪🇬', 'ZA' => '🇿🇦', 'NG' => '🇳🇬', 'KE' => '🇰🇪', 'GH' => '🇬🇭',
            'TH' => '🇹🇭', 'VN' => '🇻🇳', 'PH' => '🇵🇭', 'ID' => '🇮🇩', 'MY' => '🇲🇾',
            'SG' => '🇸🇬', 'HK' => '🇭🇰', 'TW' => '🇹🇼', 'UA' => '🇺🇦', 'BY' => '🇧🇾',
            'LT' => '🇱🇹', 'LV' => '🇱🇻', 'EE' => '🇪🇪', 'IS' => '🇮🇸', 'IE' => '🇮🇪',
            'PT' => '🇵🇹', 'LU' => '🇱🇺', 'MT' => '🇲🇹', 'CY' => '🇨🇾', 'HR' => '🇭🇷',
            'SI' => '🇸🇮', 'SK' => '🇸🇰', 'RS' => '🇷🇸', 'BA' => '🇧🇦', 'MK' => '🇲🇰',
            'AL' => '🇦🇱', 'ME' => '🇲🇪', 'MD' => '🇲🇩', 'AM' => '🇦🇲', 'GE' => '🇬🇪',
            'AZ' => '🇦🇿', 'KZ' => '🇰🇿', 'UZ' => '🇺🇿', 'KG' => '🇰🇬', 'TJ' => '🇹🇯',
            'TM' => '🇹🇲', 'AF' => '🇦🇫', 'PK' => '🇵🇰', 'BD' => '🇧🇩', 'LK' => '🇱🇰',
            'MV' => '🇲🇻', 'BT' => '🇧🇹', 'NP' => '🇳🇵', 'MM' => '🇲🇲', 'LA' => '🇱🇦',
            'KH' => '🇰🇭', 'BN' => '🇧🇳', 'MN' => '🇲🇳', 'IR' => '🇮🇷', 'IQ' => '🇮🇶',
            'SY' => '🇸🇾', 'LB' => '🇱🇧', 'JO' => '🇯🇴', 'PS' => '🇵🇸', 'KW' => '🇰🇼',
            'QA' => '🇶🇦', 'BH' => '🇧🇭', 'OM' => '🇴🇲', 'YE' => '🇾🇪', 'CL' => '🇨🇱',
            'PE' => '🇵🇪', 'BO' => '🇧🇴', 'PY' => '🇵🇾', 'UY' => '🇺🇾', 'EC' => '🇪🇨',
            'CO' => '🇨🇴', 'VE' => '🇻🇪', 'GY' => '🇬🇾', 'SR' => '🇸🇷', 'GF' => '🇬🇫',
        ];

        return $flags[$countryCode] ?? '🏁';
    }

    /**
     * Get country name from code
     */
    public static function getCountryName($countryCode)
    {
        $countries = [
            'US' => 'United States', 'CA' => 'Canada', 'GB' => 'United Kingdom',
            'FR' => 'France', 'DE' => 'Germany', 'JP' => 'Japan', 'KR' => 'South Korea',
            'CN' => 'China', 'RU' => 'Russia', 'IN' => 'India', 'BR' => 'Brazil',
            'MX' => 'Mexico', 'AR' => 'Argentina', 'AU' => 'Australia', 'NZ' => 'New Zealand',
        ];

        return $countries[$countryCode] ?? 'Unknown';
    }
}
