<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpCode;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware();
    }

    public function index()
    {
        try {
            /* TODO paginate or search or filter and use resource */

            if (request()->has('filter') && request('filter') == 'trash') {
                $users = User::onlyTrashed()->get();
            } elseif (request('filter') == 'all') {
                $users = User::withTrashed()->get();
            } else {
                $users = User::all();
            }

            return $this->successRes(UserResource::collection($users->load('tags')), HttpCode::HTTP_OK, 'users returned.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function show($uuid)
    {
        try {
            $user = User::withTrashed()->where('uuid', $uuid)->first();

            return $user
                ? $this->successRes(new UserResource($user->load('quests', 'tags', 'answers')), HttpCode::HTTP_OK, 'users returned.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'users not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:3', Rule::unique('users', 'name')],
            'email' => ['required', 'min:3', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirm' => ['required', 'min:8'],
            'score' => ['integer']
        ]);

        if ($validator->fails())
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'update validation is fail.');

        try {
            $user = User::create([
                'uuid' => generateUuid(),
                'name' => $request->name,
                'password' => $request->password,
                'email' => $request->email,
                'score' => $request->score,
                'type' => $request->type,
            ]);

            $user->tags()->sync($request->input('tags'));

            return $this->successRes(new UserResource($user->load('quests', 'tags', 'answers')), HttpCode::HTTP_OK, 'user update successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:3', Rule::unique('users', 'name')->ignore($uuid, 'uuid')],
            'email' => ['required', 'min:3', Rule::unique('users', 'email')->ignore($uuid, 'uuid')],
            'password' => ['sometimes', 'required', 'min:8', 'confirmed'],
            'password_confirm' => ['required_if_accepted:password', 'min:8'],
            'score' => ['sometimes', 'integer']
        ]);

        if ($validator->fails())
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'update validation is fail.');

        try {
            $user = User::where('uuid', $uuid)->first();

            if (!$user)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'user not found');

            $user->update($request->all());

            $user->tags()->sync($request->input('tags'));

            return $this->successRes(new UserResource($user->load('quests', 'tags', 'answers')), HttpCode::HTTP_OK, 'user update successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function delete($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->delete();

            return $user
                ? $this->successRes('', HttpCode::HTTP_OK, 'user deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'user not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function destroy($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->forceDelete();

            return $user
                ? $this->successRes('', HttpCode::HTTP_OK, 'user hard deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'user not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function restore($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->restore();

            return $user
                ? $this->successRes(new UserResource($user->load('quests', 'tags', 'answers')), HttpCode::HTTP_OK, 'user restore successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'user not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function clean()
    {
        try {
            $trashUsers = User::onlyTrashed()->get();

            if (!$trashUsers) {
                foreach ($trashUsers as $itemTrash) {
                    $itemTrash->tags()->delete();
                    $itemTrash->forceDelete();
                }
            }

            return $trashUsers
                ? $this->successRes('', HttpCode::HTTP_OK, 'trash clean successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'user not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }
}
