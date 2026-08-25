{{--
    Record7 root view.

    Record7's own Blade layout, loading ONLY Record7's own assets. Compare with
    app.blade.php (frontend1/frontend2), f3.blade.php (frontend3) and
    f4.blade.php (frontend4): a different entry point, different typefaces, a
    different ground colour, and no component-library stylesheet at all.

    Record7's only CSS is resources/css/record7/r7.css, imported by r7.jsx.
    Nothing from another front end is referenced here.
--}}
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex, nofollow">
    <title inertia>{{ config('record7.product_name', 'Record7') }}</title>

    {{--
        Sora for headings and Outfit for body and interface text — the pairing
        named in the Record7 design direction. Sora is geometric and confident
        at large sizes; Outfit is open and highly legible at the small sizes
        that names, doses and audit rows actually get read at.
    --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">

    {{-- Paint the warm cream ground before React mounts so there is no flash,
         and set the body font so nothing falls back to a serif. The app font is
         scoped to .r7-root; this is the safety net for body. --}}
    {{--
        NO THEME FLASH.

        The chosen theme is read and applied BEFORE the stylesheet paints and
        before React mounts, so a person who chose dark never sees a flash of
        cream first. Record7 defaults to warm cream, so light needs no work —
        only a stored dark choice writes anything.

        Inline and synchronous on purpose: anything deferred is, by definition,
        after the first paint. Storage can throw in a private window, so the
        whole thing is wrapped and the cream default simply stands.
    --}}
    <script>
        (function () {
            try {
                if (window.localStorage.getItem('record7.theme') === 'dark') {
                    document.documentElement.setAttribute('data-r7-theme', 'dark');
                }
            } catch (error) {
                /* Storage unavailable. Warm cream stands. */
            }
        })();
    </script>

    <style>
        html, body {
            margin: 0;
            background: #FAF4E9;
            font-family: "Outfit", "Segoe UI", system-ui, Arial, sans-serif;
        }
        /* Paint the midnight ground immediately for a stored dark choice, so
           the page never flashes cream before the stylesheet arrives. */
        html[data-r7-theme="dark"], html[data-r7-theme="dark"] body { background: #0E1922; }
    </style>

    @viteReactRefresh
    @vite(['resources/js/r7.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
