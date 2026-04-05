<?php

namespace Tests\Unit;

use App\Services\BlogApiService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.blog.url'     => 'http://blog-nginx',
            'services.blog.api_key' => 'test-key',
        ]);
    }

    // --- Posts ---

    #[Test]
    public function get_posts_returns_paginated_data_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts*' => Http::response([
                'data' => [['id' => 1, 'title' => 'Post 1', 'slug' => 'post-1']],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->getPosts();

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('Post 1', $result['data'][0]['title']);
    }

    #[Test]
    public function get_posts_returns_empty_structure_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/posts*' => Http::response(null, 500)]);

        $result = app(BlogApiService::class)->getPosts();

        $this->assertEquals(['data' => [], 'meta' => []], $result);
    }

    #[Test]
    public function create_post_returns_success_with_data(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts' => Http::response([
                'data' => ['id' => 1, 'title' => 'New Post', 'slug' => 'new-post'],
            ], 201),
        ]);

        $result = app(BlogApiService::class)->createPost([
            'title'   => 'New Post',
            'slug'    => 'new-post',
            'content' => 'Content',
            'status'  => 'draft',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('New Post', $result['data']['title']);
    }

    #[Test]
    public function create_post_returns_failure_with_error_body_on_422(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts' => Http::response([
                'message' => 'The slug has already been taken.',
                'errors'  => ['slug' => ['The slug has already been taken.']],
            ], 422),
        ]);

        $result = app(BlogApiService::class)->createPost([
            'title'   => 'Dupe',
            'slug'    => 'existing-slug',
            'content' => 'Content',
            'status'  => 'draft',
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(422, $result['status']);
        $this->assertArrayHasKey('errors', $result['body']);
    }

    #[Test]
    public function create_post_returns_failure_on_server_error(): void
    {
        Http::fake(['blog-nginx/api/internal/posts' => Http::response(null, 500)]);

        $result = app(BlogApiService::class)->createPost(['title' => 'Test']);

        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['status']);
    }

    #[Test]
    public function update_post_returns_success_with_data(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts/1' => Http::response([
                'data' => ['id' => 1, 'title' => 'Updated', 'slug' => 'updated'],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->updatePost(1, ['title' => 'Updated']);

        $this->assertTrue($result['success']);
        $this->assertEquals('Updated', $result['data']['title']);
    }

    #[Test]
    public function update_post_returns_failure_on_404(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts/999' => Http::response(['message' => 'Not found'], 404),
        ]);

        $result = app(BlogApiService::class)->updatePost(999, ['title' => 'X']);

        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['status']);
    }

    #[Test]
    public function delete_post_returns_true_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts/1' => Http::response(['message' => 'Post deleted successfully'], 200),
        ]);

        $this->assertTrue(app(BlogApiService::class)->deletePost(1));
    }

    #[Test]
    public function delete_post_returns_false_on_failure(): void
    {
        Http::fake([
            'blog-nginx/api/internal/posts/999' => Http::response(null, 404),
        ]);

        $this->assertFalse(app(BlogApiService::class)->deletePost(999));
    }

    // --- Categories ---

    #[Test]
    public function get_categories_returns_list_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/categories*' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'PHP', 'slug' => 'php'],
                    ['id' => 2, 'name' => 'Docker', 'slug' => 'docker'],
                ],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->getCategories();

        $this->assertCount(2, $result);
        $this->assertEquals('PHP', $result[0]['name']);
    }

    #[Test]
    public function get_categories_returns_empty_array_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/categories*' => Http::response(null, 500)]);

        $this->assertEquals([], app(BlogApiService::class)->getCategories());
    }

    #[Test]
    public function create_category_returns_data_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/categories' => Http::response([
                'data' => ['id' => 1, 'name' => 'DevOps', 'slug' => 'devops'],
            ], 201),
        ]);

        $result = app(BlogApiService::class)->createCategory(['name' => 'DevOps', 'slug' => 'devops']);

        $this->assertTrue($result['success']);
        $this->assertEquals('DevOps', $result['data']['name']);
    }

    #[Test]
    public function create_category_returns_null_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/categories' => Http::response(null, 422)]);

        $result = app(BlogApiService::class)->createCategory(['name' => 'X']);
        $this->assertFalse($result['success']);
    }

    #[Test]
    public function delete_category_returns_true_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/categories/1' => Http::response(null, 200),
        ]);

        $this->assertTrue(app(BlogApiService::class)->deleteCategory(1));
    }

    // --- Tags ---

    #[Test]
    public function get_tags_returns_list_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/tags*' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'Laravel', 'slug' => 'laravel'],
                ],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->getTags();

        $this->assertCount(1, $result);
        $this->assertEquals('Laravel', $result[0]['name']);
    }

    #[Test]
    public function get_tags_returns_empty_array_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/tags*' => Http::response(null, 500)]);

        $this->assertEquals([], app(BlogApiService::class)->getTags());
    }

    #[Test]
    public function create_tag_returns_data_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/tags' => Http::response([
                'data' => ['id' => 5, 'name' => 'Kubernetes', 'slug' => 'kubernetes'],
            ], 201),
        ]);

        $result = app(BlogApiService::class)->createTag(['name' => 'Kubernetes', 'slug' => 'kubernetes']);

        $this->assertNotNull($result);
        $this->assertEquals('Kubernetes', $result['name']);
    }

    #[Test]
    public function create_tag_returns_null_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/tags' => Http::response(null, 422)]);

        $this->assertNull(app(BlogApiService::class)->createTag(['name' => 'X']));
    }

    #[Test]
    public function delete_tag_returns_true_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/tags/1' => Http::response(null, 200),
        ]);

        $this->assertTrue(app(BlogApiService::class)->deleteTag(1));
    }

    #[Test]
    public function delete_tag_returns_false_on_failure(): void
    {
        Http::fake([
            'blog-nginx/api/internal/tags/999' => Http::response(null, 404),
        ]);

        $this->assertFalse(app(BlogApiService::class)->deleteTag(999));
    }

    // --- Media ---

    #[Test]
    public function get_media_returns_paginated_data_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media*' => Http::response([
                'data' => [['id' => 1, 'uuid' => 'abc', 'filename' => 'photo.jpg']],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->getMedia();

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('photo.jpg', $result['data'][0]['filename']);
    }

    #[Test]
    public function get_media_passes_query_params(): void
    {
        Http::fake(['blog-nginx/*' => Http::response(['data' => [], 'meta' => []], 200)]);

        app(BlogApiService::class)->getMedia(['search' => 'banner', 'page' => 2]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'search=banner')
                && str_contains($request->url(), 'page=2');
        });
    }

    #[Test]
    public function get_media_returns_empty_structure_on_failure(): void
    {
        Http::fake(['blog-nginx/api/internal/media*' => Http::response(null, 500)]);

        $result = app(BlogApiService::class)->getMedia();

        $this->assertEquals(['data' => [], 'meta' => []], $result);
    }

    #[Test]
    public function upload_media_returns_success_with_data(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media' => Http::response([
                'data' => ['id' => 1, 'uuid' => 'abc', 'filename' => 'photo.jpg', 'url' => 'http://cdn/photo.jpg'],
            ], 201),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');
        $result = app(BlogApiService::class)->uploadMedia($file, 'A test photo');

        $this->assertTrue($result['success']);
        $this->assertEquals('photo.jpg', $result['data']['filename']);
    }

    #[Test]
    public function upload_media_sends_multipart_with_api_key(): void
    {
        Http::fake(['blog-nginx/api/internal/media' => Http::response(['data' => []], 201)]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('test.png', 100, 'image/png');
        app(BlogApiService::class)->uploadMedia($file, 'alt text');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Internal-Api-Key', 'test-key')
                && str_contains($request->url(), '/api/internal/media');
        });
    }

    #[Test]
    public function upload_media_returns_failure_on_validation_error(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media' => Http::response([
                'message' => 'The file field is required.',
                'errors' => ['file' => ['The file field is required.']],
            ], 422),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $result = app(BlogApiService::class)->uploadMedia($file);

        $this->assertFalse($result['success']);
        $this->assertEquals(422, $result['status']);
    }

    #[Test]
    public function update_media_returns_success_with_data(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media/1' => Http::response([
                'data' => ['id' => 1, 'uuid' => 'abc', 'filename' => 'photo.jpg', 'alt' => 'Updated alt'],
            ], 200),
        ]);

        $result = app(BlogApiService::class)->updateMedia(1, ['alt' => 'Updated alt']);

        $this->assertTrue($result['success']);
        $this->assertEquals('Updated alt', $result['data']['alt']);
    }

    #[Test]
    public function update_media_returns_failure_on_404(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media/999' => Http::response(['message' => 'Not found'], 404),
        ]);

        $result = app(BlogApiService::class)->updateMedia(999, ['alt' => 'test']);

        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['status']);
    }

    #[Test]
    public function delete_media_returns_true_on_success(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media/1' => Http::response(null, 204),
        ]);

        $this->assertTrue(app(BlogApiService::class)->deleteMedia(1));
    }

    #[Test]
    public function delete_media_returns_false_on_failure(): void
    {
        Http::fake([
            'blog-nginx/api/internal/media/999' => Http::response(null, 404),
        ]);

        $this->assertFalse(app(BlogApiService::class)->deleteMedia(999));
    }

    // --- Internal API key header ---

    #[Test]
    public function requests_include_internal_api_key_header(): void
    {
        Http::fake(['blog-nginx/*' => Http::response(['data' => []], 200)]);

        app(BlogApiService::class)->getCategories();

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Internal-Api-Key', 'test-key');
        });
    }
}
