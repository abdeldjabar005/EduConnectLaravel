<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::all();
        return CommentResource::collection($comments);

//        return response()->json($comments, 200);
    }

    public function store(Request $request, $postId)
    {
        $request->validate(['text' => 'required']);
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $postId,
            'text' => $request->text,
        ]);
        return new CommentResource($comment);

//        return response()->json($comment, 201);
    }

    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json($comment, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['text' => 'required']);

        $comment = Comment::findOrFail($id);
        $comment->update([
            'text' => $request->text,
        ]);

        return response()->json($comment, 200);
    }

   public function destroy($postId, $commentId)
{
    $comment = Comment::where('post_id', $postId)->findOrFail($commentId);

    // Check if the authenticated user is the owner of the comment or an admin
    if (auth()->id() !== $comment->user_id ) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $comment->delete();

    return response()->json(null, 204);
}

    public function comments($postId)
    {
        $post = Post::findOrFail($postId);
        $comments = $post->comments;

        return CommentResource::collection($comments);
    }
    public function comment( $commentId)
    {
        $comment = Comment::findOrFail($commentId);
        return new CommentResource($comment);
    }
}
