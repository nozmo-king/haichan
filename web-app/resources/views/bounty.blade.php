@extends('layout')

@section('title', 'Code & Bug Bounty Program')

@section('content')
<div class="hero">
    <h1 class="hero-title">
        🏆 HAICHAN BUG BOUNTY
    </h1>
    <p class="hero-subtitle">
        Help us improve Haichan's proof-of-work imageboard system. Find bugs, suggest improvements, contribute code - earn rewards!
    </p>
</div>

<div class="grid grid-cols-3 gap-md mb-lg" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    
    <!-- Security Bugs -->
    <x-card>
        <x-slot name="header">
            <div class="flex align-center gap-sm">
                <span class="text-xl">🔒</span>
                <h3 class="card-title text-error m-0">Security Vulnerabilities</h3>
            </div>
        </x-slot>
        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
            <li style="padding: 5px 0; color: #666;">• SQL Injection - <strong>$200-500</strong></li>
            <li style="padding: 5px 0; color: #666;">• XSS/CSRF - <strong>$100-300</strong></li>
            <li style="padding: 5px 0; color: #666;">• Authentication Bypass - <strong>$300-600</strong></li>
            <li style="padding: 5px 0; color: #666;">• File Upload Exploits - <strong>$150-400</strong></li>
            <li style="padding: 5px 0; color: #666;">• PoW Mining Exploits - <strong>$400-800</strong></li>
        </ul>
        <div style="background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 4px;">
            <small style="color: #DC3545; font-weight: bold;">🚨 Critical security issues get priority review</small>
        </div>
    </div>

    <!-- System Bugs -->
    <div class="bounty-category" style="background: #FFF; border: 2px solid #FFC107; border-radius: 8px; padding: 20px;">
        <div class="category-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
            <span style="font-size: 24px;">🐛</span>
            <h3 style="color: #FFC107; margin: 0;">System & Logic Bugs</h3>
        </div>
        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
            <li style="padding: 5px 0; color: #666;">• Mining Dashboard Issues - <strong>$50-150</strong></li>
            <li style="padding: 5px 0; color: #666;">• Thread/Post Creation Bugs - <strong>$75-200</strong></li>
            <li style="padding: 5px 0; color: #666;">• UI/UX Glitches - <strong>$25-100</strong></li>
            <li style="padding: 5px 0; color: #666;">• Performance Issues - <strong>$100-250</strong></li>
            <li style="padding: 5px 0; color: #666;">• Data Inconsistencies - <strong>$50-150</strong></li>
        </ul>
        <div style="background: rgba(255, 193, 7, 0.1); padding: 10px; border-radius: 4px;">
            <small style="color: #FFC107; font-weight: bold;">⚡ Reproducible bugs with clear steps preferred</small>
        </div>
    </div>

    <!-- Feature Contributions -->
    <div class="bounty-category" style="background: #FFF; border: 2px solid #28A745; border-radius: 8px; padding: 20px;">
        <div class="category-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
            <span style="font-size: 24px;">⚡</span>
            <h3 style="color: #28A745; margin: 0;">Code Contributions</h3>
        </div>
        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
            <li style="padding: 5px 0; color: #666;">• Mining Algorithm Improvements - <strong>$200-500</strong></li>
            <li style="padding: 5px 0; color: #666;">• New Board Features - <strong>$100-300</strong></li>
            <li style="padding: 5px 0; color: #666;">• API Enhancements - <strong>$75-200</strong></li>
            <li style="padding: 5px 0; color: #666;">• Mobile Optimizations - <strong>$100-250</strong></li>
            <li style="padding: 5px 0; color: #666;">• Performance Optimizations - <strong>$150-400</strong></li>
        </ul>
        <div style="background: rgba(40, 167, 69, 0.1); padding: 10px; border-radius: 4px;">
            <small style="color: #28A745; font-weight: bold;">💡 Quality PRs with tests get bonus rewards</small>
        </div>
    </div>

</div>

