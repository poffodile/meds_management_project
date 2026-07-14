<!DOCTYPE html>
<html lang="en" data-mantine-color-scheme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'CareOS') }}</title>
    {{-- Global app font (matches the modern UI tokens — see frontend/tokens.js).
         Extra families are loaded for the Stock page font trial (Inter + alternates);
         once a winner is chosen we self-host it and trim this back. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Geist:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=Public+Sans:wght@400;500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&family=Instrument+Sans:wght@400;500;600;700&family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Satoshi (not on Google Fonts) — Fontshare CDN. --}}
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    {{-- Apply the saved site-wide font BEFORE React mounts, so there's no flash.
         Must mirror the stacks in frontend/lib/font.js. --}}
    <script>
        (function () {
            try {
                var FB = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                var M = {
                    inter: '"Inter", ' + FB, satoshi: '"Satoshi", ' + FB, manrope: '"Manrope", ' + FB,
                    jakarta: '"Plus Jakarta Sans", ' + FB, system: FB, geist: '"Geist", ' + FB,
                    dmsans: '"DM Sans", ' + FB, outfit: '"Outfit", ' + FB, publicsans: '"Public Sans", ' + FB,
                    plex: '"IBM Plex Sans", ' + FB, instrument: '"Instrument Sans", ' + FB, figtree: '"Figtree", ' + FB
                };
                var body = M[localStorage.getItem('careone-font-body') || 'inter'] || M.inter;
                var head = M[localStorage.getItem('careone-font-headings') || 'inter'] || M.inter;
                document.documentElement.style.setProperty('--mantine-font-family', body);
                document.documentElement.style.setProperty('--mantine-font-family-headings', head);
            } catch (e) { /* ignore */ }
        })();
    </script>
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
