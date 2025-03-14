<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validated = $request->validate([
            'full_name' => 'required|max:40',
            'username' => 'required|max:40',
            'email' => 'required|email|unique:persons,email',
            'nationality' => 'required|max:20',
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
}
