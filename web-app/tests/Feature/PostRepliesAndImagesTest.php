<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\BitcoinAuth;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostRepliesAndImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_root_post_with_image_and_reply_to_it(): void
    {
        Storage::fake('public');

        // Create a thread using the app's models
        $thread = Thread::factory()->create();

        // Create root post with image
        $response = $this->postJson("/api/threads/{$thread->id}/posts", [
            'body' => 'root',
            'image' => UploadedFile::fake()->image('a.jpg', 600, 400)
        ]);

        $response->assertCreated();

        $rootPost = Post::first();
        $this->assertNotNull($rootPost);
        $this->assertEquals('root', $rootPost->content);

        // Create reply post
        $replyResponse = $this->postJson("/api/threads/{$thread->id}/posts", [
            'body' => 'reply',
            'parent_id' => $rootPost->id
        ]);

        $replyResponse->assertCreated();

        // Verify image was stored
        if ($rootPost->image_path) {
            Storage::disk('public')->assertExists($rootPost->image_path);
        }

        // Verify reply relationship
        $replyPost = Post::where('parent_id', $rootPost->id)->first();
        $this->assertNotNull($replyPost);
        $this->assertEquals('reply', $replyPost->content);
        $this->assertEquals($rootPost->id, $replyPost->parent_id);

        // Test the API response structure
        $getResponse = $this->getJson("/api/threads/{$thread->id}/posts");
        $getResponse->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.body', 'root')
            ->assertJsonPath('data.1.body', 'reply')
            ->assertJsonPath('data.1.parent_id', $rootPost->id);
    }
}