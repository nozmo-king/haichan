@extends('layout')

@section('title', 'Haichan - Boards')

@section('content')
<!-- Header -->
<div style="background: #F5F5DC; padding: 30px; border-radius: 12px; border: 2px solid #708B75; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1); text-align: center;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 10px 0;">
        📋 Board Directory
    </h1>
    <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
        Choose a board for <span class="glow-text">anonymous</span> discussion • Mining required to post
    </p>
</div>

<!-- Boards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    @foreach($boards as $board)
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1); overflow: hidden; transition: all 0.3s ease; cursor: pointer;" 
         onclick="location.href='{{ $board->url }}'" 
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(112, 139, 117, 0.2)'"
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(112, 139, 117, 0.1)'">
        
        <!-- Board Header -->
        <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #F5F5DC; padding: 20px; position: relative;">
            <div style="position: absolute; top: 15px; right: 20px; background: rgba(245, 245, 220, 0.2); color: #F5F5DC; padding: 4px 8px; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; border-radius: 4px; border: 1px solid rgba(245, 245, 220, 0.3);">
                /{{ $board->code }}/
            </div>
            
            <h3 style="font-family: 'Nova Cut', serif; font-size: 20px; margin: 0 0 8px 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                {{ $board->title }}
            </h3>
            <p style="font-size: 13px; margin: 0; opacity: 0.9; line-height: 1.4;">
                {{ $board->description }}
            </p>
        </div>
        
        <!-- Board Stats -->
        <div style="background: #FFFACD; padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; text-align: center;">
                <div>
                    <div style="font-size: 20px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">
                        {{ number_format($board->threads_count) }}
                    </div>
                    <div style="font-size: 10px; color: #6B7A6B; text-transform: uppercase; margin-top: 2px;">
                        Threads
                    </div>
                </div>
                
                <div>
                    <div style="font-size: 20px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">
                        {{ number_format($board->post_count ?? 0) }}
                    </div>
                    <div style="font-size: 10px; color: #6B7A6B; text-transform: uppercase; margin-top: 2px;">
                        Posts
                    </div>
                </div>
                
                <div>
                    <div style="font-size: 20px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">
                        {{ $board->is_active ? '🟢' : '🔴' }}
                    </div>
                    <div style="font-size: 10px; color: #6B7A6B; text-transform: uppercase; margin-top: 2px;">
                        Status
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <a href="{{ $board->url }}" 
                   style="flex: 1; background: linear-gradient(135deg, #708B75, #9AB87A); color: #F5F5DC; text-decoration: none; padding: 10px; border-radius: 6px; text-align: center; font-size: 12px; font-weight: bold; transition: all 0.3s ease; display: block;">
                    📋 View Board
                </a>
                <a href="{{ $board->url }}/catalog" 
                   style="flex: 1; background: #FFFACD; color: #708B75; text-decoration: none; padding: 10px; border-radius: 6px; text-align: center; font-size: 12px; font-weight: bold; border: 1px solid #708B75; transition: all 0.3s ease; display: block;">
                    📑 Catalog
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if(count($boards) === 0)
<div style="background: #F5F5DC; padding: 40px; border-radius: 12px; border: 2px solid #708B75; text-align: center; margin-top: 30px;">
    <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
    <h3 style="color: #708B75; margin: 0 0 10px 0; font-family: 'Nova Cut', serif;">No Boards Available</h3>
    <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
        Check back later or contact an administrator to create boards.
    </p>
</div>
@endif

@endsection
