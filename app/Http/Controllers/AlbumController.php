<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::select('albums.*', DB::raw('(
                SELECT images.url FROM images
                JOIN album_images ON images.image_id = album_images.image_id
                WHERE album_images.album_id = albums.album_id
                ORDER BY images.upload_date DESC
                LIMIT 1
            ) as latest_image_url'))
            ->orderBy('created_at', 'desc')
            ->get();
    
        return view('albums.albums', compact('albums'));
    }
    public function create()
    {
        return view('albums.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        Album::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('albums.index')->with('success', 'Альбом успешно создан.');
    }

    public function search(Request $request) {
        $query = $request->get('q');
        $albums = Album::where('name', 'LIKE', "%{$query}%")->get();
        return response()->json($albums);
    }

    public function show($albumId, Request $request)
    {
        $sort = $request->query('sort', 'newest');
        $album = Album::with(['images' => function ($query) use ($sort) {
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
        }])->findOrFail($albumId);

        return view('albums.album', compact('album'));
    }


    public function edit($id)
    {
        $album = Album::findOrFail($id);
        $this->authorize('update', $album);
        return view('albums.edit', compact('album'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $album = Album::findOrFail($id);
        $this->authorize('update', $album);

        $album->update($request->all());

        return redirect()->route('albums.index')->with('success', 'Альбом обновлен успешно.');
    }

    public function destroy($id)
    {
        $album = Album::findOrFail($id);
        $this->authorize('delete', $album);

        $album->delete();

        return redirect()->route('albums.index')->with('success', 'Альбом удален успешно.');
    }

    public function addImage(Request $request, $album_id)
    {
        $request->validate(['image_id' => 'required|exists:images,image_id']);
        $album = Album::findOrFail($album_id);
        $this->authorize('update', $album);

        $album->images()->attach($request->image_id);

        return back()->with('success', 'Изображение добавлено в альбом.');
    }

    public function removeImage($album_id, $image_id)
    {
        $album = Album::findOrFail($album_id);
        $this->authorize('update', $album);

        $album->images()->detach($image_id);

        return back()->with('success', 'Изображение удалено из альбома.');
    }

    
}
