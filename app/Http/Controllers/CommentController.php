<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Comment;
use App\Events\CommentAdded;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Jobs\BroadcastCommentNotification;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

            $comment->load('person');
            $postAuthorId = $comment->post->person_id;

            if($postAuthorId !== $comment->person_id) {
                $notification = Notification::create([
                    'receiver_id' => $postAuthorId,
                    'sender_id' => $comment->person_id,
                    'type' => 'comment',
                    'content' => "{$comment->person->full_name} added a comment to your post",
                ]);

                $notification->load('sender:id,full_name,image');

                dispatch(new BroadcastCommentNotification($notification, $postAuthorId));
            }

            return response()->json([
                'message' => 'comment created successfully',
                'comment' => $comment,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
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

    public function totalComments() {
        try {
            $commentsCount = Comment::count();
            $totalCommentsInWeek = Comment::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();

            return response()->json(['commentsCount' => $commentsCount, 'totalCommentsInWeek' => $totalCommentsInWeek], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
