<?php

namespace Laravelista\Comments;

use Illuminate\Support\Facades\Config;

class CommentLike extends \Illuminate\Database\Eloquent\Model
{
    protected $with = ['user'];

    protected $fillable = [
        'user_id',
        'comment_id'
    ];

    public function user()
    {
        return $this->belongsTo(Config::get('comments.user_model'));
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}