<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Post $post)
    {
        // Check if the authenticated user is the client who owns this post
        $client = auth()->user()->client;
        if (!$client || $post->client_id !== $client->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content']
        ]);

        // Load the post with relationships for the response
        $post->load(['agency.user:id,name', 'comments']);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->toIso8601String(),
            ],
            'post' => $this->transformPost($post)
        ]);
    }

    /**
     * Transform post for API response
     */
    protected function transformPost(Post $post)
    {
        return [
            'id' => $post->id,
            'title' => $post->title ?? 'Untitled Post',
            'content' => $post->content,
            'scheduled_at' => $post->scheduleDate?->toIso8601String() ?? now()->toIso8601String(),
            'platforms' => is_array($post->platform) ? $post->platform : [],
            'status' => $post->status,
            'media_url' => $post->media ? asset('storage/' . $post->media) : null,
            'type' => 'post',
            'comments' => $post->comments->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toIso8601String(),
                ];
            }),
            'rejection_reason' => $post->feedback,
            'agency_name' => $post->agency->user->name ?? 'Unknown Agency',
        ];
    }
}
