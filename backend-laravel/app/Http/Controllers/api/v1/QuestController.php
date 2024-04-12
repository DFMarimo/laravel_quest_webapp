<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestResource;
use App\Models\Channel;
use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestController extends Controller
{
    public function index()
    {
        try {
            /* TODO paginate or search or filter and use resource */

            if (request()->has('filter') && request('filter') == 'trash') {
                $quests = Quest::onlyTrashed()->paginate(20);
            } elseif (request('filter') == 'all') {
                $quests = Quest::withTrashed()->paginate(20);
            } else {
                $quests = Quest::all();
            }

            /*QuestResource::collection($quests)*/

            return $this->successRes(QuestResource::collection($quests->load(['tags' ,'author' ,'channel'])), HttpCode::HTTP_OK, 'all quests returned.');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function show($uuid)
    {
        try {
            /* TODO return parent and child */
            $quest = Quest::where('uuid', $uuid)->first();

            return $quest
                ? $this->successRes(new QuestResource($quest->load('tags' ,'author')), HttpCode::HTTP_OK, 'channel returned.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => ['required'],
            'title' => ['required', 'min:3'],
            'body' => ['required', 'min:3'],
            'tag.*' => ['sometimes', Rule::exists('tags', 'uuid')],
            // 'author_id' => ['required', Rule::exists('users', 'uuid')],
        ]);
        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validation is fail.');
        }

        try {
            DB::beginTransaction();
            $isExistChannel = Channel::where('uuid', $request->channel_id)->first();
            if (!$isExistChannel)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

            $quest = Quest::create([
                'uuid' => generateUuid(),
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'author_id' => $request->input('author'),
                // 'author' => Auth::user()->uuid,
                'is_active' => $request->input('is_active', false),
                'channel_id' => $request->input('channel_id')
            ]);

            $quest->tags()->sync($request->input('tag'));

            DB::commit();
            return $this->successRes(new QuestResource($quest->load('tags' ,'author')), HttpCode::HTTP_CREATED, 'create quest successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => ['required'],
            'title' => ['required', 'min:3'],
            'body' => ['required', 'min:3'],
            'tags.*' => ['sometimes', Rule::exists('tags', 'uuid')],
            // 'author' => ['required', Rule::exists('users', 'uuid')],
        ]);
        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validation is fail.');
        }

        try {
            DB::beginTransaction();
            $quest = Quest::where('uuid', $request->uuid)->first();
            if (!$quest)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'quest not found');

            $isExistChannel = Channel::where('uuid', $request->channel_id)->first();
            if (!$isExistChannel)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

            $quest = $quest->tags()->update($request->all());

            $quest->sync($request->input('tags'));

            DB::commit();
            return $this->successRes(new QuestResource($quest->load('tags' ,'author')), HttpCode::HTTP_CREATED, 'update quest successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function delete($uuid)
    {
        try {
            $quest = Quest::where('uuid', $uuid)->delete();

            return $quest
                ? $this->successRes('', HttpCode::HTTP_OK, 'quest deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'quest not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function restore($uuid)
    {
        try {
            $quest = Quest::where('uuid', $uuid)->restore();

            return $quest
                ? $this->successRes(new QuestResource($quest->load('tags' ,'author')), HttpCode::HTTP_OK, 'quest restore successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'quest not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function clean()
    {
        try {
            $trashQuests = Quest::onlyTrashed()->get();

            if (!$trashQuests) {
                foreach ($trashQuests as $itemTrash) {
                    $itemTrash->tags()->delete();
                    $itemTrash->forceDelete();
                }
            }

            return $trashQuests
                ? $this->successRes('', HttpCode::HTTP_OK, 'trash clean successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function destroy($uuid)
    {
        try {
            $quest = Quest::where('uuid', $uuid)->first();
            $quest->tags()->delete();
            $quest->forceDelete();

            return $quest
                ? $this->successRes('', HttpCode::HTTP_OK, 'quest hard deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'quest not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }
}
