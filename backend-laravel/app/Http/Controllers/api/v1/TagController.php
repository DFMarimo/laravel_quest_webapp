<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TagController extends Controller
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
                $tags = Tag::onlyTrashed()->get();
            } elseif (request('filter') == 'all') {
                $tags = Tag::withTrashed()->get();
            } else {
                $tags = Tag::all();
            }

            return $this->successRes($tags, HttpCode::HTTP_OK, 'all tag returned.');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function show($uuid)
    {
        try {
            /* TODO return parent and child */
            $tag = Tag::withTrashed()->where('uuid', $uuid)->first();

            return $tag
                ? $this->successRes($tag, HttpCode::HTTP_OK, 'tag returned.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:2', 'string', Rule::unique('tags', 'name')],
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validation is fail.');
        }

        try {
            $tag = Tag::create([
                'uuid' => generateUuid(),
                'name' => $request->input('name'),
            ]);

            return $this->successRes($tag, HttpCode::HTTP_CREATED, 'create tag successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:2', 'string', Rule::unique('tags', 'name')->ignore($uuid,'uuid')],
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'update validation is fail.');
        }

        try {
            $tag = Tag::where('uuid', $uuid)->first();

            if (!$tag)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

            $tag->update($request->all());

            return $this->successRes($tag, HttpCode::HTTP_OK, 'tag update successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function delete($uuid)
    {
        try {
            $tag = Tag::where('uuid', $uuid)->delete();

            return $tag
                ? $this->successRes('', HttpCode::HTTP_OK, 'tag deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function destroy($uuid)
    {
        try {
            $tag = Tag::where('uuid', $uuid)->forceDelete();

            return $tag
                ? $this->successRes('', HttpCode::HTTP_OK, 'tag hard deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function restore($uuid)
    {
        try {
            $tag = Tag::where('uuid', $uuid)->restore();

            return $tag
                ? $this->successRes($tag, HttpCode::HTTP_OK, 'tag restore successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function clean()
    {
        try {
            $trashTags = Tag::onlyTrashed()->get();

            if (!$trashTags) {
                foreach ($trashTags as $itemTrash) {
                    $itemTrash->forceDelete();
                }
            }

            return $trashTags
                ? $this->successRes('', HttpCode::HTTP_OK, 'trash clean successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'tag not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }
}
