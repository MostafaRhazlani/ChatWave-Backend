<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::orderBy('created_at', 'desc')->get();
        return response()->json(['tags' => $tags]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_name' => 'required',
        ]);

        try {
            $tag = new Tag();
            $tag->tag_name = $validated['tag_name'];
            $tag->save();

            return response()->json(['message' => 'tag created successfully', 'tag' => $tag], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tagId)
    {
        try {
            $tag = Tag::select('id', 'tag_name')->where('id', $tagId)->first();
            return response()->json(['tag' => $tag], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $tagId)
    {
        $validated = $request->validate([
            'tag_name' => 'required',
        ]);

        try {
            $tag = Tag::find($tagId);
            $tag->tag_name = $validated['tag_name'];
            $tag->save();

            return response()->json(['message' => 'tag updated successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tagId)
    {
        try {
            Tag::destroy($tagId);
            return response()->json(['message' => 'tag deleted successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
