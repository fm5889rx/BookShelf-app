<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
    public function reviews(): HasManyThrough       // ジャンルー書籍情報ーレビュー間リレーション
    {
        return $this->hasManyThrough(review::class, Book::class);
    }
}
