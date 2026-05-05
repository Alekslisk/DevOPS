<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlockedUserController;




// routes/web.php
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);


// Веб-маршруты для аутентификации
Route::group(['namespace' => 'Auth'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
});

Route::get('/', [HomeController::class, 'index'])->name('home.index');

// Маршрут для отображения формы загрузки изображений
Route::get('/upload', [ImageController::class, 'create'])->name('images.create')->middleware('auth');

// Маршрут для обработки запроса на загрузку изображения
Route::post('/upload', [ImageController::class, 'store'])->name('images.store')->middleware('auth');

// web.php
Route::get('/images/{id}', [ImageController::class, 'show'])->name('images.show');

// Маршрут для добавления лайка к изображению
Route::post('/images/{image}/likes', [LikeController::class, 'store'])->name('likes.store');

// Маршрут для удаления лайка с изображения
Route::delete('/images/{image}/likes', [LikeController::class, 'destroy'])->name('likes.destroy');

// Добавление в избранное
Route::post('/images/{image}/favorites', [FavoriteController::class, 'store'])->name('favorites.store');

// Удаление из избранного
Route::delete('/images/{image}/favorites', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

Route::post('/images/{image}/comments', [CommentController::class, 'store'])->name('comments.store');

// Маршрут для страницы редактирования изображения
Route::get('/images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');

// Маршрут для обработки запроса на обновление изображения
Route::put('/images/{id}', [ImageController::class, 'update'])->name('images.update');

// Маршрут для удаления изображения
Route::delete('/images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');

Route::put('/comments/{comment_id}', [CommentController::class, 'update']);

Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy'])->name('comments.destroy');

Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');

Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');

// Маршрут для страницы профиля пользователя
Route::get('/profile/{userId}', [ProfileController::class, 'show'])->name('profile.show');

// Маршрут для блокировки пользователя администратором
Route::post('/admin/block-user/{userId}', [AdminController::class, 'blockUser'])->name('admin.blockUser');

Route::get('/blocked', [BlockedUserController::class, 'index'])->name('blocked');

Route::post('/admin/unblock-user/{userId}', [AdminController::class, 'unblockUser'])->name('admin.unblockUser');




