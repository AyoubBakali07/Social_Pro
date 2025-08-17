<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StoreAgencyUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgencyInvitation;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalAgencies = Agency::count();
        $totalClients = Client::count();
        $activePosts = Post::where('status', 'published')->count();
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                [
                    'label' => 'Total Agencies',
                    'value' => $totalAgencies,
                    'description' => '+2 this month',
                    'color' => 'blue',
                    'icon' => '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                ],
                [
                    'label' => 'Total Clients',
                    'value' => $totalClients,
                    'description' => '+12 this week',
                    'color' => 'green',
                    'icon' => '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                ],
                [
                    'label' => 'Monthly Revenue',
                    'value' => '$12,450',
                    'description' => 'This month',
                    'color' => 'purple',
                    'icon' => '<svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                ],
                [
                    'label' => 'Active Posts',
                    'value' => $activePosts,
                    'description' => 'Currently active',
                    'color' => 'amber',
                    'icon' => '<svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>',
                ],
            ]
        ]);
    }

    public function agencies()
    {
        $totalAgencies = Agency::count();
        $active = Agency::where('status', 'Active')->count();
        $pending = Agency::where('status', 'Pending')->count();
        $suspended = Agency::where('status', 'Inactive')->count(); // Assuming 'Inactive' means suspended
        $agencies = Agency::withCount('clients')->get()->map(function ($agency) {
            return [
                'id' => $agency->id,
                'name' => $agency->company_name,
                'email' => $agency->user ? $agency->user->email : '',
                'clients' => $agency->clients_count,
                'status' => $agency->status,
                'created' => $agency->created_at ? $agency->created_at->toDateString() : '',
            ];
        });
        return Inertia::render('Admin/Agencies', [
            'stats' => [
                [
                    'label' => 'Total Agencies',
                    'value' => $totalAgencies,
                    'description' => '+2 this month',
                    'color' => 'blue',
                    'icon' => '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                ],
                [
                    'label' => 'Active',
                    'value' => $active,
                    'description' => 'Active agencies',
                    'color' => 'green',
                    'icon' => '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                ],
                [
                    'label' => 'Pending',
                    'value' => $pending,
                    'description' => 'Awaiting approval',
                    'color' => 'amber',
                    'icon' => '<svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                ],
                [
                    'label' => 'Suspended',
                    'value' => $suspended,
                    'description' => 'Requires attention',
                    'color' => 'red',
                    'icon' => '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                ],
            ],
            'agencies' => $agencies,
        ]);
    }

    public function deactivateAgency(Agency $agency)
    {
        // Set status to 'Inactive' if not already
        if ($agency->status !== 'Inactive') {
            $agency->status = 'Inactive';
            $agency->save();
        }

        // Redirect back to agencies page
        return redirect()->route('admin.agencies');
    }

    public function activateAgency(Agency $agency)
    {
        if ($agency->status !== 'Active') {
            $agency->status = 'Active';
            $agency->save();
        }

        return redirect()->route('admin.agencies');
    }

    /**
     * Invite a new agency via email authentication
     */
    public function storeAgency(StoreAgencyUserRequest $request)
    {
        try {
            // Create the user with a random temporary password
            $password = Str::random(32);
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role' => 'agency',
                'email_verified_at' => null,
            ]);

            // Create the agency record
            $agency = Agency::create([
                'user_id' => $newUser->id,
                'company_name' => $request->company_name,
                'status' => 'Pending',
            ]);

            // Generate a password setup token
            $token = app('auth.password.broker')->createToken($newUser);

            // Send the invitation email
            Notification::send($newUser, new AgencyInvitation($token, config('app.name')));

            return redirect()->route('admin.agencies')->with([
                'message' => 'Agency invited successfully. They will receive an email to set their password.',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'An error occurred while creating the agency. Please try again.'
            ]);
        }
    }
}