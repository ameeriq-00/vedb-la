<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }
    
    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'تم تحديد الإشعار كمقروء');
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة');
    }
}