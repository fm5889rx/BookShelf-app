<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な変数
     */
    protected $fillable = [
        'name',
    ];

    /**
     * リレーション定義
     */
    public function books(): BelongsToMany          // ジャンルー書籍情報間リレーション（多対多）
    {
        return $this->belongsToMany(Book::class);
    }

    // Advanced;
    public function reviews(): BelongsToMany        // ジャンルー書籍情報ーレビュー間リレーション
    {
    return $this->belongsToMany(Book::class, 'book_genre', 'genre_id', 'book_id')
        ->whereHas('reviews');
    }
}
