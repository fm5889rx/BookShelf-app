<?php

namespace Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    public function test_認可エラーが発生したときに前のページへエラーメッセージ付きでリダイレクトされること()
    {
        // 1. テスト環境で「例外ハンドラ」を通常通り動かすように強制する
        $this->withExceptionHandling();

        // 2. わざと403（AuthorizationException）を発生させるテスト用のルートを作る
        Route::get('/_test-403', function () {
            throw new AuthorizationException('権限がありません');
        });

        // 3. 「前のページ（一覧画面など）」からアクセスしてきたという状況（リファラ）を再現する
        $previousUrl = url('/books');

        // 4. 前のページ情報をヘッダーに持たせて、わざと例外が出るルートへアクセス
        $response = $this->from($previousUrl)->get('/_test-403');

        // 5. 【検証】前のページへ正しくリリダイレクト（302）されているか
        $response->assertRedirect($previousUrl);

        // 6. 【検証】セッション（フラッシュメッセージ）に指定の文字が入っているか
        $response->assertSessionHas('error', '編集権限がありません');
    }
}
