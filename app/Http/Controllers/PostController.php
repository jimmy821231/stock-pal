<?php

namespace App\Http\Controllers;

use App\Service\LineMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function form()
    {
        return view('post');
    }

    public function push(Request $request, LineMessageService $lineMessageService): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $input = $request->all();
        $lineMessageService->manualPushMessage($input['title'], $input['content']);

        return redirect()->route('post.create');
    }


}
