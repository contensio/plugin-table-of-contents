<?php

namespace Contensio\Plugins\TableOfContents\Support;

class HeadingExtractor
{
    /**
     * Extract all H2 and H3 headings from a Contensio blocks array.
     *
     * Returns an array of items in document order:
     *   ['level' => 'h2'|'h3', 'text' => string, 'id' => string]
     *
     * Sources:
     * - `heading` blocks with level h2 or h3
     * - `<h2>` / `<h3>` tags embedded in `richtext` block HTML
     *
     * @param  array   $blocks  Content::$blocks (already cast to array)
     * @param  int|null $langId  Language ID for translatable fields
     */
    public static function extract(array $blocks, ?int $langId): array
    {
        $headings = [];
        $seen     = [];

        foreach ($blocks as $block) {
            if (empty($block['is_active'])) {
                continue;
            }

            $type  = $block['type'] ?? '';
            $data  = $block['data'] ?? [];
            $trans = self::translation($block, $langId);

            if ($type === 'heading') {
                $level = strtolower($data['level'] ?? 'h2');
                if (! in_array($level, ['h2', 'h3'], true)) {
                    continue;
                }
                $text = strip_tags($trans['text'] ?? '');
                if ($text === '') {
                    continue;
                }
                $headings[] = [
                    'level' => $level,
                    'text'  => $text,
                    'id'    => self::slug($text, $seen),
                ];
            }

            if ($type === 'richtext') {
                $html = $trans['content'] ?? '';
                if ($html === '') {
                    continue;
                }
                foreach (self::extractFromHtml($html, $seen) as $item) {
                    $headings[] = $item;
                }
            }
        }

        return $headings;
    }

    /**
     * Scan a richtext HTML string for <h2> and <h3> tags and return heading items.
     */
    private static function extractFromHtml(string $html, array &$seen): array
    {
        $headings = [];
        if (preg_match_all('/<(h[23])[^>]*>(.*?)<\/h[23]>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $level = strtolower($m[1]);
                $text  = trim(strip_tags($m[2]));
                if ($text === '') {
                    continue;
                }
                $headings[] = [
                    'level' => $level,
                    'text'  => $text,
                    'id'    => self::slug($text, $seen),
                ];
            }
        }
        return $headings;
    }

    /**
     * Resolve the translation array for a block, falling back to the first available language.
     */
    private static function translation(array $block, ?int $langId): array
    {
        $all = $block['translations'] ?? [];
        if ($langId !== null && isset($all[$langId])) {
            return $all[$langId];
        }
        return $all[array_key_first($all) ?? 0] ?? [];
    }

    /**
     * Convert a heading text to a URL-safe slug, guaranteeing uniqueness within a page.
     * Matches the algorithm used by the frontend JavaScript in toc.blade.php.
     *
     * @param  array<string,int>  $seen  Passed by reference — tracks used slugs
     */
    public static function slug(string $text, array &$seen): string
    {
        $base = preg_replace('/[^\w\s-]/u', '', mb_strtolower($text));
        $base = trim(preg_replace('/[\s_]+/', '-', $base), '-');
        $base = preg_replace('/-+/', '-', $base);
        $base = $base ?: 'section';

        $slug = $base;
        $n    = 1;
        while (isset($seen[$slug])) {
            $n++;
            $slug = $base . '-' . $n;
        }
        $seen[$slug] = 1;

        return $slug;
    }
}
