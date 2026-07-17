<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * その他機能（お気に入り・いいね・ランキング）のテスト
 */
class OtherFunctionsTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリフレッシュするトレイト

    /**---------------------------------------------------------
     * お気に入りのテスト
     *--------------------------------------------------------*/
    public function test_お気に入り一覧を表示する()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        // 実行
        $response = $this->actingAs($user)                      // ログインしてお気に一覧を表示
            ->json('GET', route('favorites.index'));

        // 検証
        $response->assertStatus(200);                           // HTTPステータスは200を期待（正常終了）

        $response->assertViewIs('favorites.index');             // お気に入り一覧が表示されているか確認
    }

    public function test_お気に入りを設定する()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                     // 渡した書籍情報のお気に入りを設定する
            ->post(route('favorites.toggle', $book->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response
            ->assertRedirect(route('books.show', $book->id));  // 書籍詳細画面にリダイレクトしているか確認

        $this->assertDatabaseHas('favorites', [         // favoritesテーブルにレコードが書き込まれたか確認
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_お気に入りを解除する()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)                     // 渡した書籍情報のお気に入りを設定する
            ->post(route('favorites.toggle', $book->id));

        // 実行
        $response = $this->actingAs($user)                     // 渡した書籍情報のお気に入りを解除する
            ->post(route('favorites.toggle', $book->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response
            ->assertRedirect(route('books.show', $book->id));  // 書籍詳細画面にリダイレクトしているか確認

        $this->assertDatabaseMissing('favorites', [     // favoritesテーブルからレコードが削除されたか確認
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_未ログイン状態ではお気に入りを設定／解除できない()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this                                   // 未ログインで書籍情報のお気に入りを設定する
            ->post(route('favorites.toggle', $book->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面にリダイレクトしているか確認
    }

    /**---------------------------------------------------------
     * いいねのテスト
     *--------------------------------------------------------*/
    public function test_いいねを設定する()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([                   // テスト用にレビューを1件作成
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // 渡したレビューのいいねを設定する
            ->post(route('reviews.like', $review->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response
            ->assertRedirect(route('books.show', $review->id)); // 書籍詳細画面にリダイレクトしているか確認

        $this->assertDatabaseHas('review_likes', [      // favoritesテーブルにレコードが書き込まれたか確認
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_いいねを解除する()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([                   // テスト用にレビューを1件作成
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user)                                  // 渡したレビューのいいねを設定する
            ->post(route('reviews.like', $review->id));

        // 実行
        $response = $this->actingAs($user)                      // レビューのいいねを解除する
            ->post(route('reviews.like', $review->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response
            ->assertRedirect(route('books.show', $review->id)); // 書籍詳細画面にリダイレクトしているか確認

        $this->assertDatabaseMissing('review_likes', [  // favoritesテーブルからレコードが削除されたか確認
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_未ログイン状態ではいいねを設定／解除できない()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([                   // テスト用にレビューを1件作成
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 実行
        $response = $this                                       // 未ログインでレビューのいいねを設定する
            ->post(route('favorites.toggle', $book->id));

        // 検証
        $response->assertStatus(302);                       // HTTPステータスは302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面にリダイレクトしているか確認
    }

    /**---------------------------------------------------------
     * ランキングのテスト
     *--------------------------------------------------------*/
    public function test_ランキングが表示できる()
    {
        // 準備
        $reviews = Review::factory()->count(15)->create();  // テスト用にレビューを15件作成

        // 実行
        $response = $this->get(route('ranking.index'));     // ランキング一覧を表示

        // 検証
        $response->assertStatus(200);                       // HTTPステータスは200を期待（正常終了）

        $response->assertViewIs('ranking.index');           // ランキング画面にリダイレクトしているか確認
    }
}
