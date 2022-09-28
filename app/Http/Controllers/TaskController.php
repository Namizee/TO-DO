<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tasks = User::find(Auth::id())->tasks;
        return response()->json($tasks, '200');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:5',
            'image' => 'nullable|image|max:1024'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), '400');
        }
        $pathImage = null;
        if ($request->hasFile('image')) {
            $extensionImage = $request->image->extension();
            $time = time();
            $pathImage = $request->image->storeAs('images', "image_{$time}.{$extensionImage}");
        }
        $task = Task::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $pathImage,
            'user_id' => Auth::id(),
        ]);
        return response()->json($task, '201');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $task = User::find(Auth::id())->tasks()->where('id', $id)->get();
        if (!$task->count()) {
            return response()->json(['error' => true, 'message' => 'Not found'], '404');
        }
        return response()->json($task,  '200');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (is_null($task) || $task->user_id != Auth::id()) {
            return response()->json(['error' => true, 'message' => 'Not found'], '404');
        }

        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:5',
            'image' => 'nullable|image|max:1024'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), '400');
        }
        $pathImage = null;
        if ($request->hasFile('image')) {
            $extensionImage = $request->image->extension();
            $time = time();
            $pathImage = $request->image->storeAs('images', "image_{$time}.{$extensionImage}");
        }

        $task->update([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $pathImage,
            'user_id' => Auth::id(),
        ]);

        return response()->json($task, '201');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $task = Task::find($id);
        if (is_null($task) || $task->user_id != Auth::id()) {
            return response()->json(['error' => true, 'message' => 'Not found'], '404');
        }
        $task->delete();
        return response()->json('', '204');
    }
}
