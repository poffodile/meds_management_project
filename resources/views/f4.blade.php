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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Paint the app background before React mounts so there is no white flash. --}}
    <style>html,body{margin:0;background:#F7F8FA;}</style>

    @viteReactRefresh
    @vite(['resources/js/f4.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
