<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string|confirmed|min:5|max:255',
            'password_confirmation' => 'required|min:5|max:255',
        ]);

        $user = User::where('id', auth()->user()->id)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
