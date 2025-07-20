<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use App\Http\Requests\AgencyRequest;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    public function index()
    {
        return Agency::all();
    }

    public function show(Agency $agency)
    {
        return $agency;
    }

    public function store(AgencyRequest $request)
    {
        $agency = Agency::create($request->validated());
        return response()->json($agency, 201);
    }

    public function update(AgencyRequest $request, Agency $agency)
    {
        $agency->update($request->validated());
        return response()->json($agency);
    }

    public function destroy(Agency $agency)
    {
        $agency->delete();
        return response()->json(null, 204);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $agency = Agency::where('user_id', $user->id)->first();
        if (!$agency) {
            abort(403, 'No agency found for this user.');
        }
        $totalClients = Client::where('agency_id', $agency->id)->count();
        $scheduledPosts = Post::where('agency_id', $agency->id)->where('status', 'scheduled')->count();
        $pendingApprovals = Post::where('agency_id', $agency->id)->where('status', 'pending')->count();
        $publishedToday = Post::where('agency_id', $agency->id)
            ->where('status', 'published')
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $posts = Post::with('client')
            ->where('agency_id', $agency->id)
            ->whereNotNull('scheduleDate') // Only include posts with a scheduled date
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'media' => $post->media,
                    'scheduleDate' => $post->scheduleDate ? \Carbon\Carbon::parse($post->scheduleDate)->toIso8601String() : null,
                    'platform' => $post->platform,
                    'postType' => $post->post_type, // Ensure this matches your database column
                    'status' => $post->status,
                    'feedback' => $post->feedback,
                    'client' => $post->client ? $post->client->company_name : null,
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            });
        $clients = Client::where('agency_id', $agency->id)->get(['id', 'company_name']);
        return Inertia::render('Agency/Dashboard', [
            'stats' => [
                [
                    'label' => 'Total Clients',
                    'value' => $totalClients,
                    'color' => 'blue',
                    'icon' => '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87M16 3.13a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"/></svg>',
                ],
                [
                    'label' => 'Scheduled Posts',
                    'value' => $scheduledPosts,
                    'color' => 'green',
                    'icon' => '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>',
                ],
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'color' => 'yellow',
                    'icon' => '<svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
                ],
                [
                    'label' => 'Published Today',
                    'value' => $publishedToday,
                    'color' => 'green',
                    'icon' => '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                ],
            ],
            'posts' => $posts,
            'clients' => $clients,
        ]);
    }

    public function clientStats()
    {
        $user = Auth::user();
        $agency = Agency::where('user_id', $user->id)->first();
        if (!$agency) {
            abort(403, 'No agency found for this user.');
        }
        $totalClients = Client::where('agency_id', $agency->id)->count();
        $activeClients = Client::where('agency_id', $agency->id)->where('status', 'active')->count();
        $pendingClients = Client::where('agency_id', $agency->id)->where('status', 'pending')->count();
        $inactiveClients = Client::where('agency_id', $agency->id)->where('status', 'inactive')->count();
        $clients = Client::with('user')->where('agency_id', $agency->id)->get()->map(function ($client) {
            $pendingPosts = $client->posts()->where('status', 'pending')->count();
            return [
                'name' => $client->user ? $client->user->name : '',
                'email' => $client->user ? $client->user->email : '',
                'status' => ucfirst($client->status),
                'pendingPosts' => $pendingPosts,
                'joined' => $client->created_at ? $client->created_at->toDateString() : '',
            ];
        });
        return Inertia::render('Agency/Client', [
            'stats' => [
                [
                    'label' => 'Total Clients',
                    'value' => $totalClients,
                    'color' => 'blue',
                    'icon' => '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87M16 3.13a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"/></svg>',
                ],
                [
                    'label' => 'Active',
                    'value' => $activeClients,
                    'color' => 'green',
                    'icon' => '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>',
                ],
                [
                    'label' => 'Pending',
                    'value' => $pendingClients,
                    'color' => 'yellow',
                    'icon' => '<svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
                ],
                [
                    'label' => 'Inactive',
                    'value' => $inactiveClients,
                    'color' => 'red',
                    'icon' => '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
                ],
            ],
            'clients' => $clients,
        ]);
    }

    public function storePost(Request $request)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'media' => 'nullable|array',
                'media.*' => 'nullable|string',
                'scheduleDate' => 'required|date',
                'platform' => 'required|string',
                'postType' => 'required|string',
                'client_id' => 'required|exists:clients,id',
                'status' => 'required|string',
                'feedback' => 'nullable|string',
            ]);

            $user = auth()->user();
            $agency = $user->agency;
            
            if (!$agency) {
                return response()->json([
                    'success' => false,
                    'message' => 'No agency found for this user.'
                ], 403);
            }

            $post = Post::create([
                'agency_id' => $agency->id,
                'client_id' => $validated['client_id'],
                'content' => $validated['content'],
                'media' => $validated['media'] ?? null,
                'scheduleDate' => $validated['scheduleDate'],
                'platform' => $validated['platform'],
                'post_type' => $validated['postType'],
                'status' => $validated['status'],
                'feedback' => $validated['feedback'] ?? null,
            ]);

            $post->load('client');

            return response()->json([
                'success' => true,
                'message' => 'Post scheduled successfully',
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'media' => $post->media,
                    'scheduleDate' => $post->scheduleDate ? \Carbon\Carbon::parse($post->scheduleDate)->toIso8601String() : null,
                    'platform' => $post->platform,
                    'postType' => $post->post_type,
                    'status' => $post->status,
                    'client' => $post->client ? $post->client->company_name : null,
                    'created_at' => $post->created_at->toIso8601String(),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 