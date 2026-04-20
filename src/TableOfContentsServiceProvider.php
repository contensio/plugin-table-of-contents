<?php

namespace Contensio\Plugins\TableOfContents;

use Contensio\Models\Content;
use Contensio\Models\ContentTranslation;
use Contensio\Models\Language;
use Contensio\Plugins\TableOfContents\Support\HeadingExtractor;
use Contensio\Support\Hook;
use Illuminate\Support\ServiceProvider;

class TableOfContentsServiceProvider extends ServiceProvider
{
    protected string $ns = 'contensio-table-of-contents';

    /**
     * Minimum number of headings required before the TOC is rendered.
     * A post with only one or two headings doesn't need a TOC.
     */
    private const MIN_HEADINGS = 3;

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->ns);

        Hook::add('contensio/frontend/post-before-content', function (Content $content, ContentTranslation $translation): string {
            $langId   = Language::where('is_default', true)->value('id');
            $blocks   = $content->blocks ?? [];
            $headings = HeadingExtractor::extract($blocks, $langId);

            if (count($headings) < self::MIN_HEADINGS) {
                return '';
            }

            return view($this->ns . '::partials.toc', compact('headings'))->render();
        });
    }
}
