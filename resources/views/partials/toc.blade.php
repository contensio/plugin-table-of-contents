<nav class="contensio-toc mb-8 rounded-xl border border-gray-200 bg-gray-50 px-5 py-4" aria-label="Table of contents">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Contents</p>
    <ol class="space-y-1">
        @foreach($headings as $heading)
            <li class="{{ $heading['level'] === 'h3' ? 'pl-4' : '' }}">
                <a href="#{{ $heading['id'] }}"
                   class="text-sm text-gray-600 hover:text-ember-600 transition-colors leading-snug block py-0.5">
                    {{ $heading['text'] }}
                </a>
            </li>
        @endforeach
    </ol>
</nav>

<script>
(function () {
    // Mirrors HeadingExtractor::slug() — must stay in sync.
    function slugify(text, seen) {
        var base = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-') || 'section';
        var slug = base, n = 1;
        while (seen[slug]) { n++; slug = base + '-' + n; }
        seen[slug] = 1;
        return slug;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var body = document.querySelector('.contensio-post-body');
        if (!body) return;

        var seen = {};
        body.querySelectorAll('h2, h3').forEach(function (el) {
            var text = el.textContent.trim();
            var id   = slugify(text, seen);
            if (!el.id) el.id = id;
        });
    });
}());
</script>
