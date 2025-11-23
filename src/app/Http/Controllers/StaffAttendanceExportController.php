<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\AttendanceSummaryService;
use App\Models\Attendance;

class StaffAttendanceExportController extends Controller
{
    //ルート引数の初期値を'null'に指定
    public function export(Request $request, $user_id, $year = null, $month = null)
    {
        //'now()->**'省略時に現在年月を表示
        //1日を起点とさせる為'1'を指定
        $user = User::find($user_id);
        $targetDate = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            1,
        )
        ->startOfMonth();

        $attendances = $user->attendances()
            ->with('attendanceBreaks')
            ->whereBetween('date', [
                $targetDate->copy()->startOfMonth(),
                $targetDate->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        //プロパティ追加
        //総勤務 → 'total_work_seconds' 総休憩 → 'total_break_seconds' 総稼働 → 'actual_work_seconds'
        app(AttendanceSummaryService::class)->summarize($attendances);

        $filename = 'attendances_' . 
            $targetDate->year . 
            $targetDate->month . 
            '_userid' .$user_id .  
            '_' . 
            now()->format('Ymd_His') . 
            '.csv';

        //csv形式文字列生成
        $csvContent = mb_convert_encoding(
            $this->generateAttendanceCsv($attendances, $user), 'SJIS-win', 'UTF-8'
        );

        //保存先がstorageの時に'storage/app/exports'に保存
        if ($request->destination === 'storage') {
            Storage::disk('local')->put("exports/{$filename}", $csvContent);
            return back();
        }

        //ローカルへダウンロード
        return new StreamedResponse(function () use ($csvContent) {
            echo $csvContent;
        }, 200, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    private function generateAttendanceCsv($attendances, $user)
    {
        $csv = fopen('php://temp', 'r+');

        //header生成
        fputcsv($csv, ['ユーザーID', $user->id]);
        fputcsv($csv, ['名前', $user->name]);
        fputcsv($csv, ['メールアドレス', $user->email]);
        //空行
        fputcsv($csv, []);
        //body生成
        fputcsv($csv, ['日付', '出勤', '退勤', '休憩', '合計', '作成日', '更新日']);
        foreach ($attendances as $attendance) {
            fputcsv($csv, [
                $attendance->date->locale('ja')->isoFormat('MM/DD(ddd)'),
                $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                $attendance->total_break_seconds 
                    ? sprintf(
                        '%02d:%02d', 
                        floor($attendance->total_break_seconds / 3600), 
                        ($attendance->total_break_seconds % 3600) / 60
                    ) 
                    : '',
                $attendance->actual_work_seconds 
                    ? sprintf(
                        '%02d:%02d', 
                        floor($attendance->actual_work_seconds / 3600), 
                        ($attendance->actual_work_seconds % 3600) / 60
                    ) 
                    : '',
                $attendance->created_at,
                $attendance->updated_at,
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }
}   
