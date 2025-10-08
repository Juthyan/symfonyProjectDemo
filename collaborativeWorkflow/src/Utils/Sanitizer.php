<?php

namespace App\Utils;

final class Sanitizer
{
    private function __construct() {}

    /**
     * Sanitize a generic text field (e.g., descriptions, comments).
     */
    public static function sanitizeText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = trim($text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return $text;
    }
}
