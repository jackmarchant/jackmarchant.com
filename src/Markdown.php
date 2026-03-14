<?php

namespace App;

use Parsedown;

class Markdown implements MarkdownParserInterface
{
    /** @var Parsedown */
    private $parsedown;

    public function __construct()
    {
        $this->parsedown = new Parsedown();
    }

    /**
     * Parses markdown text into html string
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        $html = $this->parsedown->text($text);
        $html = $this->embedTweets($html);
        return $html;
    }

    /**
     * Replaces standalone tweet URLs wrapped in <p> tags with Twitter embed HTML
     *
     * @param string $html
     * @return string
     */
    private function embedTweets(string $html): string
    {
        // Parsedown converts bare URLs to <p><a href="url">url</a></p>
        return preg_replace_callback(
            '/<p><a href="(https?:\/\/(?:twitter\.com|x\.com)\/[^\/]+\/status\/\d+[^"]*)">[^<]*<\/a><\/p>/',
            function ($matches) {
                $url = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
                return '<blockquote class="twitter-tweet" data-dnt="true"><a href="' . $url . '"></a></blockquote>';
            },
            $html
        );
    }
}