<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Получение параметров сортировки из запроса
        $sort = $request->query('sort', 'newest');
        $tagQuery = $request->query('tag');
        
        $userIsAuthenticated = Auth::check();
        // Определение возраста пользователя
        $userIsAdult = Auth::check() && Auth::user()->age >= 18;

        // Инициализация запроса для получения изображений
        $query = Image::with('user', 'tags', 'likes');

        if (!$userIsAdult && $userIsAuthenticated) {
            // Если пользователь не совершеннолетний, исключаем контент для взрослых
            $query->where('is_adult', false);
        }

        if ($tagQuery) {
            $tags = explode(',', $tagQuery);
            $includeTags = [];
            $excludeTags = [];
    
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (str_starts_with($tag, '-')) {
                    $excludeTags[] = substr($tag, 1);
                } else {
                    $includeTags[] = $tag;
                }
            }
    
            if (!empty($includeTags)) {
                $query->where(function ($query) use ($includeTags) {
                    foreach ($includeTags as $tag) {
                        $query->orWhereHas('tags', function ($q) use ($tag) {
                            $q->where('name', 'LIKE', '%' . $tag . '%');
                        });
                    }
                });
            }
    
            foreach ($excludeTags as $tag) {
                $query->whereDoesntHave('tags', function ($q) use ($tag) {
                    $q->where('name', 'LIKE', '%' . $tag . '%');
                });
            }
        }

        // Определение возраста пользователя
        $userIsAdult = Auth::check() && Auth::user()->age >= 18;

        // Определение, когда нужно показывать альбомы
        $shouldShowAlbums = $sort == 'newest' && empty($tagQuery);

        $albums = [];
        if ($shouldShowAlbums) {
            $albums = Album::select('albums.*', DB::raw('(
                    SELECT images.url FROM images
                    JOIN album_images ON images.image_id = album_images.image_id
                    WHERE album_images.album_id = albums.album_id
                    ORDER BY images.upload_date DESC
                    LIMIT 1
                ) as latest_image_url'))
                ->orderBy('last_image_added_at', 'desc')
                ->take(3)
                ->get();
        }


        // Применение сортировки для изображений
        switch ($sort) {
            case 'popular_all_time':
                $query->withCount('likes')
                      ->orderBy('likes_count', 'desc');
                break;
            case 'popular_year':
                $query->withCount('likes')
                      ->where('upload_date', '>', Carbon::now()->subYear())
                      ->orderBy('likes_count', 'desc');
                break;
            case 'popular_month':
                $query->withCount('likes')
                      ->where('upload_date', '>', Carbon::now()->subMonth())
                      ->orderBy('likes_count', 'desc');
                break;
            case 'popular_week':
                $query->withCount('likes')
                      ->where('upload_date', '>', Carbon::now()->subWeek())
                      ->orderBy('likes_count', 'desc');
                break;
            default:
                $query->orderBy('upload_date', 'desc');
                break;
        }

        // Получение изображений и применение фильтра по возрасту
        $images = $query->get()->map(function ($image) use ($userIsAdult) {
            $image->show_blur = $image->is_adult && !$userIsAdult;
            return $image;
        });

        return view('home.index', compact('albums', 'images'));
    }
}
