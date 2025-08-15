<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'day_of_week',
        'job_start',
        'job_finish',
        'job_status',
        'break_duration',
        'job_duration',
        'remark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function breakTime()
    {
        return $this->hasMany(BreakTime::class, 'job_id', 'id');
    }


    /*    1. ジョブのステータスを更新するメソッド
ジョブのステータスを簡単に更新できるメソッドを追加します。

*/
    public function updateDate( $date)
    {
        $this->date = $date;
        $this->save();
    }
    public function updateDayOfWeek( $dayOfWeek)
    {
        $this->date = $dayOfWeek;
        $this->save();
    }
    public function updateStart( $start)
    {
        $this->job_start = $start;
        $this->save();
    }
    public function updateFinish( $finish)
    {
        $this->job_finish = $finish;
        $this->save();
    }
    public function updateStatus( $status)
    {
        $this->job_status = $status;
        $this->save();
    }
    public function updateRemark( $remark)
    {

        if ($remark==null) {
            $remark = ''; // 空文字列をデフォルト値として設定
        }
        $this->remark = $remark;
        $this->save();
    }
    /*使用例:

コピー
$job->updateStatus('completed');
*/

    /*2. ジョブの所要時間を計算するメソッド
ジョブの開始時間と終了時間から所要時間を計算するメソッドを追加します。

*/
    public function calculateDuration()
    {
        $start = strtotime($this->job_start);
        $finish = strtotime($this->job_finish);
        //dd($start." ".$finish." ".($finish-$start)/60);
        if ($start && $finish) {
            return ($finish - $start) / 60; // 分単位で返す
        }

        return null; // 開始または終了時間が不正の場合
    }

    /*使用例

コピー
$duration = $job->calculateDuration(); // 所要時間を取得
*/

    /*3. ジョブに関連する休憩時間を合計するメソッド
休憩時間のリレーションを利用して、関連する休憩時間を合計します。
*/
    public function calculateTotalBreakTime()
    {
        return $this->breakTime->sum(function ($break) {
            $start = strtotime($break->break_start);
            $finish = strtotime($break->break_finish);

            if ($start && $finish) {
                return ($finish - $start) / 60; // 分単位で返す
            }

            return 0; // 不正なデータの場合は0を返す
        });
    }

    /*使用例:

コピー
$totalBreakTime = $job->calculateTotalBreakTime(); // 合計休憩時間を取得
*/

    /*
4. ジョブが完了しているかどうかを判定するメソッド
現在のステータスをチェックして、ジョブが完了しているかを判定します。
*/
    public function isCompleted()
    {
        return $this->job_status === 'completed';
    }
    /*使用例:


if ($job->isCompleted()) {
    echo "このジョブは完了しています。";
}
*/

    /*
5. 指定された日付のジョブを取得するクエリスコープ
特定の日付のジョブを取得するスコープを追加します。

*/
    public function scopeOfDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /*使用例:

$jobs = Job::ofDate('2025-08-06')->get(); // 特定の日付のジョブを取得
*/
    /*
6. ジョブの総所要時間を計算するメソッド
ジョブの所要時間から休憩時間を差し引いて、実際の作業時間を計算します。
*/

    public function calculateNetWorkTime()
    {
        $totalDuration = $this->calculateDuration();
        $totalBreakTime = $this->calculateTotalBreakTime();

        if ($totalDuration !== null) {
            return $totalDuration - $totalBreakTime; // 実際の作業時間を返す
        }

        return null; // 不正なデータの場合
    }
    /*
使用例:

コピー
$netWorkTime = $job->calculateNetWorkTime(); // 実際の作業時間を取得
*/

    /*7. ジョブの詳細を表示するメソッド
ジョブの詳細を簡単に取得できるようにするメソッドを追加します。
*/

    public function getDetails()
    {
        return [
            'user' => $this->user->name ?? '未設定',
            'date' => $this->date,
            'start' => $this->job_start,
            'finish' => $this->job_finish,
            'status' => $this->job_status,
            'netWorkTime' => $this->calculateNetWorkTime() . ' 分',
        ];
    }
    /*
使用例:

コピー
$details = $job->getDetails();
print_r($details);
*/
}
