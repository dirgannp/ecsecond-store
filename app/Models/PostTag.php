<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
    protected $fillable=['title','slug','status'];

    public function posts(){
        return $this->hasMany(Post::class, 'post_tag_id', 'id')->where('status', 'active');
    }
}
