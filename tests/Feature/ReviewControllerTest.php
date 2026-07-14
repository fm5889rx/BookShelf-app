<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * レビューCRUDテスト
 */
class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリセットするトレイト
    use MakesHttpRequests;

    public function test_ユーザーはレビュー編集画面を表示できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $review = Review::factory()->create([                   // テスト用にuser_idとbook_idを登録したレビュー
            'book_id' => $book->id,
            'user_id' => $user->id,
            ]);

        // 実行
        $response = $this->actingAs($user)                      // レビュー編集画面を表示
            ->get(route('reviews.edit', [
                $review->id,
            ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('reviews.edit');                // レビュー編集画面が表示されていることを確認
    }

    public function test_ユーザーはレビューを新規作成できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $review = Review::factory()->create([                   // テスト用にuser_idとbook_idを登録したレビュー
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // レビュー編集画面を表示
            ->post(route('reviews.store', $book->id), [
                'book_id' => $book->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]);

        // 検証
        $response->assertStatus(302);                           // 前のページにリダイレクトされていることを確認

        $this->assertDatabaseHas('reviews', [                   // データベースに保存されていることを確認
            'book_id' => $book->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ]);
    }

    public function test_ユーザーはレビューを更新できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $review = Review::factory()->create([                   // テスト用にuser_idとbook_idを登録したレビュー
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // レビューを更新
            ->put(route('reviews.update', $review->id), [
                'book_id' => $book->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]);

        // 検証
        $response->assertViewIs('books.show');                  // 書籍詳細画面を表示していることを確認

        $this->assertDatabaseHas('reviews', [                   // データベースが更新されていることを確認
            'id' => $review->id,
            'book_id' => $book->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ]);
    }


    public function test_ユーザーはレビューを削除できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $review = Review::factory()->create([             // テスト用にuser_idとbook_idを登録したレビュー
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // レビューを削除
            ->delete(route('reviews.destroy', $review->id));

        // 検証
        $this->assertDatabaseMissing('reviews', [               // データベースからレコードが削除さfれているかを確認
            'id' => $review->id,
        ]);
    }
}