<?php

namespace App\Http\Controllers;

/**
 * Advanced:
 * 読書計画CRUD
 */
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    protected $currentStatus;

    /**
     *読書計画一覧
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status');       // 現在の表示ステータスをクエリパラメータから取得

        $plans = ReadingPlan::with('user')                      // ステータス指定がある場合は条件付きで、
                                                                // なければ全件読書計画を取得
            ->when($currentStatus !== null, function ($q) use ($currentStatus) {

                $q->where('status', $currentStatus);

            })

            ->paginate(10);                                     // ページネーションは10件／ページ

        return view('reading-plans.index', [                    // パラメータ付で読書計画一覧画面を表示

            'readingPlans' => $plans,                           // レコード本体を渡す

            'currentStatus' => $currentStatus,                  // 今の絞り込みステータスを渡す
        ]);
    }

    /**
     * 読書計画新規登録画面の表示
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画の新規作成
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $user = Auth::user();                                   // ログインユーザーを取得

        $bookId = $request->book_id;                            // 入力された書籍IDを取得

        $status = ReadingPlanStatus::Inective;                  // ステータスを未読書とする

        $startDate = Carbon::today();                     // 開始日は今日とする（過去日を期日としない前提）

        $targetDate = $request->target_date;                    // 期日は$request内の入力日付とする

        $completedAt = null;                                    // 読了日の初期値をnullにする

        ReadingPlan::create([                                   // 準備したデータでレコード作成
            'user_id' => $user->id,
            'book_id' => $bookId,
            'start_date' => $startDate,
            'target_date' => $targetDate,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        return redirect()->route('reading-plans.index');        // 読書計画一覧画面にリダイレクト
    }

    /**
     * 読書計画編集画面の表示
     */
    public function edit(string $id): View
    {
        $readingPlan = ReadingPlan::findOrFail($id);            // 指定された読書計画レコードを取得

        $this->authorize('edit', $readingPlan);     // ログインユーザーが読書計画の作成者かpolicyでチェック

        return view('reading-plans.edit', [                     // 読書計画編集画面を表示
            'readingPlan' => $readingPlan,
        ]);
    }

    /**
     * 既存読書計画の更新
     */
    public function update(UpdateReadingPlanRequest $request, string $id): RedirectResponse
    {
        $readingPlan = ReadingPlan::findOrFail($id);            // 既存レコードをテーブルから読み込み

        $this->authorize('update', $readingPlan);   // ログインユーザーが読書計画の作成者かpolicyでチェック

        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $readingPlan->update($validated);                       // 準備したデータでレコード更新

        return redirect()->route('reading-plans.index');        // 読書計画一覧画面にリダイレクト
    }

    /**
     * 既存読書計画の削除
     */
    public function destroy(string $id): RedirectResponse
    {
        $readingPlan = ReadingPlan::findOrFail($id);            // 既存レコードをテーブルから読み込み

        $this->authorize('delete', $readingPlan);   // ログインユーザーが読書計画の作成者かpolicyでチェック

        $readingPlan->delete();                                 // レコードを削除

        return redirect()->route('reading-plans.index');        // 読書計画一覧画面へリダイレクト
    }

    /**
     * 既存計画のステータスを読了に変更する
     */
    public function complete(string $id): View
    {
        $readingPlan = ReadingPlan::findOrFail($id);            // 対象レコードをテーブルから読み込み

        $readingPlan->status = readingPlanStatus::Completed;    // ステータスを読了に更新

        $readingPlan->completed_at = now();                     // 完了日を現在日時に更新

        $readingPlan->save();                                   // レコード保存

        // 一覧画面際表示の準備
        $plans = ReadingPlan::all();                            // 更新後の全データを取得

        $currentStatus = null;                                  // 表示フィルタをクリアする

        return view('reading-plans.index', [                    // パラメータ付で読書計画一覧画面を表示

            'readingPlans' => $plans,                           // レコード本体を渡す

            'currentStatus' => $currentStatus,                  // 今の絞り込みステータスを渡す
        ]);
    }
}
