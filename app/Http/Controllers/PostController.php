<?php

namespace App\Http\Controllers;

use App\Jobs\BroadcastPostCreatedNotification;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();

        if($user->role === 'admin') {
            $posts = Post::with(['person'])
                ->withCount('comments', 'likes')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $followerIds = $user->followers()->pluck('person_id')->toArray();
            $followingIds = $user->following()->pluck('followed_person_id')->toArray();

            $personIds = array_unique(array_merge($followerIds, $followingIds));
            $posts = Post::where(function ($query) use ($personIds, $user) {
                $query->whereIn('person_id', $personIds)
                       ->orWhere('person_id', $user->id);
            })->where('is_banned', false)
              ->with(['person', 'tags', 'latestThreeComments'])
              ->withCount('comments', 'likes', 'savedByUsers')
              ->orderBy('created_at', 'desc')
              ->get();

            $posts->each(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('person_id', $user->id)->exists();
                $post->is_saved = $post->savedByUsers()->where('person_id', $user->id)->exists();
            });
        }
        return response()->json(['posts' => $posts], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'content' => 'required',
            'media' => 'required|file',
            'type' => 'required|in:image,video',
            'tags' => 'required'
        ]);

        try {

            // Check if a file is uploaded
            if (!$request->hasFile('media')) {
                return response()->json(['message' => 'No file received'], 400);
            }

            $file = $request->file('media');
            $fileExtension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            $fileType = strtolower($fileExtension);

            $post = new Post();
            $post->content = $validated['content'];
            $post->person_id = $authUser->id;
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

            if($authUser->id === $latestPost->person_id) {
                $followerIds = $authUser->followers()->pluck('person_id')->toArray();
                $followingIds = $authUser->following()->pluck('followed_person_id')->toArray();

                $friends = array_unique(array_merge($followerIds, $followingIds));

                foreach($friends as $friend_id) {
                    $notification = Notification::create([
                        'receiver_id' => $friend_id,
                        'sender_id' => $authUser->id,
                        'type' => 'post',
                        'content' => "You friend {$authUser->full_name} added a new post",
                    ]);

                    $notification->load('sender:id,full_name,image');
                    dispatch(new BroadcastPostCreatedNotification($notification, $friend_id));
                }
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
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $post = Post::where('id', $id)->with(['person', 'tags', 'comments.person'])->withCount('comments', 'likes', 'savedByUsers')->first();
        $post->is_liked = $post->likes()->where('person_id', $user->id)->exists();
        $post->is_saved = $post->savedByUsers()->where('person_id', $user->id)->exists();
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

    public function searchPosts(Request $request) {
        try {
            $query = $request->query('query');
            $posts = Post::whereHas('person', function ($q) use ($query) {
                $q->where('full_name', 'ILIKE', "%$query%");
            })->with(['person'])->get();

            return response()->json(['posts' => $posts], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function toggleStatusPost($postId) {

        try {
            $post = Post::find($postId);
            $post->is_banned = !$post->is_banned;
            $post->save();

            return response()->json(['message' => $post->is_banned ? 'post stopped successfully' : 'user published successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function totalPosts() {
        try {
            $postsCount = Post::count();
            $totalPostsInWeek = Post::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();

            return response()->json(['postsCount' => $postsCount, 'totalPostsInWeek' => $totalPostsInWeek], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
