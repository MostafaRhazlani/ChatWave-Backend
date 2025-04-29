<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {

    }

    public function toggleSavePost(Request $request, $postId) {
        $authUser = $request->user();

        try {
            $authUser->savedPosts()->toggle($postId);
            $isSaved = $authUser->savedPosts()->where('post_id', $postId)->exists();

            return response()->json([
                'message' => $isSaved ? 'post saved successfully' : 'post unsaved successfully',
                'isSaved' => $isSaved
            ], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
