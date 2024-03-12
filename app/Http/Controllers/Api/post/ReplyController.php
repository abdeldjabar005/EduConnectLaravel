<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Http\Request;


class ReplyController extends Controller
{
    public function index(Comment $comment)
    {
        $replies = $comment->replies;
        return response()->json($replies, 200);
    }

    public function show(Comment $comment, Reply $reply)
    {
        return response()->json($reply, 200);
    }

    public function store(Request $request, Comment $comment)
    {
        $request->validate([
            'text' => 'required',
        ]);

        $reply = Reply::create([
            'user_id' => auth()->id(),
            'comment_id' => $comment->id,
            'text' => $request->text,
        ]);

        return response()->json($reply, 201);
    }

    public function update(Request $request, Reply $reply)
    {
        $request->validate([
            'text' => 'required',
        ]);

        if ($reply->user_id != auth()->id()) {
            return response()->json(['error' => 'You can only update your own replies'], 403);
        }

        $reply->text = $request->text;
        $reply->save();

        return response()->json($reply, 200);
    }

    public function destroy(Comment $comment, Reply $reply)
    {
        if ($reply->user_id != auth()->id()) {
            return response()->json(['error' => 'You can only delete your own replies'], 403);
        }

        $reply->delete();

        return response()->json(null, 204);
    }
}
