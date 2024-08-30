<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAttachment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $table = 'post_attachments';

    protected $fillable = [
        'storage_path',
        'post_id'
    ];

    public function post()
    {
        return $this->belongsTo(Posts::class);
    }
}
