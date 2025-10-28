<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'content'];

    public function user() {
    return $this->belongsTo(User::class);
}

public function tags() {
    return $this->belongsToMany(Tag::class);
}

public function scopeRecent($query)
{
    return $query->orderBy('created_at', 'desc')->take(5);
}
}