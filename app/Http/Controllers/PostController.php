<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Person;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();

        $followerIds = $user->followers()->pluck('person_id')->toArray();
        $followingIds = $user->following()->pluck('followed_person_id')->toArray();

        $personIds = array_unique(array_merge($followerIds, $followingIds));
        $posts = Post::whereIn('person_id', $personIds)
                ->orWhere('person_id', $user->id)
                ->with(['person', 'tags', 'latestThreeComments'])
                ->withCount('comments')
                ->orderBy('created_at', 'desc')
                ->get();

        return response()->json(['posts' => $posts], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $validated = $request->validate([
            'content' => 'required',
            'media' => 'required|file',
            'type' => 'required|in:image,video',
            'tags' => 'required'
        ]);

        try {
            $user = Person::where('token', hash('sha256', $token))->first();

            // Check if a file is uploaded
            if (!$request->hasFile('media')) {
                return response()->json(['message' => 'No file received'], 400);
            }

            $file = $request->file('media');
            $fileExtension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            $fileType = strtolower($fileExtension);

            $post = new Post();
            $post->content = $validated['content'];
            $post->person_id = $user->id;
            $post->type = $validated['type'];


            if($validated['type'] === 'image') {

                if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'webp'])) {
                    return response()->json(['message' => 'Invalid image file'], 400);
                }

                $image = $request->file('media');
                $uniqueImage = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('posts/images', $uniqueImage, 'public');
                $post->media = $uniqueImage;
            } elseif($validated['type'] === 'video') {
                if (!in_array($fileType, ['mp4'])) {
                    return response()->json(['message' => 'Invalid video file'], 400);
                }

                $video = $request->file('media');
                $uniqueName = uniqid() . '.' . $video->getClientOriginalExtension();
                $video->storeAs('posts/videos', $uniqueName, 'public');
                $post->media = $uniqueName;
            }

            $post->save();

            if(count(json_decode($request->tags)) > 0) {
                $latestPost = Post::find($post->id);
                $latestPost->tags()->syncWithoutDetaching(json_decode($request->tags));
            }
            return response()->json(['message' => 'Post created successfully', 'post' => $post], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
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
    public function show($id)
    {
        $post = Post::where('id', $id)->with(['person', 'tags', 'comments.person'])->first();
        return response()->json(['post' => $post], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $post = Post::where('id', $id)->with(['person', 'tags'])->first();
        return response()->json(['post' => $post], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $validated = $request->validate([
            'content' => 'required',
            'media' => 'sometimes|file',
            'type' => 'required|in:image,video',
            'tags' => 'required'
        ]);

        try {
            $user = Person::where('token', hash('sha256', $token))->first();
            if(!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $post = Post::find($id);
            if(!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }

            $post->content = $validated['content'];
            $post->type = $validated['type'];

            // Check if a file is uploaded
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $fileExtension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $fileType = strtolower($fileExtension);


                if($validated['type'] === 'image') {

                    if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'webp'])) {
                        return response()->json(['message' => 'Invalid image file'], 400);
                    }

                    $image = $request->file('media');
                    $uniqueImage = uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('posts/images', $uniqueImage, 'public');
                    $post->media = $uniqueImage;
                } elseif($validated['type'] === 'video') {
                    if (!in_array($fileType, ['mp4'])) {
                        return response()->json(['message' => 'Invalid video file'], 400);
                    }

                    $video = $request->file('media');
                    $uniqueName = uniqid() . '.' . $video->getClientOriginalExtension();
                    $video->storeAs('posts/videos', $uniqueName, 'public');
                    $post->media = $uniqueName;
                }
            }

            $post->save();

            if(count(json_decode($request->tags)) > 0) {
                $post->tags()->sync(json_decode($request->tags));
            }
            return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $post = Post::find($id);
            $post->tags()->detach();
            $post->delete();

            return response()->json(['message' => 'Post deleted successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
