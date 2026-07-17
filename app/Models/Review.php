<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    /**
     * 複数定義可能な変数
     */
    protected $fillable = [
        'user_id',
        'rating',
        'comment',
        'book_id',
    ];

    /**
     * リレーション定義
     */
    public function book(): BelongsTo                  // 書籍情報ーレビュー間リレーション(多対１)
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo                   // レビューーユーザー間リレーション（多対１）
    {
        return $this->belongsTo(User::class);
    }

    public function likedByUsers(): BelongsToMany       // いいねーユーザー間リレーション（多対多）
    {
        return $this->belongsToMany(                    // いいねテーブルを介して、レビューとユーザーの
            User::class,                                // 多対多のリレーションを定義
            'review_likes',
            'review_id',
            'user_id'
        )->withTimestamps();
    }
}
