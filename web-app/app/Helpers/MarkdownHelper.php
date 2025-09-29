<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function parseContent($content)
    {
        // Escape HTML first
        $content = htmlspecialchars($content);

        // Replace newlines with <br> tags
        $content = nl2br($content);

        // Apply greentext formatting (lines starting with >)
        $content = preg_replace('/^&gt;(.+)(<br\s*\/?>)?/m', '<span class="greentext">&gt;$1</span>$2', $content);

        // Quote links (>>123)
        $content = preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', $content);

        // YouTube link embedding (decode URLs first)
        $content = html_entity_decode($content);
        $content = self::embedYouTubeLinks($content);

        return $content;
    }

    /**
     * Embed YouTube videos from links
     */
    private static function embedYouTubeLinks($html)
    {
        // Match YouTube URLs and extract video IDs
        $patterns = [
            '/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/https?:\/\/(?:www\.)?youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/https?:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            $html = preg_replace_callback($pattern, function ($matches) {
                $videoId = $matches[1];
                $originalUrl = $matches[0];

                // Create embedded video player
                return '<div class="youtube-embed" style="margin: 15px 0; max-width: 560px;">
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                        <iframe
                            src="https://www.youtube.com/embed/'.$videoId.'?rel=0&showinfo=0&modestbranding=1"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                        <a href="'.$originalUrl.'" target="_blank" style="color: #708B75;">🎥 '.$originalUrl.'</a>
                    </div>
                </div>';
            }, $html);
        }

        return $html;
    }
}
