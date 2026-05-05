<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'age',
        'is_admin',
        // Другие поля, которые вы хотите включить в массовое назначение
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'blocked_until'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_blocked' => 'boolean'
    ];

    // Связь с моделью Image
    public function images()
    {
        return $this->hasMany(Image::class, 'user_id');
    }

    // Связь с моделью Album
    public function albums()
    {
        return $this->hasMany(Album::class, 'user_id');
    }

    // Связь с моделью Like
    public function likes()
    {
        // Убедитесь, что здесь правильно указаны названия столбцов
        return $this->hasMany(Like::class, 'user_id');
    }

    public function hasLiked(Image $image)
    {
        return $this->likes()->where('image_id', $image->image_id)->exists();
    }
    

    // Связь с моделью Favorite
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function hasFavorited(Image $image)
    {
        return $this->favorites()->where('image_id', $image->image_id)->exists();
    }
    
    // Связь с моделью Comment
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    // Связь с моделью Administrator (если пользователь является администратором)
    public function administrator()
    {
        return $this->hasOne(Administrator::class, 'user_id');
    }

    /**
     * Проверяет, заблокирован ли пользователь полностью.
     *
     * @return bool
     */
    public function isFullyBlocked()
    {
        return $this->is_blocked && $this->block_level === 2 && $this->blocked_until > now();
    }

    /**
     * Проверяет, заблокирована ли возможность загрузки изображений.
     *
     * @return bool
     */
    public function isUploadBlocked()
    {
        return $this->is_blocked && $this->block_level === 1 && $this->blocked_until > now();
    }
}
