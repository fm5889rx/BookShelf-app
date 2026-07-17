<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な変数
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];

    /**
     * リレーション定義
     */
    public function genres(): BelongsToMany         // 書籍情報ージャンル（多対多）
    {
        return $this->belongsToMany(Genre::class);
    }

    public function reviews(): HasMany              // 書籍情報ーレビュー（１対多）
    {
        return $this->hasMany(Review::class);
    }

    public function favoriteByUser(): BelongsToMany  // 書籍情報ーお気に入り（多対多）
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }
}
