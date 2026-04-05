# CDN Backend

A minimal CDN backend built with Laravel. Handles upload, storage and serving of image and video files through a simple JSON API.

## Requirements

- PHP >= 8.2
- Composer
- Any web server (or `php artisan serve` for local development)

## Setup

```bash
composer install

cp .env.example .env
php artisan key:generate

php artisan storage:link
```

Adjust `.env` as needed (see configuration section below), then start the server:

```bash
php artisan serve
```

## Configuration

| Variable | Default | Description |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | Base URL used to build file URLs |
| `CDN_API_KEY` | *(empty)* | Shared secret for upload and delete routes. Leave empty to disable auth in local dev. |
| `CDN_MAX_IMAGE_KB` | `10240` | Maximum image size in kilobytes (default 10 MB) |
| `CDN_MAX_VIDEO_KB` | `512000` | Maximum video size in kilobytes (default 500 MB) |

Make sure `upload_max_filesize` and `post_max_size` in `php.ini` are at least as large as the video limit.

## API

Interactive docs are available at `/api/documentation` once the server is running.

### Authentication

Protected routes require the `X-CDN-Key` header when `CDN_API_KEY` is set:

```
X-CDN-Key: your-secret-key
```

You can also pass it as a query parameter: `?api_key=your-secret-key`.

---

### POST /api/upload/image

Upload an image file.

**Protected:** yes  
**Content-Type:** `multipart/form-data`

| Field | Type | Description |
|---|---|---|
| `file` | binary | Image file. Accepted: jpg, jpeg, png, gif, webp, svg. |

**Response 201**
```json
{
  "name": "550e8400-e29b-41d4-a716-446655440000.jpg",
  "url": "http://localhost:8000/storage/images/550e8400-e29b-41d4-a716-446655440000.jpg",
  "size": 204800,
  "mime": "image/jpeg",
  "uploaded_at": "2024-01-15T10:30:00+00:00"
}
```

---

### POST /api/upload/video

Upload a video file.

**Protected:** yes  
**Content-Type:** `multipart/form-data`

| Field | Type | Description |
|---|---|---|
| `file` | binary | Video file. Accepted: mp4, webm, ogg, mov, avi, mkv. |

**Response 201**
```json
{
  "name": "550e8400-e29b-41d4-a716-446655440000.mp4",
  "url": "http://localhost:8000/storage/videos/550e8400-e29b-41d4-a716-446655440000.mp4",
  "size": 104857600,
  "mime": "video/mp4",
  "uploaded_at": "2024-01-15T10:30:00+00:00"
}
```

---

### GET /api/files

List stored files.

**Protected:** no

| Query param | Default | Description |
|---|---|---|
| `type` | *(all)* | Filter by `images` or `videos` |
| `page` | `1` | Page number |
| `limit` | `20` | Items per page (max 100) |

**Response 200**
```json
{
  "data": [
    {
      "name": "550e8400.jpg",
      "type": "images",
      "url": "http://localhost:8000/storage/images/550e8400.jpg",
      "size": 204800
    }
  ],
  "total": 1,
  "page": 1,
  "limit": 20,
  "total_pages": 1
}
```

---

### DELETE /api/files/{type}/{name}

Delete a stored file.

**Protected:** yes  
**Path params:** `type` is `images` or `videos`, `name` is the file name returned on upload.

**Response 200**
```json
{ "message": "File deleted." }
```

## Storage layout

Files are stored under `storage/app/public/` and served through the symlinked `public/storage/` directory:

```
storage/app/public/
├── images/   ← uploaded images
└── videos/   ← uploaded videos
```

## License

MIT
