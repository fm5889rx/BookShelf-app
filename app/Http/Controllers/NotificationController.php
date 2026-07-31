<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\View\View;

/**
 * Advanced:
 * 読書計画に連動した通知機能
 */
class NotificationController extends Controller
{
    /**
     * 通知一覧画面
     */
    public function index(): View
    {
        $notifications = Notification::all();                       // 現在の通知情報をテーブルから取得

        return view('notifications.index', [                        // 画面表示
            'notifications' => $notifications,
        ]);
    }

    /**
     * 「既読にする」リンクに対する処理
     */
    public function read(string $id)
    {

        // 対象の通知をデータベースから取得（他人の通知を操作できないようログインユーザーで絞り込み）
        $notification = Notification::where('id', $id)
            ->where('notifiable_id', auth()->id())
            ->findOrFail($id);

        // read_at（既読日時）に現在日時を入れて保存（既読化）
        $notification->update([
            'read_at' => now(),
        ]);

        // 元の画面にリダイレクトで戻る（これでBladeの変数エラーを回避できます！）
        return redirect()->back()->with('success', '通知を既読にしました');

        return view('notifications.index');
    }
}
