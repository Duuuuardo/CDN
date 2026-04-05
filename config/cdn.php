<?php

return [
    'api_key'          => env('CDN_API_KEY', ''),
    'max_image_size_kb' => env('CDN_MAX_IMAGE_KB', 10240),
    'max_video_size_kb' => env('CDN_MAX_VIDEO_KB', 512000),
    'image_mimes'      => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
    'video_mimes'      => ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'],
];
