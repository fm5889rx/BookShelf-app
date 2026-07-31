<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;                  // Advanced:
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
// Advanced:
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Advanced:
     * authorize()で403エラーが出た時の例外ハンドラ
     */
    public function render($request, Throwable $exception)
    {
        // 403 が発生したら「一覧画面に戻る + メッセージ」
        if ($exception instanceof AuthorizationException) {
            return redirect()
                ->to(url()->previous())          // 前のページへ戻る
                ->with('error', '編集権限がありません'); // ここで flash する
        }

        return parent::render($request, $exception);
    }
}
