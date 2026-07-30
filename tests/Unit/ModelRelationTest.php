<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Notification;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイト

    /**
     * BookーGenre間 多対多リレーション
     */
    /** Book側から検証**/
    public function test_多対多リレーション_book側()
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
    public function test_多対多リレーション_genre側()
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

    /**
     * Advanced:
     */
    public function test_Genreモデルのreviewsリレーションが正しく定義されている(): void
    {
        // テストデータの準備
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // ジャンルに紐づく書籍を作成
        $book = Book::factory()->create([
            'user_id'  => $user->id,
        ]);

        // ピボットテーブルbook_genreに追加
        $book->genres()->attach($genre);

        // その書籍に紐づくレビューを作成（これで ジャンル ➔ 書籍 ➔ レビュー の線が繋がります）
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 実行
        // リレーションメソッドを呼び出す
        $reviewsCollection = $genre->reviews()->get();

        // 検証（コレクションが返ってきていること、中身がReviewモデルであることを確認）
        $this->assertInstanceOf(Collection::class, $reviewsCollection);
        $this->assertCount(1, $reviewsCollection);
        $this->assertEquals($book->id, $reviewsCollection->first()->id);
    }

    /**
     * AdVanced：
     */
    public function test_Notificationモデルのリレーションが正しく定義されている(): void
    {
        // テストデータの準備
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // データベース（notificationsテーブル）にレコードを直接作成
        $notificationRecord = Notification::create([
            'reading_plan_id' => $plan->id,
            'timing'          => 'on_due_date',
            'title'           => 'テストタイトル',
            'body'            => 'テスト本文',
            'notifiable_id'   => $user->id,
            'notifiable_type' => get_class($user),
            'data'            => json_encode(['reading_plan_id' => $plan->id]),
        ]);

        // テスト用にリレーションの元となる関係性をモデル内部に強制セット
        $notificationRecord->setAttribute('user_id', $user->id);
        $notificationRecord->setAttribute('book_id', $book->id);
        $notificationRecord->setAttribute('reading_plan_id', $plan->id);

        // 各リレーションがそれぞれの正しいモデルクラスを返すかを検証
        // userリレーションの検証
        $this->assertInstanceOf(User::class, $notificationRecord->user);

        // bookリレーションの検証
        if (isset($notificationRecord->book_id)) {
            $this->assertInstanceOf(Book::class, $notificationRecord->book);
        }

        // reading_planリレーションの検証
        if (isset($notificationRecord->reading_plan_id)) {
            $this->assertInstanceOf(ReadingPlan::class, $notificationRecord->reading_plan);
        }
    }
    /**
     * Advanced:
     */
    public function test_ReadingPlanモデルのnotificationリレーションが正しく定義されている(): void
    {
        // テストデータの準備（ユーザーと読書計画を作成）
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        // その読書計画に紐づく通知レコードを1件直接作成
        $plan->notifications()->save(new Notification([
            'timing'          => 'on_due_date',
            'title'           => 'リマインダータイトル',
            'body'            => 'リマインダー本文',
            'notifiable_id'   => $user->id,
            'notifiable_type' => get_class($user),
        ]));

        // 実行
        // リレーション経由で実際にクエリを発行して取得
        $notificationsCollection = $plan->fresh()->notifications;

        // 検証
        // コレクションが返り、作成した通知が確実に1件含まれていること
        $this->assertInstanceOf(Collection::class, $notificationsCollection);
        $this->assertCount(1, $notificationsCollection);

        // 戻り値の型宣言（HasMany）の整合性も同時にチェック
        $this->assertInstanceOf(HasMany::class,
            $plan->notifications()
        );
    }
}
