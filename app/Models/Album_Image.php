<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AlbumImage extends Pivot
{
    protected $table = 'album_images';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'album_id',
        'image_id',
        // Другие поля, которые можно назначать массово
    ];

    public $timestamps = false; // Если в таблице нет стандартных полей timestamps

    public function album()
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }
}