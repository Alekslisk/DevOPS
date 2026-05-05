<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    // Добавление комментария к изображению
    public function store(Request $request, $imageId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000', // Установите ограничения по своему усмотрению
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $imageId,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Комментарий добавлен');
    }

    // Редактирование комментария (если требуется)
    public function edit($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, $comment_id)
    {
        try {
            $comment = Comment::findOrFail($comment_id);
            $comment->comment = $request->comment;
            $comment->save();
    
            return response()->json(['message' => 'Комментарий обновлен']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении комментария: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка сервера'], 500);
        }
    }
    


    // Удаление комментария
    public function destroy($comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        // Проверка прав доступа, если необходимо
        $comment->delete();
    
        return response()->json(['message' => 'Комментарий удален']);
    }
    
    
}
