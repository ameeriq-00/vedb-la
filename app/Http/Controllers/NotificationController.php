<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Automatically mark old notifications as read
        $user->unreadNotifications()
             ->where('created_at', '<', Carbon::now()->subWeek())
             ->update(['read_at' => Carbon::now()]);

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
            return response()->json([
                'success' => true,
                'unreadCount' => Auth::user()->unreadNotifications()->count()
            ]);
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
            return response()->json(['success' => true, 'unreadCount' => 0]);
        }

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'unreadCount' => Auth::user()->unreadNotifications()->count()
            ]);
        }

        return back()->with('success', 'تم حذف الإشعار بنجاح');
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request)
    {
        Auth::user()->notifications()->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'تم حذف جميع الإشعارات بنجاح');
    }
}
