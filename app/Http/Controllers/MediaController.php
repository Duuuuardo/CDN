<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\VideoUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'CDN API',
    version: '1.0.0',
    description: 'Simple CDN backend for storing and serving image and video files.',
    contact: new OA\Contact(email: 'admin@example.com')
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKeyHeader',
    type: 'apiKey',
    in: 'header',
    name: 'X-CDN-Key'
)]
class MediaController extends Controller
{
    #[OA\Post(
        path: '/api/upload/image',
        summary: 'Upload an image',
        description: 'Stores the uploaded image and returns its public URL. Accepted formats: jpg, jpeg, png, gif, webp, svg. Max 10 MB.',
        security: [['ApiKeyHeader' => []]],
        tags: ['Images'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Image uploaded successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'name',        type: 'string',  example: 'a1b2c3d4.jpg'),
                    new OA\Property(property: 'url',         type: 'string',  example: 'http://localhost:8000/storage/images/a1b2c3d4.jpg'),
                    new OA\Property(property: 'size',        type: 'integer', example: 204800),
                    new OA\Property(property: 'mime',        type: 'string',  example: 'image/jpeg'),
                    new OA\Property(property: 'uploaded_at', type: 'string',  format: 'date-time'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function uploadImage(ImageUploadRequest $request): JsonResponse
    {
        $file     = $request->file('file');
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        Storage::disk('cdn_images')->putFileAs('', $file, $filename);

        return response()->json([
            'name'        => $filename,
            'url'         => Storage::disk('cdn_images')->url($filename),
            'size'        => $file->getSize(),
            'mime'        => $file->getMimeType(),
            'uploaded_at' => now()->toIso8601String(),
        ], 201);
    }

    #[OA\Post(
        path: '/api/upload/video',
        summary: 'Upload a video',
        description: 'Stores the uploaded video and returns its public URL. Accepted formats: mp4, webm, ogg, mov, avi, mkv. Max 500 MB.',
        security: [['ApiKeyHeader' => []]],
        tags: ['Videos'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Video uploaded successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'name',        type: 'string',  example: 'e5f6a7b8.mp4'),
                    new OA\Property(property: 'url',         type: 'string',  example: 'http://localhost:8000/storage/videos/e5f6a7b8.mp4'),
                    new OA\Property(property: 'size',        type: 'integer', example: 104857600),
                    new OA\Property(property: 'mime',        type: 'string',  example: 'video/mp4'),
                    new OA\Property(property: 'uploaded_at', type: 'string',  format: 'date-time'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function uploadVideo(VideoUploadRequest $request): JsonResponse
    {
        $file     = $request->file('file');
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        Storage::disk('cdn_videos')->putFileAs('', $file, $filename);

        return response()->json([
            'name'        => $filename,
            'url'         => Storage::disk('cdn_videos')->url($filename),
            'size'        => $file->getSize(),
            'mime'        => $file->getMimeType(),
            'uploaded_at' => now()->toIso8601String(),
        ], 201);
    }

    #[OA\Get(
        path: '/api/files',
        summary: 'List all uploaded files',
        description: "Returns a paginated list of all images and videos. Use the 'type' filter to narrow results.",
        tags: ['Files'],
        parameters: [
            new OA\Parameter(name: 'type',  in: 'query', required: false, description: "Filter by type: 'images' or 'videos'",
                schema: new OA\Schema(type: 'string', enum: ['images', 'videos'])),
            new OA\Parameter(name: 'page',  in: 'query', required: false, description: 'Page number (default 1)',
                schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Items per page (default 20, max 100)',
                schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File listing',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'type', type: 'string', enum: ['images', 'videos']),
                        new OA\Property(property: 'url',  type: 'string'),
                        new OA\Property(property: 'size', type: 'integer'),
                    ])),
                    new OA\Property(property: 'total',       type: 'integer'),
                    new OA\Property(property: 'page',        type: 'integer'),
                    new OA\Property(property: 'limit',       type: 'integer'),
                    new OA\Property(property: 'total_pages', type: 'integer'),
                ])
            ),
        ]
    )]
    public function listFiles(Request $request): JsonResponse
    {
        $type  = $request->query('type');
        $page  = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $files = [];

        if ($type !== 'videos') {
            foreach (Storage::disk('cdn_images')->files() as $file) {
                $files[] = [
                    'name' => basename($file),
                    'type' => 'images',
                    'url'  => Storage::disk('cdn_images')->url($file),
                    'size' => Storage::disk('cdn_images')->size($file),
                ];
            }
        }

        if ($type !== 'images') {
            foreach (Storage::disk('cdn_videos')->files() as $file) {
                $files[] = [
                    'name' => basename($file),
                    'type' => 'videos',
                    'url'  => Storage::disk('cdn_videos')->url($file),
                    'size' => Storage::disk('cdn_videos')->size($file),
                ];
            }
        }

        $total  = count($files);
        $offset = ($page - 1) * $limit;
        $paged  = array_slice($files, $offset, $limit);

        return response()->json([
            'data'        => $paged,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int) ceil($total / $limit),
        ]);
    }

    #[OA\Delete(
        path: '/api/files/{type}/{name}',
        summary: 'Delete a file',
        description: 'Permanently removes a file from CDN storage.',
        security: [['ApiKeyHeader' => []]],
        tags: ['Files'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'path', required: true, description: "File type: 'images' or 'videos'",
                schema: new OA\Schema(type: 'string', enum: ['images', 'videos'])),
            new OA\Parameter(name: 'name', in: 'path', required: true, description: 'File name returned on upload',
                schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'File deleted',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'File deleted.'),
                ])),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'File not found'),
        ]
    )]
    public function deleteFile(string $type, string $name): JsonResponse
    {
        $disk = match ($type) {
            'images' => 'cdn_images',
            'videos' => 'cdn_videos',
            default  => null,
        };

        if ($disk === null) {
            return response()->json(['message' => 'Invalid type. Use "images" or "videos".'], 400);
        }

        if (! Storage::disk($disk)->exists($name)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        Storage::disk($disk)->delete($name);

        return response()->json(['message' => 'File deleted.']);
    }
}
