<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffAttendanceExportController extends Controller
{
    public function export(Request $request, $user_id, $year = null, $month = null)
    {
        $user = User::find($user_id);
        $targetDate = Carbon::createFromDate(
            $year ?? Carbon::now()->year,   //'now()->**'省略時に現在年月を表示
            $month ?? Carbon::now()->month,
            1,   //1日を起点とさせる為'1'を指定
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

        //総勤務、総休憩、総稼働プロパティ追加(終了時間が無いなどの場合は'null')
        $attendances->each(function ($attendance) {
            $attendance->total_work_seconds = (
                $attendance->clock_in && $attendance->clock_out
            )
                ? $attendance->clock_out->diffInSeconds($attendance->clock_in)
                : null;

            $attendance->total_break_seconds = $attendance->attendanceBreaks
                ->sum(function ($break) {
                    return ($break->break_start && $break->break_end)
                        ? $break->break_end->diffInSeconds($break->break_start)
                        : null;
                });

            $attendance->actual_work_seconds = (
                $attendance->total_work_seconds && $attendance->total_break_seconds
            )
                ? max(0, $attendance->total_work_seconds - $attendance->total_break_seconds)
                : null;
        });

        $filename = 'attendances_' . $targetDate->year . $targetDate->month . '_userid' .$user_id .  '_' . now()->format('Ymd_His') . '.csv';
        $csvContent = $this->generateAttendanceCsv($attendances, $user);

        if ($request->destination === 'storage') {
            Storage::disk('local')->put("exports/{$filename}", $csvContent);
            return back();
        }

        return new StreamedResponse(function () use ($csvContent) {
            echo $csvContent;
        }, 200, [
            'Content-Type' => 'text/csv',
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
                $attendance->total_break_seconds ? sprintf('%02d:%02d', floor($attendance->total_break_seconds / 3600), ($attendance->total_break_seconds % 3600) / 60) : '',
                $attendance->actual_work_seconds ? sprintf('%02d:%02d', floor($attendance->actual_work_seconds / 3600), ($attendance->actual_work_seconds % 3600) / 60) : '',
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
