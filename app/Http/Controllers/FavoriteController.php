<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function store(Request $request, $imageId)
    {
        $user = Auth::user();
        $image = Image::findOrFail($imageId);

        // Проверяем, не находится ли изображение уже в избранном
        if ($user->favorites()->where('image_id', $imageId)->exists()) {
            return response()->json(['status' => 'already_in_favorites']);
        }

        // Добавление в избранное
        $user->favorites()->create(['image_id' => $imageId]);

        return response()->json(['status' => 'added_to_favorites']);
    }

    public function destroy($imageId)
    {
        $user = Auth::user();
        $favorite = $user->favorites()->where('image_id', $imageId)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed_from_favorites']);
        } else {
            return response()->json(['status' => 'not_favorited']);
        }
    }
    
}
