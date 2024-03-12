<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $like = CommentLike::create([
            'user_id' => auth()->id(),
            'comment_id' => $comment->id,
        ]);

        return response()->json($like, 201);
    }

    public function destroy(Comment $comment)
    {
        $like = CommentLike::where('user_id', auth()->id())
            ->where('comment_id', $comment->id)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => 'Like not found'], 404);
    }
}
