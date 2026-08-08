{{--
    frontend4 root view.

    Its own Blade layout, loading ONLY frontend4's own assets. Compare with
    app.blade.php (frontend1/frontend2) and f3.blade.php (frontend3): a different
    entry point, a different typeface, and — unlike both — no component-library
    stylesheet at all. frontend4's only CSS is frontend4/f4.css.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title inertia>{{ config('app.name', 'Care One OS') }} — Frontend 4</title>

    {{--
        Manrope for headings, Inter for interface and clinical text — the pairing
        in the Care One OS visual specification. Manrope is calm and slightly
        distinctive; Inter is the most legible at the small sizes that medicine
        names, doses and MAR tables actually get read at.
    --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Paint the warm cream background before React mounts so there is no flash,
         and set the body font so nothing can ever fall back to the serif default
         (the app font is scoped to .f4-root; this is the safety net for body). --}}
    <style>html,body{margin:0;background:#F6F2E9;font-family:"Plus Jakarta Sans","Inter","Segoe UI",Arial,sans-serif;}</style>

    @viteReactRefresh
    @vite(['resources/js/f4.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
