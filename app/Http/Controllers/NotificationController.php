<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $authUser = $request->user();
            $notifications = $authUser->notifications()->with('sender:id,full_name,image')->get();

            return response()->json(['notifications' => $notifications], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $authUser = $request->user();
        try {
            Notification::where('is_read', false)->Where('receiver_id', $authUser->id)->update([
                'is_read' => true,
            ]);

            return response()->json(['message' => 'all notifications marked successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $authUser = $request->user();
        try {
            Notification::where('receiver_id', $authUser->id)->delete();

            return response()->json(['message' => 'all notifications cleared successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
