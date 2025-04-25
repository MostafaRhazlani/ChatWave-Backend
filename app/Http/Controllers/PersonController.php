<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        $blockedUser = $authUser->usersBlocked()->pluck('blocked_id')->toArray();
        $blockedBy = $authUser->blockedByUsers()->pluck('blocker_id')->toArray();

        $usersBlockedIds = array_unique(array_merge($blockedUser, $blockedBy));

        $randomUsers = Person::whereNotIn('id', $usersBlockedIds)->inRandomOrder()->limit(3)->get();
        return response()->json(['randomUsers' => $randomUsers]);
    }

    public function checkUserAuth(Request $request) {
        $user = $request->user();

        return response()->json(['user' => $user]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $userInfo = Person::with('posts')->withCount(['followers', 'following'])->find($id);
            return response()->json(['userInfo' => $userInfo], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $validated = $request->validate([
            'username' => 'required|max:20|min:5',
            'full_name' => 'required|min:6',
            'email' => 'required|email',
            'description' => 'max:255',
            'date_birth' => 'required|',
            'nationality' => 'required',
            'gender' => 'required|in:male,female',
            'relationship' => 'required',
        ]);

        try {
            $user = Person::where('token', hash('sha256', $token))->first();
            $user->update($validated);

            return response()->json(['message' => 'user updated successfully', 'user' => $user], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateImageProfile(Request $request) {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $validated = $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        if(!$request->hasFile('image')) {
            return response()->json(['message' => 'No image recieved'], 400);
        }

        $user = Person::where('token', hash('sha256', $token))->first();

        try {

            $image = $request->file('image');
            $uniqueImage = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('images', $uniqueImage, 'public');
            $user->update([ 'image' => $uniqueImage]);
            return response()->json(['message' => 'image updated successfully', 'image' => $uniqueImage], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function changePassword(Request $request) {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|max:16',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Person::where('token', hash('sha256', $token))->first();
        if(!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        try {

            // check if current password is same password in database
            if(!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['errors' => ['current_password' => ['current password not correct']]], 400);
            }

            // check if new password is same of old password
            if(Hash::check($validated['new_password'], $user->password)) {
                return response()->json(['errors' => ['new_password' => ['you can\'t set same old password']]], 400);
            }

            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);
            return response()->json(['message' => 'password updated succesfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        //
    }

    public function toggleUserBlock(Request $request, $id) {
        $authUser = $request->user();

        try {
            $authUser->usersBlocked()->toggle($id);
            $isBlockedHim = $authUser->usersBlocked()->where('blocked_id', $id)->exists();
            if($isBlockedHim === true) {
                $authUser->following()->detach($id);
            }
            return response()->json(['isBlockedHim' => $isBlockedHim], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function blockStatus(Request $request, $userId) {
        $authUser = $request->user();

        try {
            $isBlockedHim = $authUser->usersBlocked()->where('blocked_id', $userId)->exists();

            return response()->json(['isBlockedHim' => $isBlockedHim], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function followStatus(Request $request, $userId) {
        $authUser = $request->user();

        try {
            $isFollowHim = $authUser->following()->where('followed_person_id', $userId)->exists();
            $isFollowMe = $authUser->followers()->where('person_id', $userId)->exists();

            return response()->json([
                'isFollowHim' => $isFollowHim,
                'isFollowMe' => $isFollowMe,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function toggleFollow(Request $request, $userId) {
        $authUser = $request->user();

        try {

            $authUser->following()->toggle($userId);
            $isFollowHim = $authUser->following()->where('followed_person_id', $userId)->exists();
            return response()->json([
                'isFollowHim' => $isFollowHim,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function getAllNotFollowBack(Request $request) {
        $user = $request->user();

        try {
            $followers = $user->followers()->pluck('person_id');
            $following = $user->following()->pluck('followed_person_id');

            $notFollowBack = $followers->diff($following);

            $usersNotFollowBack = Person::whereIn('id', $notFollowBack)->select('id', 'full_name', 'username', 'image')->get();
            return response()->json(['usersNotFollowBack' => $usersNotFollowBack]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function searchUser(Request $request) {
        $authUser = $request->user();
        $query = $request->query('query');

        $blockedUser = $authUser->usersBlocked()->pluck('blocked_id')->toArray();
        $blockedBy = $authUser->blockedByUsers()->pluck('blocker_id')->toArray();

        $usersBlockedIds = array_unique(array_merge($blockedUser, $blockedBy));
        $users = Person::whereNotIn('id', $usersBlockedIds)->where(function ($q) use ($query) {
            $q->where('full_name', 'ILIKE', "%$query%")
              ->orWhere('username', 'ILIKE', "%$query%");
        })->get();

        return response()->json(['users' => $users]);
    }
}
