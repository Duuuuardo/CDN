<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('cdn_images');
        Storage::fake('cdn_videos');
    }

    public function test_image_upload_returns_201_with_file_url(): void
    {
        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/upload/image', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure(['name', 'url', 'size', 'mime', 'uploaded_at']);

        $name = $response->json('name');
        Storage::disk('cdn_images')->assertExists($name);
    }

    public function test_video_upload_returns_201_with_file_url(): void
    {
        $file = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

        $response = $this->postJson('/api/upload/video', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure(['name', 'url', 'size', 'mime', 'uploaded_at']);

        $name = $response->json('name');
        Storage::disk('cdn_videos')->assertExists($name);
    }

    public function test_image_upload_rejects_non_image_mime(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/upload/image', ['file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_video_upload_rejects_non_video_mime(): void
    {
        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/upload/video', ['file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_list_files_returns_paginated_response(): void
    {
        $response = $this->getJson('/api/files');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'page', 'limit', 'total_pages']);
    }

    public function test_list_files_can_filter_by_type(): void
    {
        $response = $this->getJson('/api/files?type=images');
        $response->assertStatus(200);

        $response = $this->getJson('/api/files?type=videos');
        $response->assertStatus(200);
    }

    public function test_delete_returns_404_for_missing_file(): void
    {
        $response = $this->deleteJson('/api/files/images/nonexistent.jpg');
        $response->assertStatus(404);
    }

    public function test_delete_removes_existing_file(): void
    {
        $file = UploadedFile::fake()->create('todelete.png', 50, 'image/png');
        $upload = $this->postJson('/api/upload/image', ['file' => $file]);
        $name = $upload->json('name');

        $response = $this->deleteJson("/api/files/images/{$name}");

        $response->assertStatus(200)->assertJson(['message' => 'File deleted.']);
        Storage::disk('cdn_images')->assertMissing($name);
    }

    public function test_api_key_middleware_blocks_upload_when_key_is_set(): void
    {
        config(['cdn.api_key' => 'secret-key']);

        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');
        $response = $this->postJson('/api/upload/image', ['file' => $file]);

        $response->assertStatus(401);
    }

    public function test_api_key_middleware_allows_upload_with_correct_key(): void
    {
        config(['cdn.api_key' => 'secret-key']);

        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');
        $response = $this->postJson('/api/upload/image', ['file' => $file], [
            'X-CDN-Key' => 'secret-key',
        ]);

        $response->assertStatus(201);
    }
}
