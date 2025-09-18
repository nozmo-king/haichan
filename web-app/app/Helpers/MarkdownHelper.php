<?php

namespace App\Helpers;

use League\CommonMark\CommonMarkConverter;

class MarkdownHelper
{
    public static function parseContent($content)
    {
        // Create converter with safe configuration
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        $converter = new CommonMarkConverter($config);

        // Parse markdown
        $markdown = $converter->convert($content);
        $html = $markdown->getContent();

        // Apply imageboard-style formatting on top of markdown
        // Quote links (>>123)
        $html = preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', $html);

        // Greentext (lines starting with >)
        $html = preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', $html);

        return $html;
    }
}