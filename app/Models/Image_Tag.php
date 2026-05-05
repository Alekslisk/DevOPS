<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ImageTag extends Pivot
{
    protected $table = 'image_tags';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'image_id',
        'tag_id',
    ];

    public $timestamps = false; // Если в таблице нет стандартных полей timestamps

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
