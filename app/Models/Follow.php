<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'follow';

    protected $fillable = [
        'follower_id',
        'following_id',
        'is_accepted'
    ];

    public function following() {
        return $this->belongsTo(User::class, 'follower_id', 'id');
    }
    
    public function follower() {
        return $this->belongsTo(User::class, 'following_id', 'id');
    }
}
