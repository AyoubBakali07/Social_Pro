<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Requests\NotificationRequest;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::all();
    }

    public function show(Notification $notification)
    {
        return $notification;
    }

    public function store(NotificationRequest $request)
    {
        $notification = Notification::create($request->validated());
        return response()->json($notification, 201);
    }

    public function update(NotificationRequest $request, Notification $notification)
    {
        $notification->update($request->validated());
        return response()->json($notification);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(null, 204);
    }
} 