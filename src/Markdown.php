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
     * Replaces standalone tweet URLs wrapped in <p> tags with a styled tweet card
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
                $xLogoSvg = '<svg class="tweet-card__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true">'
                    . '<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>'
                    . '</svg>';
                return '<div class="tweet-card">'
                    . $xLogoSvg
                    . '<a class="tweet-card__link" href="' . $url . '" target="_blank" rel="noopener noreferrer">View on X</a>'
                    . '</div>';
            },
            $html
        );
    }
}