<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpCode;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChannelController extends Controller
{

    public function __construct()
    {
        // $this->middleware('');
    }

    public function index()
    {
        try {
            /* TODO paginate or search or filter and use resource */

            if (request()->has('filter') && request('filter') == 'trash') {
                $channels = Channel::onlyTrashed()->get();
            } elseif (request('filter') == 'all') {
                $channels = Channel::withTrashed()->get();
            } else {
                $channels = Channel::all();
            }

            return $this->successRes($channels, HttpCode::HTTP_OK, 'all channel returned.');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function show($uuid)
    {
        try {
            /* TODO return parent and child */
            $channel = Channel::withTrashed()->where('uuid', $uuid)->first();

            return $channel
                ? $this->successRes($channel, HttpCode::HTTP_OK, 'channel returned.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:2', 'string', Rule::unique('channels', 'name')],
            'parent_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validation is fail.');
        }

        try {
            $isExistParent = Channel::where('uuid', $request->parent_id)->first();
            if (!$isExistParent)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'parent channel not found');

            $channel = Channel::create([
                'uuid' => generateUuid(),
                'name' => $request->input('name'),
                'is_active' => $request->input('is_active', false),
                'parent_id' => $request->input('parent_id', 0)
            ]);

            return $this->successRes($channel, HttpCode::HTTP_CREATED, 'create channel successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:2', 'string', Rule::unique('channels', 'name')->ignore($uuid, 'uuid')],
            'parent_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'update validation is fail.');
        }

        try {
            $channel = Channel::where('uuid', $uuid)->first();
            $isExistParent = Channel::where('uuid', $request->parent_id)->first();

            if (!$channel)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

            if (!$isExistParent)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'parent channel not found');

            $channel->update($request->all());

            return $this->successRes($channel, HttpCode::HTTP_OK, 'channel update successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function delete($uuid)
    {
        try {
            $channel = Channel::where('uuid', $uuid)->delete();

            return $channel
                ? $this->successRes('', HttpCode::HTTP_OK, 'channel deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function destroy($uuid)
    {
        try {
            $channel = Channel::where('uuid', $uuid)->forceDelete();

            return $channel
                ? $this->successRes('', HttpCode::HTTP_OK, 'channel hard deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function restore($uuid)
    {
        try {
            $channel = Channel::where('uuid', $uuid)->restore();

            return $channel
                ? $this->successRes($channel, HttpCode::HTTP_OK, 'channel restore successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function clean()
    {
        try {
            $trashChannels = Channel::onlyTrashed()->get();

            if (!$trashChannels) {
                foreach ($trashChannels as $itemTrash) {
                    $itemTrash->forceDelete();
                }
            }

            return $trashChannels
                ? $this->successRes('', HttpCode::HTTP_OK, 'trash clean successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'channel not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }
}
