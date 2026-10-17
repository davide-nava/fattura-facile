<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

<script>
    (function () {
        function resolveTheme() {
            const storedTheme = localStorage.getItem('theme');
            const storedFlux = localStorage.getItem('flux.appearance');
            const preference = storedFlux || storedTheme;
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (preference === 'dark') return 'dark';
            if (preference === 'light') return 'light';
            if (preference === 'system') return prefersDark ? 'dark' : 'light';
            return prefersDark ? 'dark' : 'light';
        }

        const effective = resolveTheme();
        if (effective === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        window.applyTheme = function (theme) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' ;

            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            localStorage.setItem('flux.appearance', isDark ? 'dark' : 'light');

            if (window.$flux) {
                window.$flux.appearance = isDark ? 'dark' : 'light';
            }

            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: isDark ? 'dark' : 'light', mode: isDark ? 'dark' : 'light' }
            }));

            document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000; SameSite=Lax`;
        };

        window.toggleTheme = function () {
            const currentlyDark = document.documentElement.classList.contains('dark');
            window.applyTheme(currentlyDark ? 'light' : 'dark');
        };

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            const fluxApp = localStorage.getItem('flux.appearance');
            const themeApp = localStorage.getItem('theme');
            if (fluxApp === 'system' || (!fluxApp && !themeApp)) {
                window.applyTheme('system');
            }
        });
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
