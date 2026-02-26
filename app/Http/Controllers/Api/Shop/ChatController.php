<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * GET /api/orders/{id}/chat
     * Get chat history for an order
     */
    public function index(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Check if user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = DB::table('messages')
            ->where('order_id', $id)
            ->orderBy('created_at', 'asc') // Oldest first
            ->get()
            ->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'text' => $msg->message,
                    'is_me' => $msg->sender_type === 'user', // True if I sent it
                    'time' => \Carbon\Carbon::parse($msg->created_at)->format('g:i A'), // "9:02 PM"
                ];
            });

        return response()->json([
            'driver_name' => $order->driver ? $order->driver->name : 'Driver',
            'driver_avatar' => $order->driver ? $order->driver->avatar_url : null,
            'messages' => $messages
        ]);
    }

    /**
     * POST /api/orders/{id}/chat
     * Send a message to the driver
     */
    public function store(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $order = Order::find($id);

        if (!$order) return response()->json(['message' => 'Order not found'], 404);

        DB::table('messages')->insert([
            'order_id' => $id,
            'user_id' => $request->user()->id,
            'driver_id' => $order->driver_id,
            'message' => $request->message,
            'sender_type' => 'user',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Message sent']);
    }
}