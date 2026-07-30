<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // 現在の通知情報をテーブルから取得
        $notifications = Notification::all();

        // 画面表示
        return view('notifications.index', ['notifications' => $notifications]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function read(string $id)
    {

    // 1. 対象の通知をデータベースから取得（他人の通知を操作できないようログインユーザーで絞り込み）
        $notification = Notification::where('id', $id)
            ->where('notifiable_id', auth()->id())
            ->findOrFail($id);

        // 2. read_at（既読日時）に現在日時を入れて保存（既読化）
        $notification->update([
                'read_at' => now(),
            ]);

            // 3. 元の画面にリダイレクトで戻る（これでBladeの変数エラーを回避できます！）
            return redirect()->back()->with('success', '通知を既読にしました');

        return view('notifications.index');
    }
}
