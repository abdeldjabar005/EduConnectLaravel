<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::all();
        return response()->json($comments, 200);
    }

    public function store(Request $request, $postId)
    {
        $request->validate(['text' => 'required']);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $postId,
            'text' => $request->text,
        ]);

        return response()->json($comment, 201);
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

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Check if the authenticated user is the owner of the comment or an admin && !auth()->user()->isAdmin()
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

        return response()->json($comments, 200);
    }
}
