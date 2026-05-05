<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    // Определите название столбца для временной метки создания
    const CREATED_AT = 'upload_date';

    protected $table = 'images';
    protected $primaryKey = 'image_id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'is_adult',
        // другие поля, которые вы хотите назначать массово
    ];

    protected $casts = [
        'is_adult' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function albums()
    {
        return $this->belongsToMany(Album::class, 'album_images', 'image_id', 'album_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'image_tags', 'image_id', 'tag_id');
    }    

    public function likes()
    {
        return $this->hasMany(Like::class, 'image_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'image_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'image_id');
    }
}
