<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class LikeController extends Controller
{
    public function store(Request $request, $imageId)
    {
        $user = Auth::user();
        $likeExists = $user->likes()->where('image_id', $imageId)->exists();
    
        if (!$likeExists) {
            $user->likes()->create(['image_id' => $imageId]);
        }
    
        $likesCount = Image::findOrFail($imageId)->likes()->count();
        return response()->json(['status' => $likeExists ? 'already_liked' : 'liked', 'likes' => $likesCount]);
    }

    public function destroy($imageId)
    {
        $user = Auth::user();
        $like = $user->likes()->where('image_id', $imageId)->first();
    
        if ($like) {
            $like->delete();
        }
    
        $likesCount = Image::findOrFail($imageId)->likes()->count();
        return response()->json(['status' => $like ? 'unliked' : 'not_liked', 'likes' => $likesCount]);
    }
}
