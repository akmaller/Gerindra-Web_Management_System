<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'session_id',
        'ip',
        'user_agent',
        'viewed_at',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    protected $table = 'post_views';
    public $timestamps = true; // created_at & updated_at ada di tabel

    // gunakan viewed_at jika ada, fallback ke created_at
    protected $casts = [
        'viewed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

}
