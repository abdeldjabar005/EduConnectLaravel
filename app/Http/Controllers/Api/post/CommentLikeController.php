<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
   public function store(Request $request, $commentId)
{
    $comment = Comment::find($commentId);

    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }

    $like = CommentLike::firstOrCreate([
        'user_id' => auth()->id(),
        'comment_id' => $commentId,
    ]);

    $isLiked = true;

    if (!$like->wasRecentlyCreated) {
        $like->delete();
        $isLiked = false;
    }

    return response()->json(['commentId' => $commentId, 'isLiked' => $isLiked]);
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
