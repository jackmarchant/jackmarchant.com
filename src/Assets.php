<?php

namespace App;

class Assets
{
    private string $publicDir;
    private array $cache = [];

    public function __construct(string $publicDir)
    {
        $this->publicDir = rtrim($publicDir, '/');
    }

    public function path(string $path): string
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $file = $this->publicDir . $path;
        if (!is_file($file)) {
            return $this->cache[$path] = $path;
        }

        $hash = substr(md5_file($file), 0, 8);
        $info = pathinfo($path);
        $dir = $info['dirname'] === '/' ? '' : $info['dirname'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

        return $this->cache[$path] = $dir . '/' . $info['filename'] . '.' . $hash . $ext;
    }
}
