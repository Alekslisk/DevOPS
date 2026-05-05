<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasFactory;

    protected $table = 'administrators';
    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'user_id',
        'privileges_level',
        // Другие поля, которые можно назначать массово
    ];

    public $timestamps = false; // Если в таблице нет стандартных полей времени создания и обновления

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Возможные дополнительные методы и связи
}
