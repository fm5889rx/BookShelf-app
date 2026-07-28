<?php

namespace App\Http\Controllers;

/**
 * Advanced:
 * マイ読書レポート
 */
use App\Enums\ReadingPlanStatus;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // 高評価書籍TOP5を準備
        $topRatedBooks = DB::table('reviews')                   // レビューテーブルを操作

            ->where('rating', '>=', 4)                          // 4 星以上

            ->join('books', 'reviews.book_id', '=', 'books.id') // booksテーブルからreviewsにあるidを取得

            ->select(                                           // booksテーブルから一部の情報を取得
                'books.id',                                     // ID
                'books.title',                                  // 書籍タイトル
                'books.author',                                 // 著者
                DB::raw('COUNT(*) as review_count'),            // レビュー数
                DB::raw('AVG(reviews.rating) as avg_rating'))   // 平均評価点

            ->groupBy('books.id', 'books.title')                // books.idでグループ化

            ->orderByDesc('review_count')                       // 評価数が多い順

            ->limit(5)                                          // 最大5件

            ->get();                                            // 条件に該当するレコードを取得

        $topRatedBooks = $topRatedBooks->map(function ($row) {  // コレクションを連想配列化
            return [
                'id' => $row->id,                               // ID
                'title' => $row->title,                         // 書籍タイトル
                'author' => $row->author,                       // 著者
                'rating' => $row->avg_rating,                   // 平均評価点
            ];
        })->toArray();

        // ジャンル滅評価傾向TOP5を準備
        $topGenres = Genre::query()                             // genresテーブルを操作

            ->select('genres.*',                                // 全てのカラムを取得
                    DB::raw('AVG(reviews.rating) as average_rating'), // 平均評価点
                    DB::raw('COUNT(reviews.id) as count'))      // レビュー数

            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id') // ピボットテーブルと接続

            ->join('books', 'book_genre.book_id', '=', 'books.id') // booksテーブルと接続

            ->Join('reviews', 'reviews.book_id', '=', 'books.id') // reviewsテーブルと接続

            ->groupBy('genres.id')                              // genres.idでグループ化

            ->orderByDesc('average_rating')                     // 平均評価点が高い順

            ->limit(5)                                          // 最大5件

            ->get();                                            // 条件に該当するレコードを取得

        // 読了冊数を準備
        $completedCount = Auth::user()                          // ログインユーザーが対象

            ->readingPlans()                                    // 読書計画とリレーションで接続

            ->where('status', ReadingPlanStatus::Completed)     // 読了ステータスで検索

            ->distinct()                                        // 同じ内容のレコードは１つだけ返す

            ->count('book_id');                                 // 書籍数をカウント

        $reviewAverage = Review::avg('rating');                 // 平均評価点を取得

        // 評価点ごとのレビュー数を準備
        $rating = Review::selectRaw('rating, COUNT(*) as total') // 総評価数を選択

            ->groupBy('rating')                                 // 評価点でグループ化

            ->orderByDesc('rating')                             // 評価点が高い順

            ->get();                                            // 条件に該当するレコードを取得

        $reviewRating = $rating->pluck('total', 'rating')       // コレクションを連想配列化
            ->toArray();

        // bladeに渡す連想配列を準備
        $data = [

            'summary' => [

                'total_reviews' => Review::count(),             // 総レビュー数

                'books_read' => $completedCount,                // 読了冊数

                'average_rating' => $reviewAverage,             // 平均評価点

            ],

            'rating_distribution' => $reviewRating,             // 評価分析

            'top_rated_books' => $topRatedBooks,                // 高評価書籍TOP5

            'genre_ratings' => $topGenres,                      // ジャンル別評価傾向TOP5

        ];

        $stats = collect($data)->map(function ($value) {        // 連想配列をコレクション化
            return is_array($value) ? collect($value) : $value;
        });

        return view('reports.index', ['stats' => $stats]);    // 統計結果を渡してマイ読書レポート画面を表示
    }
}
