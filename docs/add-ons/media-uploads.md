# Media uploads

## When to use

Users upload avatars, images or attachments.

## Recommended package

Start with Laravel's own `Storage` — the kit already has a configurable uploads disk. For conversions, responsive images and collections, consider **`spatie/laravel-medialibrary`** (verify current version with the `researcher` agent; it adds a `media` table and its own conventions — record it in an ADR).

## Module to create

Add uploads to the module that owns the thing (e.g. avatars in Users, cover images in Blog). Create `php artisan make:module Media` only for a shared library of files used by several modules, and expose it via `Contracts\`.

## Integration points

- **Disk** — `config('filesystems.uploads')` reads `UPLOADS_DISK` (`public` by default; `local`, `public` or `s3` — any S3-compatible service: AWS, MinIO, R2, Arvan). Inject `Illuminate\Contracts\Filesystem\Factory` in the Action and call `->disk($config->string('filesystems.uploads'))` — no `Storage` facade in Actions (conventions §2).
- **Validation** (FormRequest): `['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']` (KB); for documents use an explicit `mimes:` list. Never trust the client file name or extension; store under a generated name (`$file->store(...)` / `hashName()`).
- **DTO** — pass the `UploadedFile` (or its stored path) in the DTO; the Action stores it, saves the path on the model, and deletes the previous file after the transaction commits.
- **Serving** — `public` disk needs `php artisan storage:link`; private files go on `local`/`s3` and are served through a controller with a Policy check or `temporaryUrl()`.
- **Security** — no SVG/HTML uploads on a public disk (stored XSS); set `client_max_body_size` / `upload_max_filesize` in production; CSP already allows `img-src 'self' data: blob:` — add your S3/CDN origin if files are served from there.
- **Cleanup** — a queued job or model event removes files when the owner is deleted.

## Tests to add

- `Storage::fake('public')` (or the configured disk) → upload → `Storage::disk(...)->assertExists($path)`.
- Validation: too large, wrong mime, not a file → errors.
- Replacing a file deletes the old one; deleting the owner deletes its files.
- Private file route: guest/unauthorized → 403, owner → 200.
