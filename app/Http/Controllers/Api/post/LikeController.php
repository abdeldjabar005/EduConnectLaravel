<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, $postId)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'post_id' => $postId,
        ]);

        if (!$like->wasRecentlyCreated) {
            $like->delete();
            return response()->json(['message' => 'like deleted'], 204);
        }

        return response()->json($like, 201);
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
}
