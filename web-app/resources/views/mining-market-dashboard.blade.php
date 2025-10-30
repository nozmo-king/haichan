@extends('layout')

@section('title', 'Haichan - Live Mining Market')

@section('content')

<!-- Magic Numbers Cascade -->
<div class="magic-numbers-stream" style="position: fixed; top: 0; right: 20px; width: 160px; height: 100vh; pointer-events: none; z-index: 1; overflow: hidden;">
    <div class="number-cascade" id="numbers-cascade" style="font-family: 'Courier New', monospace; font-size: 7px; color: rgba(112, 139, 117, 0.12); line-height: 1.1; white-space: pre;">
        <!-- Numbers will be populated by JavaScript -->
    </div>
</div>

<!-- Enhanced Navigation -->
<div class="haichan-nav" style="position: sticky; top: 0; background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); border-bottom: 1px solid #708B75; padding: 12px 0; z-index: 100; backdrop-filter: blur(2px);">
    <div class="nav-container" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
        <div class="nav-primary" style="display: flex; gap: 8px;">
            <a href="{{ route('forum.index') }}" class="nav-item active">🏠 Market</a>
            <a href="{{ route('mining.dashboard') }}" class="nav-item">⛏️ Range</a>
            <a href="/chat" class="nav-item">💬 Chat</a>
            <a href="/shop" class="nav-item">🛒 Shop</a>
            <a href="{{ route('bounty') }}" class="nav-item">🏆 Bounty</a>
        </div>
        <div class="nav-mining-summary" id="mini-mining-display" style="font-family: 'Courier New', monospace; font-size: 10px; color: #708B75; display: flex; align-items: center; gap: 15px;">
            <span class="mining-status-compact" id="nav-status">⚡ 0 H/s</span>
            <span class="mining-heartbeat" id="nav-heartbeat">💚</span>
        </div>
    </div>
</div>

<!-- Market Hero Section -->
<div class="market-hero" style="background: linear-gradient(135deg, #F5F5DC 0%, #FFFACD 50%, #F0F8FF 100%); border: 2px solid #708B75; border-radius: 12px; padding: 30px 20px; text-align: center; margin: 20px auto; max-width: 1200px; position: relative; overflow: hidden;">
    
    <!-- Market Status Indicator -->
    <div class="market-status" style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 8px; font-size: 11px; color: #708B75;">
        <div class="status-dot" id="market-status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #28A745; animation: gentle-pulse 2s ease-in-out infinite;"></div>
        <span>MARKET LIVE</span>
    </div>

    <!-- Main Market Display -->
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 8px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.05);">
        ⚡ MINING MARKET
    </h1>
    <p style="color: #6B7A6B; font-size: 14px; max-width: 500px; margin: 0 auto 25px auto;">
        Live proof-of-work trading floor • Hyperconsistent • Hyperpersistent • Secure
    </p>

    <!-- Real-time Market Metrics -->
    <div class="market-ticker" style="display: flex; justify-content: center; gap: 25px; flex-wrap: wrap; margin-bottom: 20px; font-family: 'Courier New', monospace;">
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="global-hashrate" style="font-size: 18px; font-weight: bold; color: #D4AF37;">0</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">H/sec Global</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="magic-discovered" style="font-size: 18px; font-weight: bold; color: #DC3545;">0</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">21e8 Found</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="active-miners" style="font-size: 18px; font-weight: bold; color: #28A745;">{{ $activeSessions }}</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">Miners</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="market-cap" style="font-size: 18px; font-weight: bold; color: #6F42C1;">{{ number_format(\App\Models\ProofOfWork::sum('points')) }}</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">Points</div>
        </div>
    </div>

    <!-- Mining Difficulty Weather -->
    <div class="difficulty-weather" style="background: rgba(255,255,255,0.3); border-radius: 6px; padding: 12px; margin-top: 20px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; font-size: 12px;">
            <span>Difficulty Weather:</span>
            <div class="weather-display" id="mining-weather" style="display: flex; align-items: center; gap: 8px;">
                <span class="weather-icon">⛅</span>
                <span class="weather-text" style="font-family: 'Courier New', monospace; color: #708B75;">Moderate</span>
            </div>
            <span>|</span>
            <span>Next Storm: <span id="storm-countdown" style="font-family: 'Courier New', monospace; color: #D4AF37;">--:--</span></span>
        </div>
    </div>
</div>
