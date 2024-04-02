<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
 public function store(Request $request, $postId)
{
    $post = Post::find($postId);

    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    $like = Like::firstOrCreate([
        'user_id' => auth()->id(),
        'post_id' => $postId,
    ]);

    $isLiked = true;

    if (!$like->wasRecentlyCreated) {
        $like->delete();
        $isLiked = false;
    }

    return response()->json(['postId' => $postId, 'isLiked' => $isLiked]);
}
    public function destroy($postId)
    {
        $like = Like::where('post_id', $postId)
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'like deleted'], 204);
        }

        return response()->json(['message' => 'Like not found'], 404);
    }

    public function isLiked($postId)
    {
        $isLiked = Like::where('post_id', $postId)
            ->where('user_id', auth()->id())
            ->exists();

//        return response()->json(['isLiked' => $isLiked]);
        return response()->json(['postId' => $postId, 'isLiked' => $isLiked]);

    }
}
