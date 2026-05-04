<?php

namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsExtension extends AbstractExtension
{
    public function __construct(private Assets $assets) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'path']),
        ];
    }

    public function path(string $p): string
    {
        return $this->assets->path($p);
    }
}
