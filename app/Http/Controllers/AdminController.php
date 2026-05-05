<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function blockUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->is_blocked = true;
        $user->block_reason = $request->input('block_reason');
        $user->blocked_until = $request->input('blocked_until');
        $user->block_level = $request->input('blockType') == 'restrict' ? 1 : 2; // 1 для ограничения загрузки, 2 для полной блокировки
        $user->save();

        return redirect()->back()->with('success', 'Пользователь успешно заблокирован.');
    }

    public function unblockUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->is_blocked = false;
        $user->block_reason = null;
        $user->blocked_until = null;
        $user->block_level = 0;
        $user->save();

        return response()->json(['message' => 'Блокировка пользователя снята']);
    }

}