<!-- Submission Guidelines -->
<div class="guidelines-section" style="background: #F8F9FA; border: 1px solid #DEE2E6; border-radius: 8px; padding: 30px; margin-bottom: 30px;">
    <h2 style="color: #495057; margin: 0 0 20px 0; font-size: 24px;">📋 Submission Guidelines</h2>
    
    <div class="guidelines-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        
        <div class="guideline-item">
            <h4 style="color: #6C757D; margin: 0 0 10px 0;">🔍 Bug Reports</h4>
            <ul style="margin: 0; padding-left: 20px; color: #495057;">
                <li>Clear reproduction steps</li>
                <li>Screenshots/videos if applicable</li>
                <li>Browser/system information</li>
                <li>Expected vs actual behavior</li>
            </ul>
        </div>

        <div class="guideline-item">
            <h4 style="color: #6C757D; margin: 0 0 10px 0;">💻 Code Contributions</h4>
            <ul style="margin: 0; padding-left: 20px; color: #495057;">
                <li>Follow existing code style</li>
                <li>Include relevant tests</li>
                <li>Document new features</li>
                <li>Submit via GitHub pull request</li>
            </ul>
        </div>

        <div class="guideline-item">
            <h4 style="color: #6C757D; margin: 0 0 10px 0;">⚖️ Terms & Conditions</h4>
            <ul style="margin: 0; padding-left: 20px; color: #495057;">
                <li>First valid submission wins</li>
                <li>Duplicates not eligible</li>
                <li>No automated testing/scanning</li>
                <li>Public disclosure after fix</li>
            </ul>
        </div>

        <div class="guideline-item">
            <h4 style="color: #6C757D; margin: 0 0 10px 0;">💰 Payment Methods</h4>
            <ul style="margin: 0; padding-left: 20px; color: #495057;">
                <li>Bitcoin (preferred)</li>
                <li>Ethereum</li>
                <li>PayPal</li>
                <li>GitHub Sponsors</li>
            </ul>
        </div>

    </div>
</div>

<!-- Active Bounties -->
<div class="active-bounties" style="background: #FFF; border: 2px solid #6F42C1; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
    <h2 style="color: #6F42C1; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
        <span>🎯</span> Priority Targets (Double Rewards!)
    </h2>
    
    <div class="priority-list" style="background: rgba(111, 66, 193, 0.05); border-radius: 6px; padding: 20px;">
        <div class="priority-item" style="border-bottom: 1px dashed #6F42C1; padding: 15px 0; margin-bottom: 15px;">
            <h4 style="color: #6F42C1; margin: 0 0 8px 0;">⛏️ Mining System Race Conditions</h4>
            <p style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">
                The mining toolbar sometimes shows zeros instead of real stats. Related to timing between mining system initialization and UI updates.
            </p>
            <div style="display: flex; gap: 15px; font-size: 12px;">
                <span style="background: #6F42C1; color: white; padding: 2px 8px; border-radius: 12px;">🏆 $300</span>
                <span style="color: #6C757D;">Status: Active</span>
                <span style="color: #6C757D;">Difficulty: Medium</span>
            </div>
        </div>

        <div class="priority-item" style="border-bottom: 1px dashed #6F42C1; padding: 15px 0; margin-bottom: 15px;">
            <h4 style="color: #6F42C1; margin: 0 0 8px 0;">🎨 Mobile UI Responsiveness</h4>
            <p style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">
                Several pages don't render properly on mobile devices. Mining dashboard, thread creation forms, and board catalogs need optimization.
            </p>
            <div style="display: flex; gap: 15px; font-size: 12px;">
                <span style="background: #6F42C1; color: white; padding: 2px 8px; border-radius: 12px;">🏆 $400</span>
                <span style="color: #6C757D;">Status: Active</span>
                <span style="color: #6C757D;">Difficulty: Easy-Medium</span>
            </div>
        </div>

        <div class="priority-item" style="padding: 15px 0;">
            <h4 style="color: #6F42C1; margin: 0 0 8px 0;">🔐 21e8 PoW Security Review</h4>
            <p style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">
                Comprehensive security audit of our custom 21e8 proof-of-work implementation. Looking for cryptographic weaknesses or implementation flaws.
            </p>
            <div style="display: flex; gap: 15px; font-size: 12px;">
                <span style="background: #6F42C1; color: white; padding: 2px 8px; border-radius: 12px;">🏆 $1000</span>
                <span style="color: #6C757D;">Status: Active</span>
                <span style="color: #6C757D;">Difficulty: Hard</span>
            </div>
        </div>
    </div>
</div>

<!-- Contact & Submit -->
<div class="contact-section" style="background: linear-gradient(135deg, #708B75, #9AB87A); color: white; border-radius: 8px; padding: 30px; text-align: center;">
    <h2 style="margin: 0 0 20px 0; font-size: 28px;">🚀 Ready to Contribute?</h2>
    <p style="margin: 0 0 25px 0; font-size: 16px; opacity: 0.9;">
        Submit bugs via GitHub issues or email. Code contributions via pull requests.
    </p>
    
    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
        <a href="https://github.com/haichan-project/issues" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            📝 Report Bug
        </a>
        <a href="https://github.com/haichan-project/pulls" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            💻 Submit Code
        </a>
        <a href="mailto:bounty@haichan.org" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            📧 Contact Us
        </a>
    </div>
    
    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); font-size: 14px; opacity: 0.8;">
        <p style="margin: 0;">
            🏆 Total Bounties Paid: <strong>$12,430</strong> | 
            🔍 Active Hunters: <strong>47</strong> | 
            🐛 Bugs Fixed: <strong>156</strong>
        </p>
    </div>
</div>

@endsection