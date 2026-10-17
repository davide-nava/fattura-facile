<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - Fattura Facile' : 'Fattura Facile' }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

<script>
    (function () {
        // Migrate legacy 'theme' to Flux's canonical 'flux.appearance' if not set
        try {
            const storedFlux = localStorage.getItem('flux.appearance');
            const storedTheme = localStorage.getItem('theme');
            if (!storedFlux && storedTheme) {
                if (storedTheme === 'dark' || storedTheme === 'light') {
                    localStorage.setItem('flux.appearance', storedTheme);
                }
            }
        } catch (e) {}

        window.resolveTheme = function () {
            const storedFlux = localStorage.getItem('flux.appearance');
            const storedTheme = localStorage.getItem('theme');
            const preference = storedFlux || storedTheme;
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (preference === 'dark') return 'dark';
            if (preference === 'light') return 'light';
            if (preference === 'system') return prefersDark ? 'dark' : 'light';
            return prefersDark ? 'dark' : 'light';
        };

        const effective = window.resolveTheme();
        if (effective === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
    (function () {
        // Intercept Flux.applyAppearance so that whenever Flux changes appearance
        // (via $flux.appearance, $flux.dark, or navigation), we keep DOM, storage, and cookies synchronized.
        if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
            const originalApply = window.Flux.applyAppearance;

            window.Flux.applyAppearance = function (appearance) {
                originalApply(appearance);

                const isDark = document.documentElement.classList.contains('dark');
                try {
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000; SameSite=Lax`;
                } catch (e) {}

                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: { theme: isDark ? 'dark' : 'light', mode: isDark ? 'dark' : 'light', appearance: appearance }
                }));
            };

            // Run once on initial load to ensure cookie & legacy keys match the resolved appearance
            const currentPref = localStorage.getItem('flux.appearance') || 'system';
            window.Flux.applyAppearance(currentPref);
        }

        // Global applyTheme function (callable from components or vanilla JS)
        window.applyTheme = function (theme) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'system' && prefersDark);

            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            try {
                if (theme === 'system') {
                    localStorage.removeItem('flux.appearance');
                } else {
                    localStorage.setItem('flux.appearance', isDark ? 'dark' : 'light');
                }
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000; SameSite=Lax`;
            } catch (e) {}

            if (window.Flux && typeof window.Flux === 'object') {
                if ('appearance' in window.Flux) {
                    window.Flux.appearance = theme;
                } else if (typeof window.Flux.applyAppearance === 'function') {
                    window.Flux.applyAppearance(theme);
                }
            }

            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: isDark ? 'dark' : 'light', mode: isDark ? 'dark' : 'light', appearance: theme }
            }));
        };

        // Global toggleTheme function
        window.toggleTheme = function () {
            const isCurrentlyDark = document.documentElement.classList.contains('dark');
            window.applyTheme(isCurrentlyDark ? 'light' : 'dark');
        };

        // Listen for OS color scheme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            const fluxApp = localStorage.getItem('flux.appearance');
            if (!fluxApp || fluxApp === 'system') {
                window.applyTheme('system');
            }
        });

        // Prevent flicker during Livewire SPA page swaps
        let navObserver = null;
        document.addEventListener('livewire:navigating', function () {
            if (navObserver) navObserver.disconnect();
            const isDark = document.documentElement.classList.contains('dark');
            navObserver = new MutationObserver(function () {
                if (isDark && !document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.add('dark');
                }
            });
            navObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        });
        document.addEventListener('livewire:navigated', function () {
            if (navObserver) {
                navObserver.disconnect();
                navObserver = null;
            }
        });
    })();
</script>
