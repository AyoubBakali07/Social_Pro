<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        return Client::all();
    }

    public function show(Client $client)
    {
        return $client;
    }

    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated());
        return response()->json($client, 201);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, 204);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            abort(403, 'No client found for this user.');
        }
        $pendingApprovals = Post::where('client_id', $client->id)->where('status', 'pending')->count();
        $approvedThisMonth = Post::where('client_id', $client->id)
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $upcomingPosts = Post::where('client_id', $client->id)
            ->where('status', 'scheduled')
            ->where('scheduleDate', '>=', now())
            ->count();
        $rejected = Post::where('client_id', $client->id)->where('status', 'rejected')->count();
        $pendingPosts = Post::where('client_id', $client->id)
            ->where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                $mediaUrls = collect($post->media)
                    ->filter()
                    ->map(function ($path) {
                        $fixedPath = str_replace('\\', '/', $path);
                        return Storage::url($fixedPath);
                    })->all();

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'platform' => $post->platform,
                    'postType' => $post->postType,
                    'status' => $post->status,
                    'client_id' => $post->client_id,
                    'created_at' => $post->created_at->toIso8601String(),
                    'scheduleDate' => optional($post->scheduleDate)->toIso8601String(),
                    'feedback' => $post->feedback,
                    'comment' => $post->comment,
                    'media' => $mediaUrls,
                ];
            });

        $calendarPosts = Post::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'scheduled', 'approved', 'published'])
            ->with('client')
            ->orderBy('scheduleDate', 'asc')
            ->get()
            ->map(function ($post) {
                $mediaUrls = collect($post->media)
                    ->filter()
                    ->map(function ($path) {
                        $fixedPath = str_replace('\\', '/', $path);
                        return Storage::url($fixedPath);
                    })->all();

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'scheduleDate' => optional($post->scheduleDate)->toIso8601String(),
                    'platform' => $post->platform,
                    'postType' => $post->postType,
                    'status' => $post->status,
                    'media' => $mediaUrls,
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            });

        return Inertia::render('Client/Dashboard', [
            'stats' => [
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'color' => 'text-blue-600',
                    'icon' => "<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/></svg>"
                ],
                [
                    'label' => 'Approved This Month',
                    'value' => $approvedThisMonth,
                    'color' => 'text-green-600',
                    'icon' => "<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'/></svg>"
                ],
                [
                    'label' => 'Upcoming Posts',
                    'value' => $upcomingPosts,
                    'color' => 'text-yellow-600',
                    'icon' => "<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/></svg>"
                ],
                [
                    'label' => 'Rejected',
                    'value' => $rejected,
                    'color' => 'text-red-600',
                    'icon' => "<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/></svg>"
                ]
            ],
            'pendingPosts' => $pendingPosts,
            'calendarPosts' => $calendarPosts
        ]);
    }

    public function approve(Post $post)
    {
        $client = Auth::user()->client;
        if (!$client || $post->client_id !== $client->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Post approved');
    }

    public function addComment(Request $request, Post $post)
    {
        $client = Auth::user()->client;
        if (!$client || $post->client_id !== $client->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $post->update(['comment' => $validated['comment']]);
        return redirect()->back()->with('success', 'Comment added');
    }

    public function reject(Request $request, Post $post)
    {
        $client = Auth::user()->client;
        if (!$client || $post->client_id !== $client->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $post->update(['status' => 'rejected', 'feedback' => $validated['feedback']]);
        return redirect()->back()->with('success', 'Post rejected with feedback');
    }
} 