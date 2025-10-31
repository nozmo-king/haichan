@extends('layout')

@section('title', 'Boards - Haichan')

@section('content')

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: #F5F5DC; border: 3px solid #9AB87A; border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: #9AB87A; margin: 0 0 10px 0;">
            📋 BOARD DIRECTORY
        </h1>
        <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
            Choose a board for anonymous discussion • Mining required to post
        </p>
    </div>

    <!-- Board Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        @foreach($boards as $board)
        <div style="background: #F5F5DC; border: 2px solid #9AB87A; border-radius: 8px; overflow: hidden; transition: all 0.3s; cursor: pointer;" 
             onclick="location.href='/{{ $board->code }}'"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)'"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            
            <!-- Board Header -->
            <div style="background: #9AB87A; color: white; padding: 20px; position: relative;">
                <div style="position: absolute; top: 10px; right: 15px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; border: 1px solid rgba(255,255,255,0.3);">
                    /{{ $board->code }}/
                </div>
                <h3 style="font-size: 20px; font-weight: bold; margin: 0 0 10px 0; color: white;">
                    {{ $board->name }}
                </h3>
                <p style="font-size: 13px; margin: 0; opacity: 0.95; line-height: 1.5; color: white;">
                    {{ $board->description }}
                </p>
            </div>
            
            <!-- Board Stats -->
            <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #9AB87A; margin-bottom: 5px;">
                        {{ $board->threads()->count() }}
                    </div>
                    <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                        Threads
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #9AB87A; margin-bottom: 5px;">
                        {{ $board->posts()->count() }}
                    </div>
                    <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                        Posts
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; margin: 0 auto 5px auto; background: {{ $board->is_active ? '#9AB87A' : '#999' }};"></div>
                    <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                        {{ $board->is_active ? 'Active' : 'Inactive' }}
                    </div>
                </div>
            </div>
            
            <!-- Board Actions -->
            <div style="padding: 15px 20px; border-top: 1px solid #D4E3C8; display: flex; gap: 10px;">
                <a href="/{{ $board->code }}" 
                   style="flex: 1; padding: 10px; background: #9AB87A; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; text-align: center; font-size: 13px; transition: all 0.3s;"
                   onmouseover="this.style.background='#8AA769'"
                   onmouseout="this.style.background='#9AB87A'">
                    View Board
                </a>
                <a href="/{{ $board->code }}/catalog" 
                   style="flex: 1; padding: 10px; background: #F5F5DC; color: #9AB87A; text-decoration: none; border-radius: 6px; font-weight: bold; text-align: center; font-size: 13px; border: 2px solid #9AB87A; transition: all 0.3s;"
                   onmouseover="this.style.background='#E5E5CC'"
                   onmouseout="this.style.background='#F5F5DC'">
                    Catalog
                </a>
            </div>
        </div>
        @endforeach
    </div>

    @if($boards->count() === 0)
    <div style="text-align: center; padding: 60px 20px; background: #F5F5DC; border: 2px solid #9AB87A; border-radius: 8px;">
        <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
        <h3 style="color: #6B7A6B; margin: 0 0 10px 0;">No Boards Available</h3>
        <p style="color: #6B7A6B; font-size: 13px; margin: 0;">
            Check back later or contact an administrator to create boards.
        </p>
    </div>
    @endif

</div>

@endsection
