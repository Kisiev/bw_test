<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
	public function index(LoginRequest $request)
	{
		$name = $request->get('name');
		$user = User::whereName($name)->first();

		if (Hash::check($request->get('password'), $user->password)){
			Auth::login($user);
			return response()->json(['user' => $user]);
		}
		return response()->json(['message' => 'Неверный пароль'], 422);
	}
}
