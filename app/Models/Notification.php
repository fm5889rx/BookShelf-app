<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Advanced:
 * 通知notificationsテーブルのモデル定義
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * 複数定義可能な変数
     */
    protected $fillable = [
        'timing',
        'title',
        'body',
        'data',
        'notifiable_id',
        'notifiable_type',
        'read_at',
    ];

    /**
     * キャスト設定
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * リレーション定義
     */
    public function book(): BelongsTo                   // 書籍情報ー読書計画間リレーション(多対１)
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo                   // 読書計画ーユーザー間リレーション（多対１）
    {
        return $this->belongsTo(User::class);
    }

    public function reading_plan(): BelongsTo           // 読書計画ー通知間リレーション（多対１）
    {
        return $this->belongsTo(ReadingPlan::class);
    }
}
