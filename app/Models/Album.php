<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $table = 'albums';
    protected $primaryKey = 'album_id';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'last_image_added_at', // Добавьте этот новый атрибут
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'album_images', 'album_id', 'image_id');
    }    

    // Возможные дополнительные методы и связи
}
