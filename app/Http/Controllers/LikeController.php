<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $validated = $request->validate([
            'post_id' => 'required|numeric',
        ]);

        $likeExists = Like::where('person_id', $userId)->where('post_id', $validated['post_id'])->exists();

        if ($likeExists) {
            return response()->json(['message' => 'Already liked'], 409);
        }


        $userId = $request->user()->id;
        try {
            $like = new Like();
            $like->person_id = $userId;
            $like->post_id = $validated['post_id'];
            $like->save();
            return response()->json(['message' => 'like added successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function likesCount(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|numeric',
        ]);

        $userId = $request->user()->id;

        try {
            $like = Like::where('person_id', $userId)->where('post_id', $validated['post_id'])->count();
            return response()->json(['like_count' => $like], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|numeric',
        ]);

        $userId = $request->user()->id;

        try {
            $like = Like::where('person_id', $userId)->where('post_id', $validated['post_id'])->first();

            if(!$like) {
                return response()->json(['message' => 'something is wrong']);
            }
            $like->destroy($like->id);
            return response()->json(['message' => 'like removed successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
