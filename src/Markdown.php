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
        return $this->parsedown->text($text);
    }
}