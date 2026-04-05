<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        'cdn_images' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/images'),
            'url'        => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/images',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        'cdn_videos' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/videos'),
            'url'        => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/videos',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
