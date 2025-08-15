<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BreakTime extends Model
{
    use HasFactory;
    protected $table = 'breaks'; // テーブル名を指定

    protected $fillable = [
        'job_id',
        'date',
        'break_start',
        'break_finish',
        'break_status',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }


    /*
    1. 休憩時間のステータスを更新するメソッド
休憩のステータスを簡単に更新するメソッドを追加します。
*/

    public function updateStart( $start)
    {
//dd($start);
        $this->break_start = $start;
        $this->save();
    }
    
    public function updateFinish( $finish)
    {
//dd($finish);
        $this->break_finish = $finish;
        $this->save();
    }
    public function updateStatus( $status)
    {
        $this->break_status = $status;
        $this->save();
    }
    /*
使用例:

$breakTime->updateStatus('completed');
2. 休憩時間を計算するメソッド
休憩の開始時間と終了時間から休憩時間を計算するメソッドを追加します。
*/

    public function calculateDuration()
    {
        $start = strtotime($this->break_start);
        $finish = strtotime($this->break_finish);

        if ($start && $finish) {
            return ($finish - $start) / 60; // 分単位で返す
        }

        return null; // 開始または終了時間が不正の場合
    }
    /*
使用例:

コピー
$duration = $breakTime->calculateDuration(); // 休憩時間を取得
3. 休憩が完了しているかどうかを判定するメソッド
現在のステータスをチェックして、休憩が完了しているかを判定します。

*/

    public function isCompleted()
    {
        return $this->break_status === 'completed';
    }

    /*使用例:

コピー
if ($breakTime->isCompleted()) {
    echo "この休憩は完了しています。";
}
4. 指定された日付の休憩を取得するクエリスコープ
特定の日付の休憩を取得するスコープを追加します。

*/

    public function scopeOfDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /*
使用例:

コピー
$breaks = BreakTime::ofDate('2025-08-06')->get(); // 特定の日付の休憩を取得
5. 休憩が指定されたジョブに属しているかを判定するメソッド
休憩が特定のジョブに属しているかどうかを判定するメソッドを追加します。
*/

    public function belongsToJob(Job $job)
    {
        return $this->job_id === $job->id;
    }

    /*使用例:

コピー
if ($breakTime->belongsToJob($job)) {
    echo "この休憩は指定されたジョブに属しています。";
}
6. 休憩の詳細を表示するメソッド
休憩の詳細を簡単に取得できるメソッドを追加します。
*/

    public function getDetails()
    {
        return [
            'job' => $this->job->id ?? '未設定',
            'date' => $this->date,
            'start' => $this->break_start,
            'finish' => $this->break_finish,
            'status' => $this->break_status,
            'duration' => $this->calculateDuration() . ' 分',
        ];
    }

    /*使用例:

コピー
$details = $breakTime->getDetails();
print_r($details);
7. 休憩が有効かどうかを判定するメソッド
開始時間と終了時間が正しく設定されているかをチェックするメソッドを追加します。

*/

    public function isValid()
    {
        $start = strtotime($this->break_start);
        $finish = strtotime($this->break_finish);

        return $start && $finish && $start < $finish;
    }
    /*
使用例:

コピー
if ($breakTime->isValid()) {
    echo "この休憩は有効です。";
}
*/
}
