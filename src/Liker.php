<?php

namespace Laravelista\Comments;

trait Liker
{
    public function commentLikes()
    {
        return $this->hasMany(CommentLike::class);
    }
}