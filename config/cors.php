<?php

return [
    'paths' => ['*'],

    'allowed_methods' => ['GET','POST','PUT','PATCH','DELETE','OPTIONS'],

    // اگر می‌خوای فقط با الگو مچ کنی، این رو خالی بذار
    'allowed_origins' => [
        'https://www.lift-app.com',
        'https://lift-app.com',
        // اگر می‌خوای فقط pattern داشته باشی، می‌تونی اینجا رو هم خالی [] بذاری
    ],

    // مهم: الگوها برای Figma preview و کل lift-app
    'allowed_origins_patterns' => [
        '#^https:\/\/([a-z0-9-]+\.)?lift-app\.com$#i',
        '#^https:\/\/([a-z0-9-]+\.)?figma\.site$#i',
        '#^https:\/\/([a-z0-9-]+\.)?figma\.com$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization','X-Request-Id'],

    'max_age' => 86400,

    'supports_credentials' => false, // چون Bearer استفاده می‌کنی
];
