<?php

namespace App\Support;

final class StorefrontRichText
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><strike><h2><h3><h4><ul><ol><li><a><blockquote><hr>';

    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '' || $html === '<p><br></p>' || $html === '<p></p>') {
            return null;
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace('/<a\s+([^>]*href\s*=\s*["\']?)javascript:[^"\']*["\']?/i', '<a ', $clean) ?? $clean;
        $clean = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean) ?? $clean;

        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    public static function render(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        $trimmed = trim($content);

        if (preg_match('/<[^>]+>/', $trimmed)) {
            return self::sanitize($trimmed) ?? '';
        }

        return nl2br(e($trimmed), false);
    }
}
