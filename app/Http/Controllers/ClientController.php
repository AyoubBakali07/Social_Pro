<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Post;
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
        return Inertia::render('Client/Dashboard', [
            'stats' => [
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'color' => 'text-black',
                    'icon' => "<svg class='w-6 h-6 text-blue-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'/></svg>",
                ],
                [
                    'label' => 'Approved This Month',
                    'value' => $approvedThisMonth,
                    'color' => 'text-green-600',
                    'icon' => "<svg class='w-6 h-6 text-green-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>",
                ],
                [
                    'label' => 'Upcoming Posts',
                    'value' => $upcomingPosts,
                    'color' => 'text-blue-600',
                    'icon' => "<svg class='w-6 h-6 text-yellow-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>",
                ],
                [
                    'label' => 'Rejected',
                    'value' => $rejected,
                    'color' => 'text-red-600',
                    'icon' => "<svg class='w-6 h-6 text-red-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10'/><path d='M15 9l-6 6m0-6l6 6'/></svg>",
                ],
            ]
        ]);
    }
} 