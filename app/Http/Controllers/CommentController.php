<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        try {
            $post = Post::find($id);
            $commentsPost = $post->comments()->get();

            return response()->json(['commentsPost' => $commentsPost], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'comment' => 'required',
        ]);

        try {
            $comment = new Comment();
            $comment->post_id = $request->post_id;
            $comment->person_id = $request->person_id;
            $comment->comment = $validated['comment'];
            $comment->save();
            return response()->json(['message' => 'comment created successfully','comment' => $comment], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $comment = Comment::find($id);
        return response()->json(['comment' => $comment], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'comment' => 'required',
        ]);

        try {
            $comment = Comment::find($id);
            $comment->comment = $validated['comment'];
            $comment->save();
            return response()->json(['message' => 'comment updated successfully','comment' => $comment], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Comment::destroy($id);
            return response()->json(['message' => 'comment deleted successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
