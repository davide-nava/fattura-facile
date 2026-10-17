<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default PDF Generator Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "browsershot", "dompdf"
    |
    */
    'default' => env('PDF_DRIVER', 'browsershot'),

    /*
    |--------------------------------------------------------------------------
    | Fallback PDF Generator Driver
    |--------------------------------------------------------------------------
    |
    | If the primary driver (e.g. Browsershot) fails due to missing system
    | dependencies (Node/Puppeteer/Chromium), the manager will fall back to this driver.
    | Set to null to disable fallback and let exceptions bubble up.
    |
    */
    'fallback' => env('PDF_FALLBACK_DRIVER', 'dompdf'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'browsershot' => [
            'node_binary' => env('BROWSERSHOT_NODE_BINARY'),
            'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),
            'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', base_path('node_modules')),
            'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
            'no_sandbox' => env('BROWSERSHOT_NO_SANDBOX', true),
            'temp_path' => env('BROWSERSHOT_TEMP_PATH', storage_path('app/temp')),
            'timeout' => env('BROWSERSHOT_TIMEOUT', 60),
            'default_options' => [
                'format' => 'A4',
                'orientation' => 'portrait',
                'print_background' => true,
                'margins' => [
                    'top' => 12,
                    'right' => 10,
                    'bottom' => 15,
                    'left' => 10,
                ],
            ],
        ],

        'dompdf' => [
            'default_options' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'enable_remote' => true,
                'default_font' => 'Helvetica',
            ],
        ],
    ],
];
