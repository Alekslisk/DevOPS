<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';
    protected $primaryKey = 'comment_id';
    const CREATED_AT = 'commented_at';

    protected $fillable = [
        'user_id',
        'image_id',
        'comment',
        // Другие поля, которые можно назначать массово
    ];

    protected $dates = [
        'commented_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    // Возможные дополнительные методы и связи 
}
