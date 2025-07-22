<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use App\Http\Requests\AgencyRequest;
use App\Http\Requests\PostRequest;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function dashboard(Request $request)
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
        $rejectedPosts = Post::where('agency_id', $agency->id)->where('status', 'rejected')->count();
        $posts = Post::where('agency_id', $agency->id)
            ->with('client') // Eager load client
            ->get()
            ->map(function ($post) {
                // Ensure media is an array of full URLs with forward slashes
                $mediaUrls = collect($post->media)
                    ->filter()
                    ->map(function ($path) {
                        $fixedPath = str_replace('\\', '/', $path);
                        return Storage::url($fixedPath);
                    })->all();

                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'scheduleDate' => $post->scheduleDate->toIso8601String(),
                    'platform' => $post->platform,
                    'postType' => $post->postType,
                    'status' => $post->status,
                    'feedback' => $post->feedback,
                    'client' => $post->client->company_name,
                    'media' => $mediaUrls, // Use the new array of full URLs
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            });

        // Get a list of clients for the schedule post form
        $clients = Client::where('agency_id', $agency->id)->select('id', 'company_name')->get();

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
                [
                    'label' => 'Rejected Posts',
                    'value' => $rejectedPosts,
                    'color' => 'red',
                    'icon' => '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                ],
            ],
            'posts' => $posts,
            'clients' => $clients,
        ]);
    }

    public function clientStats(Request $request)
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

    public function storePost(PostRequest $request)
    {
        $validated = $request->validated();

        $agency = $request->user()->agency;
        if (!$agency) {
            return redirect()->back()->with('error', 'User is not associated with an agency.');
        }

        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('posts', 'public');
            }
        }

        $post = Post::create(array_merge($validated, [
            'agency_id' => $agency->id,
            'media' => $mediaPaths,
            'status' => 'pending',
        ]));

        $post->load('client');

        // Instead of returning JSON, redirect back with a success message
        return redirect()->back()->with('success', 'Post scheduled successfully!');
    }

    public function destroyPost(Post $post)
    {
        $post->delete();
        return redirect()->back()->with('success', 'Post deleted successfully!');
    }
} 