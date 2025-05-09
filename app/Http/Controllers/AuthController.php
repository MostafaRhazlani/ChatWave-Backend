<?php

namespace App\Http\Controllers;

use App\Jobs\BroadcastUserStatusChanged;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validated = $request->validate([
            'full_name' => 'required|max:40',
            'username' => 'required|max:40',
            'email' => 'required|email|unique:persons,email',
            'nationality' => 'required',
            'date_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'password' => 'required|min:8|max:16',
            'password_confirmation' => 'required|same:password',
        ]);

        try {
            $user = new Person();
            $user->full_name = $validated['full_name'];
            $user->username = $validated['username'];
            $user->email = $validated['email'];
            $user->nationality = $validated['nationality'];
            $user->date_birth = $validated['date_birth'];
            $user->gender = $validated['gender'];
            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json(['sucess' => 'user created successfully'], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Person::where('email', $validated['email'])->first();

        if(!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => ['incorrectInput' => 'Email or password not correct']],401);
        }

        if($user->is_banned === true) {
            return response()->json(['message' => [ 'userBan' => 'Your account is banned, you can\'t login now']],401);
        }

        $token = Str::random(60);
        $user->token = hash('sha256', $token);
        $user->is_logged = true;
        $user->save();

        dispatch(new BroadcastUserStatusChanged($user->id, true));

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function logout(Request $request) {
        $authUser = $request->user();

        $authUser->update([
            'token' => null,
            'is_logged' => false,
        ]);

        dispatch(new BroadcastUserStatusChanged($authUser->id, false));

        return response()->json(['message' => 'user logged successfully'], 200);
    }
}
