<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Image;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ImageController extends Controller
{
    public function index()
    {
        $images = Image::paginate(10);
        return view('images.index', compact('images'));
    }
    public function create()
    {
        $albums = Album::all();
        return view('upload', compact('albums'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'image' => 'required|image',
            'album_id' => 'nullable|exists:albums,album_id',
            'new_album' => 'nullable|max:255',
        ]);

        if (Auth::user()->isFullyBlocked() || Auth::user()->isUploadBlocked()) {
            return back()->withErrors(['blocked' => 'Вы временно заблокированы от загрузки изображений.']);
        }

        // Обработка загрузки файла изображения
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $url = Storage::url($path);
        } else {
            // Возвращаем ошибку, если изображение не было загружено
            return back()->withErrors(['image' => 'Ошибка загрузки изображения.']);
        }

        // Обработка создания нового альбома
        $album_id = $request->album_id;
        if (!empty($request->new_album)) {
            $album = Album::create([
                'user_id' => Auth::id(),
                'name' => $request->new_album,
                'description' => '' // Описание альбома, если нужно
            ]);
            $album_id = $album->album_id;
        }

        // Создание записи изображения
        $is_adult = $request->has('is_adult') && $request->get('is_adult') == 'on';

        $image = Image::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'url' => $url,
            'is_adult' => $is_adult, // Здесь исправлено
        ]);

        // Если указан альбом, связываем изображение с альбомом
        if ($album_id) {
            $image->albums()->attach($album_id);
            $album = Album::find($album_id);
            $album->last_image_added_at = now();
            $album->save();
        }
        
        // Обработка тегов и связывание с изображением
        if ($request->has('tags')) {
            $tagNames = explode(',', $request->tags);
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                array_push($tagIds, $tag->tag_id);
            }
            $image->tags()->sync($tagIds);
        }

        return redirect()->route('home.index')->with('success', 'Изображение успешно загружено.');
    }

    public function show($id)
    {
        $image = Image::with(['user', 'comments.user', 'likes', 'favorites'])->findOrFail($id);
    
        // Проверка, аутентифицирован ли пользователь
        $user = Auth::user();
    
        // Инициализация переменных
        $userIsAdult = false;
        $hasLiked = false;
        $hasFavorited = false;
    
        // Проверка аутентификации и установка значений переменных
        if ($user) {
            $userIsAdult = $user->age >= 18;
            $hasLiked = $user->hasLiked($image);
            $hasFavorited = $user->hasFavorited($image);
        }
    
        // Проверка возрастных ограничений для контента
        if ($image->is_adult && !$userIsAdult) {
            return redirect('login')->with('error', 'Доступ запрещен.');
        }
    
        return view('images.show', compact('image', 'hasLiked', 'hasFavorited'));
    }
    
    

    public function edit($id)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Необходимо войти в систему');
        }
    
        $image = Image::findOrFail($id);
    
        $isAdmin = Auth::user()->is_admin;
        if (Auth::id() !== $image->user_id && !$isAdmin) {
            return redirect('/')->with('error', 'У вас нет прав на редактирование этого изображения');
        }
    
        return view('images.edit', compact('image'));
    }
    
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            // Дополнительные правила валидации...
        ]);
    
        $image = Image::findOrFail($id);
    
        if (Auth::id() !== $image->user_id && !Auth::user()->is_admin) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    
        $is_adult = $request->has('is_adult') && $request->get('is_adult') === 'on';
        $image->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_adult' => $is_adult,
            // Другие поля для обновления...
        ]);

        $image->tags()->detach();
        // Обновление тегов, если они предоставлены
        if ($request->has('tags')) {
            $tagNames = explode(',', $request->tags);
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                array_push($tagIds, $tag->tag_id);
            }
            $image->tags()->sync($tagIds);
        }
    
        return redirect()->route('images.show', $image->image_id)
            ->with('success', 'Изображение успешно обновлено.');
    }


    public function destroy($id)
    {
        // Найдите изображение по его ID
        $image = Image::findOrFail($id);
    
        // Проверьте, имеет ли текущий пользователь права на удаление изображения
        if (Auth::id() !== $image->user_id && !Auth::user()->is_admin) {
            return redirect()->back()->with('error', 'У вас нет прав на удаление этого изображения.');
        }
    
        // Удалите связанные записи из таблицы favorites, likes и comments
        $image->favorites()->delete();
        $image->likes()->delete();
        $image->comments()->delete();
    
        // Отсоедините изображение от альбомов и тегов
        $image->albums()->detach();
        $image->tags()->detach();
    
        // Удалите изображение из хранилища (публичной папки)
        $filePath = str_replace('/storage/', '', $image->url);
        
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
                
    
        // Удалите само изображение
        $image->delete();
    
        return redirect()->route('home.index')->with('success', 'Изображение успешно удалено.');
    }
}
