<?php
/**
 * Static site generator for jackmarchant.com
 *
 * Reads markdown content, renders HTML via Twig, and writes static files
 * to the dist/ directory for deployment on Netlify (or any static host).
 *
 * Usage: php build.php
 */

error_reporting(E_ALL ^ E_DEPRECATED);
require __DIR__ . '/vendor/autoload.php';

use App\Markdown;
use App\Services\PostService;

$dist = __DIR__ . '/dist';

// ── helpers ────────────────────────────────────────────────────────

function ensureDir(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function copyDir(string $src, string $dst): void
{
    ensureDir($dst);
    foreach (scandir($src) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $srcPath = $src . '/' . $item;
        $dstPath = $dst . '/' . $item;
        if (is_dir($srcPath)) {
            copyDir($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
}

// ── bootstrap services ─────────────────────────────────────────────

$siteUrl = 'https://www.jackmarchant.com';

$markdown    = new Markdown();
$postService = new PostService($markdown);

// index.twig is a multi-purpose template that conditionally renders
// the blog list (when 'posts' is set) or a single post (when 'post' is set).
$loader   = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig     = new Twig\Environment($loader);
$settings = [
    'environment' => 'production',
];

// ── clean dist directory ───────────────────────────────────────────

if (is_dir($dist)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dist, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dist);
}
ensureDir($dist);

// ── copy static assets from public/ ────────────────────────────────

$publicDir = __DIR__ . '/public';
foreach (scandir($publicDir) as $item) {
    if ($item === '.' || $item === '..' || $item === 'index.php' || $item === '.htaccess') {
        continue;
    }
    $srcPath = $publicDir . '/' . $item;
    $dstPath = $dist . '/' . $item;
    if (is_dir($srcPath)) {
        copyDir($srcPath, $dstPath);
    } else {
        copy($srcPath, $dstPath);
    }
}

// ── build blog list (home page) ────────────────────────────────────

$posts = $postService->getAllPostListings();
$tags  = $postService->getAllTags($posts);

$indexHtml = $twig->render('index.twig', [
    'posts'    => $posts,
    'tags'     => $tags,
    'settings' => $settings,
]);
file_put_contents($dist . '/index.html', $indexHtml);
echo "  ✓ /index.html\n";

// ── build individual post pages ────────────────────────────────────

$contentDir = __DIR__ . '/content';
$folders    = array_filter(glob($contentDir . '/*'), 'is_dir');

foreach ($folders as $folder) {
    $slug = basename($folder);
    $post = $postService->findPostByPath($slug);

    if (empty($post)) {
        echo "  ✗ skipped /{$slug} (could not parse)\n";
        continue;
    }

    $adjacent = $postService->findAdjacentPosts($slug);

    $html = $twig->render('index.twig', [
        'post'     => $post,
        'newer'    => $adjacent['newer'],
        'older'    => $adjacent['older'],
        'settings' => $settings,
    ]);

    $postDir = $dist . '/' . $slug;
    ensureDir($postDir);
    file_put_contents($postDir . '/index.html', $html);
    echo "  ✓ /{$slug}/index.html\n";
}

// ── build 404 page ─────────────────────────────────────────────────

$notFoundHtml = $twig->render('index.twig', [
    'post'     => ['title' => 'Page not found'],
    'settings' => $settings,
]);
file_put_contents($dist . '/404.html', $notFoundHtml);
echo "  ✓ /404.html\n";

// ── generate sitemap.xml ───────────────────────────────────────────

$sitemapLines = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    '<url>',
    '  <loc>' . $siteUrl . '/</loc>',
    '  <priority>1.00</priority>',
    '</url>',
];

foreach ($posts as $post) {
    $sitemapLines[] = '<url>';
    $sitemapLines[] = '  <loc>' . htmlspecialchars($siteUrl . $post['url'], ENT_XML1, 'UTF-8') . '</loc>';
    $sitemapLines[] = '  <priority>0.80</priority>';
    $sitemapLines[] = '</url>';
}

$sitemapLines[] = '</urlset>';
file_put_contents($dist . '/sitemap.xml', implode("\n", $sitemapLines) . "\n");
echo "  ✓ /sitemap.xml (generated)\n";

// ── generate rss.xml ───────────────────────────────────────────────

$rssLines = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">',
    '<channel>',
    '  <title>jack marchant</title>',
    '  <link>' . $siteUrl . '/</link>',
    '  <description>writing about software</description>',
    '  <language>en</language>',
    '  <atom:link href="' . $siteUrl . '/rss.xml" rel="self" type="application/rss+xml" />',
];

foreach ($posts as $timestamp => $listing) {
    $slug = ltrim($listing['url'], '/');
    $full = $postService->findPostByPath($slug);
    if (empty($full)) {
        continue;
    }
    $itemUrl = $siteUrl . $listing['url'];
    $rssLines[] = '<item>';
    $rssLines[] = '  <title>' . htmlspecialchars($listing['title'], ENT_XML1, 'UTF-8') . '</title>';
    $rssLines[] = '  <link>' . htmlspecialchars($itemUrl, ENT_XML1, 'UTF-8') . '</link>';
    $rssLines[] = '  <guid isPermaLink="true">' . htmlspecialchars($itemUrl, ENT_XML1, 'UTF-8') . '</guid>';
    $rssLines[] = '  <pubDate>' . date(DATE_RSS, $timestamp) . '</pubDate>';
    $rssLines[] = '  <description>' . htmlspecialchars(trim(strip_tags($full['blurb'])), ENT_XML1, 'UTF-8') . '</description>';
    $rssLines[] = '  <content:encoded><![CDATA[' . $full['content'] . ']]></content:encoded>';
    $rssLines[] = '</item>';
}

$rssLines[] = '</channel>';
$rssLines[] = '</rss>';
file_put_contents($dist . '/rss.xml', implode("\n", $rssLines) . "\n");
echo "  ✓ /rss.xml (generated)\n";

echo "\nBuild complete → {$dist}\n";
