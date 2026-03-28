<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index(Room $room)
    {
        $messages = $room->messages()->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $message = $room->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }
}
