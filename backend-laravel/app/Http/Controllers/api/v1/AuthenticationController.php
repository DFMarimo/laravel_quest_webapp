<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as HttpCode;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'password' => ['required', 'min:8'],
        ]);

        if ($validator->fails())
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validator incorrect');

        $user = User::where('email', $request->email)->first();

        if (!$user)
            return $this->errorRes('user not exists', HttpCode::HTTP_UNAUTHORIZED, 'data is unauthorized');

        if (!Hash::check($request->password, $user->password))
            return $this->errorRes('password incorrect', HttpCode::HTTP_UNAUTHORIZED, 'data is unauthorized');

        $token = $user->createToken('myApp')->plainTextToken;

        return $this->successRes(['user' => $user, 'token' => $token], HttpCode::HTTP_OK, 'user login successfully');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required'],
            'password' => ['required', 'min:8'],
            'password_confirm' => ['required', 'min:8', 'same:password_confirm'],
            'address' => ['required'],
            'cellphone' => ['required'],
            'city_id' => ['required'],
            'province_id' => ['required'],
        ]);

        if ($validator->fails())
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validator incorrect');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('myApp')->plainTextToken;

        return $this->successRes(['user' => $user, 'token' => $token], HttpCode::HTTP_CREATED, 'user register successfully');
    }

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
        return $this->successRes('logged out', HttpCode::HTTP_OK, 'user logout successfully');
    }
    // public function (){}
}
