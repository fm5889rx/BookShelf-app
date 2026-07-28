<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $notifications = Notification::all();

        return view('notifications.index', ['notifications' => $notifications]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function read(string $id)
    {
        $notification = Notification::findOrFail($id);

        $notification->notifiable_type = ReadingPlanStatus::Active;
        $notification->save();

        return view('notifications.index');
    }
}
