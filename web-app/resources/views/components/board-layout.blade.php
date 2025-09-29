@props(['board' => null, 'title' => null, 'description' => null, 'showNav' => true])

<x-board-header :board="$board" :title="$title" :description="$description" :showNav="$showNav" />

<div class="tui-window" style="margin: 20px auto; max-width: 900px;">
    @if($errors->any())
    <div style="background: #FFE6E6; border: 1px solid #FF9999; padding: 15px; margin: 20px; border-radius: 5px;">
        @foreach($errors->all() as $error)
            <p style="color: #CC0000; margin: 5px 0; font-size: 12px;">• {{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="tui-p">
        {{ $slot }}
    </div>
</div>