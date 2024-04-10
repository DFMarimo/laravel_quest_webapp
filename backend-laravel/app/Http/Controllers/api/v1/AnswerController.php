<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as HttpCode;

class AnswerController extends Controller
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
                $answers = Answer::onlyTrashed()->get();
            } elseif (request('filter') == 'all') {
                $answers = Answer::withTrashed()->get();
            } else {
                $answers = Answer::all();
            }

            return $this->successRes($answers, HttpCode::HTTP_OK, 'all answer returned.');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function show($uuid)
    {
        try {
            /* TODO return parent and child */
            $answer = Answer::withTrashed()->where('uuid', $uuid)->first();

            return $answer
                ? $this->successRes($answer, HttpCode::HTTP_OK, 'answer returned.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'answer not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'min:2', 'string'],
            'body' => ['required', 'min:2', 'string'],
            'quest_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'validation is fail.');
        }

        try {
            $isExistParent = Answer::where('uuid', $request->parent_id)->first();
            if (!$isExistParent)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'parent answer not found');

            $answer = Auth::user()->answers()->create([
                'uuid' => generateUuid(),
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'quest_id' => $request->input('quest_id'),
                'is_active' => $request->input('is_active', false),
            ]);

            /*$Answer = Answer::create([
                'uuid' => generateUuid(),
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'quest_id' => $request->input('quest_id'),
                'author' => $request->input('author'),
                'is_active' => $request->input('is_active', false),
            ]);*/

            return $this->successRes($answer, HttpCode::HTTP_CREATED, 'create answer successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'min:2', 'string'],
            'body' => ['required', 'min:2', 'string'],
            'quest_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->errorRes($validator->messages(), HttpCode::HTTP_UNPROCESSABLE_ENTITY, 'update validation is fail.');
        }

        try {
            $answer = Answer::where('uuid', $uuid)->first();
            $isExistQuest = Quest::where('uuid', $request->quest_id)->first();

            if (!$answer)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'answer not found');

            if (!$isExistQuest)
                return $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'quest not found');

            $answer->update($request->all());

            return $this->successRes($answer, HttpCode::HTTP_OK, 'answer update successfully.');
        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function delete($uuid)
    {
        try {
            $answer = Answer::where('uuid', $uuid)->delete();

            return $answer
                ? $this->successRes('', HttpCode::HTTP_OK, 'Answer deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'Answer not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function destroy($uuid)
    {
        try {
            $answer = Answer::where('uuid', $uuid)->forceDelete();

            return $answer
                ? $this->successRes('', HttpCode::HTTP_OK, 'answer hard deleted.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'answer not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function restore($uuid)
    {
        try {
            $answer = Answer::where('uuid', $uuid)->restore();

            return $answer
                ? $this->successRes($answer, HttpCode::HTTP_OK, 'answer restore successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'answer not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }

    public function clean()
    {
        try {
            $trashAnswers = Answer::onlyTrashed()->get();

            if (!$trashAnswers) {
                foreach ($trashAnswers as $itemTrash) {
                    $itemTrash->forceDelete();
                }
            }

            return $trashAnswers
                ? $this->successRes('', HttpCode::HTTP_OK, 'trash clean successfully.')
                : $this->successRes('', HttpCode::HTTP_NOT_FOUND, 'answer not found');

        } catch (\Exception $exception) {
            return $this->errorRes($exception->getMessage(), HttpCode::HTTP_INTERNAL_SERVER_ERROR, 'server error 500');
        }
    }
}
