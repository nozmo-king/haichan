@php
    $isSystem = $message->is_system;
    $rarityColor = $message->rarity_color;
    $username = $message->display_name;
    $powPoints = $message->pow_points;
    $rarityLevel = $message->rarity_level;
    $hashPreview = substr($message->pow_hash, 0, 8);
    $canDelete = $message->canUserDelete(auth()->user());
@endphp

<div class="chat-message" data-message-id="{{ $message->id }}" 
     style="margin-bottom: 12px; {{ $isSystem ? 'text-align: center; font-style: italic;' : '' }}">
    
    @if($isSystem)
        <!-- System Message -->
        <div style="color: #666; font-size: 11px; padding: 4px 8px; background: #f0f0f0; border-radius: 4px; display: inline-block;">
            🤖 {{ $message->message }}
        </div>
    @else
        <!-- User Message -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Username with rarity color -->
                <span style="font-weight: bold; color: {{ $rarityColor }};">
                    {{ $username }}
                </span>
                
                <!-- Timestamp -->
                <span style="font-size: 10px; color: var(--ib-text-muted);">
                    {{ $message->created_at->format('H:i:s') }}
                </span>
                
                <!-- PoW Badge -->
                <span style="font-size: 9px; background: {{ $rarityColor }}; color: white; padding: 2px 6px; border-radius: 8px; font-weight: bold;">
                    {{ $rarityLevel }} (+{{ $powPoints }}⚡)
                </span>
                
                <!-- Hash Preview -->
                <span style="font-size: 8px; color: var(--ib-text-muted); font-family: monospace;" 
                      title="{{ $message->pow_hash }}">
                    {{ $hashPreview }}...
                </span>
            </div>

            @if($canDelete)
            <!-- Delete Button -->
            <button onclick="deleteMessage({{ $message->id }})" 
                    style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 12px; opacity: 0.7;"
                    title="Delete message">
                🗑️
            </button>
            @endif
        </div>

        <!-- Message Content -->
        <div style="padding: 6px 8px; border-left: 3px solid {{ $rarityColor }}; background: {{ $isSystem ? 'transparent' : 'rgba(255,255,255,0.5)' }}; border-radius: 0 4px 4px 0;">
            <div style="color: var(--ib-text); word-wrap: break-word;">
                {!! $message->formatted_message !!}
            </div>
        </div>

        @if($message->isRarePattern())
        <!-- Special Effects for Rare Patterns -->
        <div style="text-align: center; margin-top: 4px;">
            <span style="font-size: 8px; color: {{ $rarityColor }}; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                ✨ {{ $rarityLevel }} PATTERN FOUND ✨
            </span>
        </div>
        @endif
    @endif
</div>