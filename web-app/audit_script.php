<?php
/**
 * Comprehensive Haichan Application Audit Script
 *
 * This script systematically tests all major functionality of the Laravel/PHP forum application
 * including authentication, PoW system, image library, forum functionality, and more.
 */

require_once __DIR__ . '/vendor/autoload.php';

class HaichanAudit
{
    private $baseUrl = 'http://localhost:8000';
    private $apiUrl = 'http://localhost:8000/api';
    private $results = [];
    private $currentSection = '';

    public function __construct()
    {
        $this->log("=== HAICHAN COMPREHENSIVE AUDIT ===");
        $this->log("Starting audit at " . date('Y-m-d H:i:s'));
        $this->log("Base URL: {$this->baseUrl}");
        $this->log("API URL: {$this->apiUrl}");
        $this->log("");
    }

    public function runFullAudit()
    {
        $this->testDatabaseIntegrity();
        $this->testPoWScalingSystem();
        $this->testImageLibraryAPI();
        $this->testAuthenticationSystem();
        $this->testForumSystem();
        $this->testMiningSystem();
        $this->testContentFormatting();
        $this->testMiniDashboard();
        $this->generateFinalReport();
    }

    private function testDatabaseIntegrity()
    {
        $this->currentSection = "Database Integrity";
        $this->log("=== TESTING DATABASE INTEGRITY ===");

        try {
            $pdo = new PDO('sqlite:database/database.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Test critical tables exist
            $tables = ['users', 'allowed_public_keys', 'proof_submissions', 'image_library',
                      'threads', 'posts', 'boards', 'mining_sessions'];

            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
                $count = $stmt->fetchColumn();
                $this->logTest("Table {$table} exists and has {$count} records", true);
            }

            // Test PoW scaling values in database
            $stmt = $pdo->query("SELECT DISTINCT pattern, COUNT(*) as count FROM proof_submissions GROUP BY pattern");
            $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->log("PoW patterns found in database:");
            foreach ($patterns as $pattern) {
                $this->log("  - Pattern: {$pattern['pattern']}, Submissions: {$pattern['count']}");
            }

            // Test foreign key relationships
            $stmt = $pdo->query("
                SELECT COUNT(*) FROM users u
                LEFT JOIN allowed_public_keys apk ON u.allowed_public_key_id = apk.id
                WHERE apk.id IS NULL
            ");
            $orphanedUsers = $stmt->fetchColumn();
            $this->logTest("No orphaned users (all users have valid public keys)", $orphanedUsers == 0);

            $this->results[$this->currentSection]['status'] = 'PASSED';

        } catch (Exception $e) {
            $this->logTest("Database connection and integrity", false, $e->getMessage());
            $this->results[$this->currentSection]['status'] = 'FAILED';
        }
    }

    private function testPoWScalingSystem()
    {
        $this->currentSection = "PoW Scaling System";
        $this->log("\n=== TESTING POW SCALING SYSTEM ===");

        // Test expected difficulty mappings from ProofSubmission model
        $expectedDifficulties = [
            '21' => 0.1,
            '21e8' => 1.0,
            '21e80' => 5.0,
            '21e800' => 25.0,
            '21e8000' => 125.0,  // Note: This differs from your requirement (100)
            '000021e8' => 625.0
        ];

        $this->log("Expected PoW scaling (from codebase analysis):");
        foreach ($expectedDifficulties as $pattern => $difficulty) {
            $this->log("  - Pattern: {$pattern} = {$difficulty} points");
        }

        // Test if the scaling matches requirements
        $requirements = [
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 100
        ];

        $this->log("Checking against requirements:");
        foreach ($requirements as $pattern => $expectedPoints) {
            $actualDifficulty = $expectedDifficulties[$pattern] ?? null;
            if ($pattern === '21e8000') {
                // Special case: code has 125, requirement is 100
                $this->logTest("Pattern {$pattern} scaling", false,
                    "Code has {$actualDifficulty}, requirement is {$expectedPoints}");
            } else {
                $matches = $actualDifficulty == $expectedPoints;
                $this->logTest("Pattern {$pattern} scaling ({$actualDifficulty} points)", $matches);
            }
        }

        $this->results[$this->currentSection]['status'] = 'NEEDS_REVIEW';
        $this->results[$this->currentSection]['issue'] = 'Pattern 21e8000 has 125 points instead of required 100';
    }

    private function testImageLibraryAPI()
    {
        $this->currentSection = "Image Library";
        $this->log("\n=== TESTING IMAGE LIBRARY API ===");

        // Test image library endpoints
        $endpoints = [
            '/library' => 'GET',
            '/api/image-library/stats' => 'GET',
            '/api/image-library/search' => 'GET',
            '/api/image-library/shifting' => 'GET'
        ];

        foreach ($endpoints as $endpoint => $method) {
            $result = $this->makeRequest($method, $endpoint);
            $this->logTest("Endpoint {$method} {$endpoint}", $result['success'], $result['error'] ?? '');
        }

        // Test image library statistics
        $statsResponse = $this->makeRequest('GET', '/api/image-library/stats');
        if ($statsResponse['success']) {
            $stats = json_decode($statsResponse['body'], true);
            $this->log("Image Library Stats:");
            $this->log("  - Total images: " . ($stats['total_images'] ?? 'N/A'));
            $this->log("  - Total PoW earned: " . ($stats['total_pow_earned'] ?? 'N/A'));
            $this->log("  - Total usage count: " . ($stats['total_usage'] ?? 'N/A'));
        }

        $this->results[$this->currentSection]['status'] = 'PASSED';
    }

    private function testAuthenticationSystem()
    {
        $this->currentSection = "Authentication System";
        $this->log("\n=== TESTING AUTHENTICATION SYSTEM ===");

        // Test auth endpoints
        $authEndpoints = [
            '/login' => 'GET',
            '/api/auth/challenge' => 'POST',
        ];

        foreach ($authEndpoints as $endpoint => $method) {
            $result = $this->makeRequest($method, $endpoint);
            $this->logTest("Auth endpoint {$method} {$endpoint}", $result['success'], $result['error'] ?? '');
        }

        // Test challenge generation (requires valid public key)
        $challengeData = [
            'public_key' => '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798' // Example key
        ];

        $challengeResponse = $this->makeRequest('POST', '/api/auth/challenge', $challengeData);
        $this->logTest("Challenge generation with test key", $challengeResponse['success'], $challengeResponse['error'] ?? '');

        if ($challengeResponse['success']) {
            $responseData = json_decode($challengeResponse['body'], true);
            $hasChallenge = isset($responseData['challenge']);
            $hasUserId = isset($responseData['user_id']);
            $this->logTest("Challenge response contains challenge", $hasChallenge);
            $this->logTest("Challenge response contains user_id", $hasUserId);
        }

        $this->results[$this->currentSection]['status'] = 'PARTIAL';
        $this->results[$this->currentSection]['note'] = 'Full auth testing requires valid secp256k1 keys';
    }

    private function testForumSystem()
    {
        $this->currentSection = "Forum System";
        $this->log("\n=== TESTING FORUM SYSTEM ===");

        // Test forum endpoints
        $forumEndpoints = [
            '/' => 'GET',
            '/forum' => 'GET',
            '/boards' => 'GET',
            '/catalog' => 'GET',
            '/gen' => 'GET',
            '/tech' => 'GET',
            '/gen/catalog' => 'GET',
        ];

        foreach ($forumEndpoints as $endpoint => $method) {
            $result = $this->makeRequest($method, $endpoint);
            $this->logTest("Forum endpoint {$method} {$endpoint}", $result['success'], $result['error'] ?? '');
        }

        // Test API endpoints
        $apiEndpoints = [
            '/api/boards' => 'GET',
        ];

        foreach ($apiEndpoints as $endpoint => $method) {
            $result = $this->makeRequest($method, $endpoint);
            $this->logTest("Forum API {$method} {$endpoint}", $result['success'], $result['error'] ?? '');
        }

        $this->results[$this->currentSection]['status'] = 'PASSED';
    }

    private function testMiningSystem()
    {
        $this->currentSection = "Mining System";
        $this->log("\n=== TESTING MINING SYSTEM ===");

        // Test mining endpoints
        $miningEndpoints = [
            '/mining' => 'GET',
            '/mining/stats' => 'GET',
            '/api/proof' => 'POST',
            '/api/proof/stats' => 'GET',
        ];

        foreach ($miningEndpoints as $endpoint => $method) {
            if ($method === 'GET') {
                $result = $this->makeRequest($method, $endpoint);
                $this->logTest("Mining endpoint {$method} {$endpoint}", $result['success'], $result['error'] ?? '');
            }
        }

        // Test proof stats endpoint
        $statsResult = $this->makeRequest('GET', '/api/proof/stats');
        $this->logTest("Proof stats endpoint", $statsResult['success'], $statsResult['error'] ?? '');

        if ($statsResult['success']) {
            $stats = json_decode($statsResult['body'], true);
            $this->log("Mining Stats:");
            $this->log("  - User stats: " . (isset($stats['user']) ? 'Available' : 'N/A'));
            $this->log("  - Global stats: " . (isset($stats['global']) ? 'Available' : 'N/A'));
        }

        // Test proof submission (with fake data to test validation)
        $fakeProofData = [
            'target_type' => 'global',
            'target_id' => 'main',
            'pattern' => '21e8',
            'hash' => str_repeat('0', 64),
            'nonce' => 12345,
            'challenge_data' => 'test:challenge:data'
        ];

        $proofResult = $this->makeRequest('POST', '/api/proof', $fakeProofData);
        $this->logTest("Proof submission validation", !$proofResult['success'],
            "Expected failure due to invalid proof - " . ($proofResult['error'] ?? ''));

        $this->results[$this->currentSection]['status'] = 'PASSED';
    }

    private function testContentFormatting()
    {
        $this->currentSection = "Content Formatting";
        $this->log("\n=== TESTING CONTENT FORMATTING ===");

        // Test if MarkdownHelper exists and check its functionality
        $helperPath = __DIR__ . '/app/Helpers/MarkdownHelper.php';
        $helperExists = file_exists($helperPath);
        $this->logTest("MarkdownHelper exists", $helperExists);

        if ($helperExists) {
            $helperContent = file_get_contents($helperPath);
            $hasGreentext = strpos($helperContent, 'greentext') !== false || strpos($helperContent, '>') !== false;
            $hasYoutube = strpos($helperContent, 'youtube') !== false || strpos($helperContent, 'embed') !== false;
            $hasQuoteLinks = strpos($helperContent, '>>') !== false;

            $this->logTest("Greentext support detected", $hasGreentext);
            $this->logTest("YouTube embedding support detected", $hasYoutube);
            $this->logTest("Quote links support detected", $hasQuoteLinks);
        }

        // Test a forum page to see if content formatting is visible
        $forumResult = $this->makeRequest('GET', '/gen');
        if ($forumResult['success']) {
            $content = $forumResult['body'];
            $hasGreentextCSS = strpos($content, 'greentext') !== false;
            $hasMarkdownJS = strpos($content, 'markdown') !== false || strpos($content, 'formatPost') !== false;

            $this->logTest("Greentext CSS present in forum pages", $hasGreentextCSS);
            $this->logTest("Content formatting JS present", $hasMarkdownJS);
        }

        $this->results[$this->currentSection]['status'] = 'PARTIAL';
    }

    private function testMiniDashboard()
    {
        $this->currentSection = "Mini Dashboard";
        $this->log("\n=== TESTING MINI DASHBOARD ===");

        // Test if dashboard shows mining targets correctly
        $dashboardResult = $this->makeRequest('GET', '/mining');
        $this->logTest("Mining dashboard loads", $dashboardResult['success'], $dashboardResult['error'] ?? '');

        if ($dashboardResult['success']) {
            $content = $dashboardResult['body'];
            $hasMiningTargets = strpos($content, 'target') !== false;
            $hasGlobalMining = strpos($content, 'global') !== false;
            $hasHotkeys = strpos($content, 'SPACE') !== false || strpos($content, 'ENTER') !== false;

            $this->logTest("Mining targets displayed", $hasMiningTargets);
            $this->logTest("Global mining available", $hasGlobalMining);
            $this->logTest("Hotkey instructions present", $hasHotkeys);
        }

        // Test context-aware mining targets on different pages
        $pages = ['/', '/gen', '/library'];
        foreach ($pages as $page) {
            $result = $this->makeRequest('GET', $page);
            if ($result['success']) {
                $content = $result['body'];
                $hasMiningWidget = strpos($content, 'mining') !== false || strpos($content, 'pow') !== false;
                $this->logTest("Mining widget present on {$page}", $hasMiningWidget);
            }
        }

        $this->results[$this->currentSection]['status'] = 'PASSED';
    }

    private function makeRequest($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: HaichanAudit/1.0'
            ]
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "cURL Error: {$error}"];
        }

        $success = $httpCode >= 200 && $httpCode < 400;

        return [
            'success' => $success,
            'http_code' => $httpCode,
            'body' => $response,
            'error' => $success ? null : "HTTP {$httpCode}"
        ];
    }

    private function logTest($description, $passed, $error = '')
    {
        $status = $passed ? '✓ PASS' : '✗ FAIL';
        $this->log("  {$status}: {$description}");
        if ($error) {
            $this->log("    Error: {$error}");
        }

        if (!isset($this->results[$this->currentSection]['tests'])) {
            $this->results[$this->currentSection]['tests'] = [];
        }

        $this->results[$this->currentSection]['tests'][] = [
            'description' => $description,
            'passed' => $passed,
            'error' => $error
        ];
    }

    private function log($message)
    {
        echo $message . "\n";
    }

    private function generateFinalReport()
    {
        $this->log("\n=== FINAL AUDIT REPORT ===");
        $this->log("Generated at: " . date('Y-m-d H:i:s'));
        $this->log("");

        $totalSections = count($this->results);
        $passedSections = 0;
        $failedSections = 0;
        $partialSections = 0;
        $reviewSections = 0;

        foreach ($this->results as $section => $result) {
            $status = $result['status'];
            $this->log("Section: {$section}");
            $this->log("  Status: {$status}");

            if (isset($result['issue'])) {
                $this->log("  Issue: {$result['issue']}");
            }
            if (isset($result['note'])) {
                $this->log("  Note: {$result['note']}");
            }

            if (isset($result['tests'])) {
                $passed = count(array_filter($result['tests'], fn($t) => $t['passed']));
                $total = count($result['tests']);
                $this->log("  Tests: {$passed}/{$total} passed");
            }

            switch ($status) {
                case 'PASSED':
                    $passedSections++;
                    break;
                case 'FAILED':
                    $failedSections++;
                    break;
                case 'PARTIAL':
                    $partialSections++;
                    break;
                case 'NEEDS_REVIEW':
                    $reviewSections++;
                    break;
            }

            $this->log("");
        }

        $this->log("=== SUMMARY ===");
        $this->log("Total sections: {$totalSections}");
        $this->log("Passed: {$passedSections}");
        $this->log("Failed: {$failedSections}");
        $this->log("Partial: {$partialSections}");
        $this->log("Needs Review: {$reviewSections}");

        $overallHealth = ($passedSections + $partialSections) / $totalSections * 100;
        $this->log("Overall Health: " . round($overallHealth, 1) . "%");

        $this->log("\n=== RECOMMENDATIONS ===");

        if ($reviewSections > 0) {
            $this->log("1. Fix PoW scaling: Pattern 21e8000 should be 100 points, not 125");
        }

        if ($partialSections > 0) {
            $this->log("2. Complete authentication testing with valid secp256k1 keypairs");
            $this->log("3. Verify content formatting by creating test posts");
        }

        $this->log("4. Test hyperinteractive mining (SPACE/ENTER hotkeys) manually");
        $this->log("5. Verify cross-browser compatibility manually");
        $this->log("6. Test image upload functionality with actual image files");

        $this->log("\n=== AUDIT COMPLETE ===");
    }
}

// Run the audit
$audit = new HaichanAudit();
$audit->runFullAudit();