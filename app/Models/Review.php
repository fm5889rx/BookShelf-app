<?php

namespace App\Models;

use App\Models\Book;
use App\Models\User;
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
        'evaluation_value',
        'comment',
        'book_id'
    ];

    /**
     * リレーション定義
     */
    public function books(): BelongsTo              // 書籍情報ーレビュー間リレーション
    {
        $this->belongsTo(Book::class);
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class,
                                    'review_likes',
                                    'review_id',
                                    'user_id')
                    ->withTimestamps();
    }
}
