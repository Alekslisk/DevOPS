<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = 'tags';
    protected $primaryKey = 'tag_id';

    protected $fillable = [
        'name',
        // Другие поля, которые можно назначать массово
    ];

    public function images()
    {
        return $this->belongsToMany(Image::class, 'image_tags', 'tag_id', 'image_id');
    }

    // Возможные дополнительные методы и связи
}
