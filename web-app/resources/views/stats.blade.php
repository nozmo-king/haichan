@extends('layout')

@section('title', 'Stats - Haichan')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header -->
<div style="background: #F5F5DC; padding: 30px; border-radius: 12px; border: 2px solid #708B75; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1); text-align: center;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 10px 0;">
        📊 Haichan Statistics
    </h1>
    <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
        Live metrics • Updated every minute • <span class="glow-text">Proof-of-Work</span> analytics
    </p>
</div>

<!-- Real-Time Stats -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🟢 Live Statistics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #00FF00; font-family: 'Courier New', monospace;">
                    {{ $stats['users_online'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Users Online</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">
                    {{ $stats['proofs_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Proofs Mined Today</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">
                    {{ $stats['posts_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Posts Today</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">
                    {{ $stats['threads_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Threads Today</div>
            </div>
        </div>
        
        <!-- Activity Chart -->
        <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
            <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px; text-align: center;">📈 7-Day Activity Trend</h4>
            <canvas id="activityChart" width="400" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            📚 Overall Statistics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Users</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['total_threads'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Threads</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #B87333; font-family: 'Courier New', monospace;">{{ number_format($stats['total_posts'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Posts</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">{{ number_format($stats['total_pow_points'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">PoW Points</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ $stats['total_boards'] ?? 0 }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Active Boards</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['posts_per_day_avg'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Posts/Day Avg</div>
            </div>
        </div>
    </div>
</div>

<!-- Mining Analytics -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #CD5C5C, #B87333); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            ⛏️ Mining Analytics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Proof Distribution -->
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
                <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px; text-align: center;">🎯 Difficulty Distribution</h4>
                <canvas id="difficultyChart" width="200" height="200"></canvas>
            </div>
            
            <!-- Mining Performance -->
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
                <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">📊 Mining Performance</h4>
                <div style="font-family: 'Courier New', monospace; font-size: 12px;">
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>Success Rate:</span>
                        <span style="color: #00FF00; font-weight: bold;">{{ number_format(($stats['proofs_today'] / max(1, $stats['posts_today'])) * 100, 1) }}%</span>
                    </div>
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>Avg Points/Proof:</span>
                        <span style="color: #9AB87A; font-weight: bold;">{{ number_format($stats['avg_points_per_proof'] ?? 0, 2) }}</span>
                    </div>
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>This Week:</span>
                        <span style="color: #708B75; font-weight: bold;">{{ $stats['posts_week'] ?? 0 }} posts</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Growth Rate:</span>
                        <span style="color: {{ ($stats['growth_rate'] ?? 0) > 0 ? '#00FF00' : '#FF6666' }}; font-weight: bold;">
                            {{ number_format($stats['growth_rate'] ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Contributors -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #B87333, #9AB87A); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🏆 Top Contributors (24h)
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="background: #FFFACD; border-radius: 8px; padding: 20px; border: 1px solid #708B75;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Top Miners -->
                <div>
                    <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">⛏️ Top Miners</h4>
                    @if(isset($stats['top_miners']) && count($stats['top_miners']) > 0)
                        @foreach($stats['top_miners'] as $index => $miner)
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                            <span style="color: #3D315B; font-size: 12px;">
                                #{{ $index + 1 }} {{ $miner->getDisplayName() }}
                            </span>
                            <span style="color: #9AB87A; font-weight: bold; font-size: 12px;">{{ $miner->daily_proofs }} proofs</span>
                        </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #6B7A6B; font-style: italic;">No data available</div>
                    @endif
                </div>
                
                <!-- Top Posters -->
                <div>
                    <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">💬 Top Posters</h4>
                    @if(isset($stats['top_posters']) && count($stats['top_posters']) > 0)
                        @foreach($stats['top_posters'] as $index => $poster)
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                            <span style="color: #3D315B; font-size: 12px;">
                                #{{ $index + 1 }} {{ $poster->getDisplayName() }}
                            </span>
                            <span style="color: #B87333; font-weight: bold; font-size: 12px;">{{ $poster->daily_posts }} posts</span>
                        </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #6B7A6B; font-style: italic;">No data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Mining Activity -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px;">
    <div style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🕐 Recent Mining Activity
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="background: #FFFACD; border-radius: 8px; padding: 20px; border: 1px solid #708B75;">
            @if(isset($stats['recent_mining']) && count($stats['recent_mining']) > 0)
                @foreach($stats['recent_mining'] as $activity)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                    <div style="flex-grow: 1;">
                        <div style="color: #3D315B; font-size: 13px; margin-bottom: 3px;">
                            {{ $activity['type'] === 'thread' ? '🧵' : '💬' }}
                            <strong>{{ Str::limit($activity['title'], 60) }}</strong>
                        </div>
                        <div style="color: #6B7A6B; font-size: 11px;">
                            /{{ $activity['board'] }}/ • {{ $activity['created_at']->diffForHumans() }}
                        </div>
                    </div>
                    <div style="background: #708B75; color: #F5F5DC; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                        ⚡{{ number_format($activity['points'], 1) }}
                    </div>
                </div>
                @endforeach
            @else
                <div style="text-align: center; color: #6B7A6B; font-style: italic; padding: 20px;">
                    No recent mining activity
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: @json(collect($stats['daily_stats'] ?? [])->pluck('date')),
        datasets: [
            {
                label: 'Threads',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('threads')),
                borderColor: '#708B75',
                backgroundColor: 'rgba(112, 139, 117, 0.1)',
                tension: 0.3
            },
            {
                label: 'Posts',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('posts')),
                borderColor: '#9AB87A',
                backgroundColor: 'rgba(154, 184, 122, 0.1)',
                tension: 0.3
            },
            {
                label: 'Proofs',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('proofs')),
                borderColor: '#CD5C5C',
                backgroundColor: 'rgba(205, 92, 92, 0.1)',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Difficulty Distribution Chart
const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
const difficultyChart = new Chart(difficultyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Easy (21)', 'Medium (21e)', 'Hard (21e8)', 'Extreme (21e88)'],
        datasets: [{
            data: [
                {{ $stats['difficulty_stats']['easy'] ?? 0 }},
                {{ $stats['difficulty_stats']['medium'] ?? 0 }},
                {{ $stats['difficulty_stats']['hard'] ?? 0 }},
                {{ $stats['difficulty_stats']['extreme'] ?? 0 }}
            ],
            backgroundColor: [
                '#9AB87A',
                '#708B75',
                '#CD5C5C',
                '#B87333'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

@endsection