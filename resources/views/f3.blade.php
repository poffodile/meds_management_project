{{--
    frontend3 root view.

    Its own Blade layout, loading ONLY frontend3's own assets. Compare with
    app.blade.php (frontend2): different fonts, different entry point, no
    font-switcher script, no Fontshare, no shared stylesheet.

    frontend3's typography is fixed by the specification — Manrope for headings,
    Inter for body — so there is deliberately no font picker here.

    See docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title inertia>{{ config('app.name', 'Care One OS') }} — Frontend 3</title>

    {{-- Quiet Clinical Luxury type: Manrope headings, Inter body (spec §17). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Paint the app background before React mounts so there is no white flash. --}}
    <style>html,body{margin:0;background:#F6F2E9;}</style>

    @viteReactRefresh
    @vite(['resources/js/f3.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
