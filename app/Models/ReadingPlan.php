<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * advanced:
 * 読書計画reading_plansテーブルのモデル定義
 */
class ReadingPlan extends Model
{
    use HasFactory;

    /**
     * 複数定義可能な変数
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'start_date',
        'target_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'status' => ReadingPlanStatus::class,
    ];

    /**
     * リレーション定義
     */
    public function book(): BelongsTo                  // 書籍情報ー読書計画間リレーション(多対１)
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo                   // 読書計画ーユーザー間リレーション（多対１）
    {
        return $this->belongsTo(User::class);
    }

    public function notification(): HasMany             // 通知ー読書計画間リレーション（１対多）
    {
        return $this->hasMany(Notification::class);
    }
}
