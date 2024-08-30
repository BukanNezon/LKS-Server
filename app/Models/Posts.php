<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    use HasFactory;

    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $table = 'posts';

    protected $fillable = [
        'caption',
        'user_id',
        'created_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(PostAttachment::class, "post_id");
    }

}
