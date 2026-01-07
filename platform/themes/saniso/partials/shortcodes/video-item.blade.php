@php
    $videoType = $video['type'] ?? 'upload';
    $videoSource = $video['source'] ?? '';
    $videoTitle = $video['title'] ?? '';
    $videoDescription = $video['description'] ?? '';
    $videoThumbnail = $video['thumbnail'] ?? '';
    $playMode = $video['play_mode'] ?? 'inline'; // 'inline' or 'modal'
    
    // Generate video embed HTML based on type
    $videoHtml = '';
    $videoHtmlAutoplay = '';
    $thumbnailUrl = '';
    
    if ($videoType === 'youtube') {
        // Extract YouTube video ID
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoSource, $matches);
        $youtubeId = $matches[1] ?? '';
        
        if ($youtubeId) {
            // Version without autoplay for initial load
            $videoHtml = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $youtubeId . '?rel=0&modestbranding=1&controls=1&showinfo=0&playsinline=1&iv_load_policy=3&disablekb=1&cc_load_policy=0" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
            // Version with autoplay for when user clicks play
            $videoHtmlAutoplay = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $youtubeId . '?autoplay=1&rel=0&modestbranding=1&controls=1&showinfo=0&playsinline=1&iv_load_policy=3&disablekb=1&cc_load_policy=0" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
            $thumbnailUrl = $videoThumbnail ?: "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg";
        }
    } elseif ($videoType === 'vimeo') {
        // Extract Vimeo video ID
        preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $videoSource, $matches);
        $vimeoId = $matches[3] ?? '';
        
        if ($vimeoId) {
            // Version without autoplay for initial load  
            $videoHtml = '<iframe width="100%" height="100%" src="https://player.vimeo.com/video/' . $vimeoId . '?muted=0&controls=1&title=0&byline=0&portrait=0" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
            // Version with autoplay for when user clicks play
            $videoHtmlAutoplay = '<iframe width="100%" height="100%" src="https://player.vimeo.com/video/' . $vimeoId . '?autoplay=1&muted=0&controls=1&title=0&byline=0&portrait=0" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
            $thumbnailUrl = $videoThumbnail ?: '';
        }
    } elseif ($videoType === 'external') {
        // External link
        $videoHtml = '<iframe width="100%" height="100%" src="' . htmlspecialchars($videoSource) . '" frameborder="0" allowfullscreen></iframe>';
        $videoHtmlAutoplay = $videoHtml; // Same for external
        $thumbnailUrl = $videoThumbnail ?: '';
    } else {
        // Upload/Local video - no autoplay initially
        $videoHtml = '<video width="100%" height="100%" controls muted><source src="' . htmlspecialchars($videoSource) . '" type="video/mp4">Your browser does not support the video tag.</video>';
        $videoHtmlAutoplay = '<video width="100%" height="100%" controls autoplay muted><source src="' . htmlspecialchars($videoSource) . '" type="video/mp4">Your browser does not support the video tag.</video>';
        $thumbnailUrl = $videoThumbnail ?: '';
    }
    
    // Fallback thumbnail
    if (!$thumbnailUrl) {
        $thumbnailUrl = asset('themes/saniso/images/video-placeholder.svg');
    }
    
    $uniqueId = 'video-item-' . uniqid();
    $videoHtmlEscaped = htmlspecialchars($videoHtml, ENT_QUOTES, 'UTF-8');
    $videoHtmlAutoplayEscaped = htmlspecialchars($videoHtmlAutoplay, ENT_QUOTES, 'UTF-8');
@endphp

<div class="video-item" id="{{ $uniqueId }}">
    <div class="video-thumbnail-wrapper">
        {{-- Video container for inline playback --}}
        <div class="video-player-container" style="display: none;">
            {!! $videoHtml !!}
            <button class="video-stop-button" onclick="stopVideo('{{ $uniqueId }}')" aria-label="Stop video">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        {{-- Thumbnail for initial display --}}
        <div class="video-thumbnail-container">
            @if($thumbnailUrl)
                <img class="video-thumbnail" 
                     src="{{ $thumbnailUrl }}" 
                     alt="{{ $videoTitle }}"
                     loading="lazy">
            @else
                <div class="video-thumbnail video-placeholder">
                    <div class="placeholder-content">
                        <i class="fas fa-play-circle" aria-hidden="true"></i>
                        <span>Video</span>
                    </div>
                </div>
            @endif
        </div>
        
        {{-- Play button overlay --}}
        <button class="video-play-button" 
                data-video-id="{{ $uniqueId }}"
                data-video-title="{{ $videoTitle }}"
                data-video-html="{{ base64_encode($videoHtmlAutoplay) }}"
                data-play-mode="{{ $playMode }}"
                onclick="playVideoFromButton(this)"
                aria-label="Play video: {{ $videoTitle }}">
            <i class="fas fa-play" aria-hidden="true"></i>
        </button>
        
        @if($videoType)
            <div class="video-type-badge">
                @switch($videoType)
                    @case('youtube')
                        <i class="fab fa-youtube" aria-hidden="true"></i>
                        @break
                    @case('vimeo')
                        <i class="fab fa-vimeo" aria-hidden="true"></i>
                        @break
                    @case('external')
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                        @break
                    @default
                        <i class="fas fa-video" aria-hidden="true"></i>
                @endswitch
            </div>
        @endif
    </div>
    
    @if($videoTitle || $videoDescription)
        <div class="video-content" style="text-align: {{ $textAlign ?? 'center' }}; color: {{ $textColor ?? '#333' }};">
            @if($videoTitle)
                <h4 class="video-title">{{ $videoTitle }}</h4>
            @endif
            
            @if($videoDescription)
                <div class="video-description">
                    {!! BaseHelper::clean($videoDescription) !!}
                </div>
            @endif
        </div>
    @endif
</div>
