<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイトを使用

    /**
     * BookーGenre間 多対多リレーション
     */
    /** Book側から検証**/
    public function test_多対多リレーション_Book側()
    {
        // 準備
        $book = Book::factory()->create();                  // 書籍情報1件を作成

        $genres = Genre::factory()->count(3)->create();     // ジャンル3件を作成

        // 実行
        $book->genres()->attach($genres);                   // 3件をbook_idーgenre_idの形で挿入

        // 検証
        $this->assertCount(3, $book->genres);               // 書籍側から見て3件できているか検証

        $this->assertEqualsCanonicalizing(                  // 書籍側から取得できるか検証
            $genres->pluck('id')->toArray(),
            $book->genres->pluck('id')->toArray()
        );
    }

    /** Genre側から検証**/
    public function test_多対多リレーション_Genre側()
    {
        // 準備
        $book = Book::factory()->create();                  // 書籍情報1件を作成

        $genre = Genre::factory()->create();                // ジャンル1件を作成

        // 実行
        $genre->books()->attach($book);                     // 書籍側から挿入

        // 検証
        $this->assertCount(1, $genre->books);               // ジャンル側から見て1件できているか検証

        $this->assertTrue(                                  // book_idが挿入されているか検証
            $genre->books->contains(
                fn ($b) => $b->id === $book->id)
        );
    }

    /** いいね（review_likes）リレーション（一対多） **/
    public function test_いいね_一対多リレーション()
    {
        // 準備
        $book = Book::factory()->create();                  // 書籍情報を1件作成

        // 実行
        $book->reviews()->createMany(                       // book1件にいいね2件を紐づける
            Review::factory()->count(2)->make()->toArray()
        );

        // 検証
        $this->assertCount(2, $book->reviews);              // 2件紐づいているか検証

        foreach ($book->reviews as $review) {               // レビュー個々の内容確認
            $this->assertNotEmpty($review->comment);        // コメントが格納されているか検証
        }
    }

    /** お気に入り（favorites）リレーション（多対多）**/
    public function test_お気に入り_多対多リレーション()
    {
        // 準備
        $book = Book::factory()->create();                  // 書籍情報を1件生成

        $user = User::factory()->create();                  // ユーザー情報を1件作成

        // 実行
        $book->favoriteByUser()->attach($user);             // favoritesテーブルに挿入

        // 検証
        $this->assertTrue(                                  // リレーションができているか検証
            $book->favoriteByUser->contains(fn ($u) => $u->id === $user->id)
        );
    }

    /** お気に入り一覧（favorites）の逆方向リレーション（多対多） **/
    public function test_お気に入り一覧_多対多リレーション()
    {
        // 準備
        $review = Review::factory()->create();              // レビューを1件生成

        $user = User::factory()->create();                  // ユーザー情報を1件作成

        // 実行
        $user->likedReviews()->attach($review);             // favoritesテーブルに挿入

        // 検証
        $this->assertTrue(                                  // リレーションができているか検証
            $user->likedReviews->contains(fn ($b) => $b->id === $review->id)
        );
    }
}
