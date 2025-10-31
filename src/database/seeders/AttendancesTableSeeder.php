<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Models\User;
use Faker\Factory;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $targetUserEmails = ['user1@example.com', 'user2@example.com'];
        $users = User::whereIn('email', $targetUserEmails)->get();

        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();
        
        //テストユーザーのみ前後2か月分の勤怠レコードを作成
        foreach ($users as $user) {
            foreach ($startDate->toPeriod($endDate) as $date) {
                //土日と今日は未処理(UIで手動確認の為)
                if ($date->isWeekend() || $date->isSameDay(now())) {
                    continue;
                }

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    'comment' => null,
                ]);
                
                //昼休憩以外もランダム取得
                foreach (range(1, rand(1, 2)) as $i) {
                    if ($i === 2) {
                        $start = $date->copy()->setTime(15, 0);
                        $end = $date->copy()->setTime(15, 30);
                    } else {
                        $start = $date->copy()->setTime(12, 0);
                        $end = $date->copy()->setTime(13, 0);
                    }
                    AttendanceBreak::factory()->create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $start,
                        'break_end' => $end,
                    ]);
                }
                
                //30%の確率で修正申請
                $applicationCheck = $faker->boolean(30);

                if ($applicationCheck) {
                    //承認待ち、承認済みのステータス分け
                    $approvalStatus = $faker->numberBetween(1, 2);

                    $amendmentApplication = AmendmentApplication::factory()->create([
                        'attendance_id' => $attendance->id,
                        'approval_status_id' => $approvalStatus,
                        'new_clock_in_time' => Carbon::createFromTime(rand(8, 10), 0)->format('H:i:s'),
                        'new_clock_out_time' => Carbon::createFromTime(rand(17, 19), 0)->format('H:i:s'),
                        'new_comment' => $faker->randomElement(['電車遅延のため', '体調不良のため',])
                    ]);

                    //昼休憩以外もランダム取得
                    foreach (range(1, rand(1, 2)) as $j) {
                        if ($j === 2) {
                            $newStart = $date->copy()->setTime(15, 30);
                            $newEnd = $date->copy()->setTime(15, 45);
                        } else {
                            $newStart = $date->copy()->setTime(12, 30);
                            $newEnd = $date->copy()->setTime(14, 00);
                        }
                        $amendmentApplicationBreak = AmendmentApplicationBreak::factory()->create([
                            'amendment_application_id' => $amendmentApplication->id,
                            'new_break_start' => $newStart,
                            'new_break_end' => $newEnd,
                        ]);
                    }

                    //承認済みの場合レコード更新
                    if ($approvalStatus === 2) {
                        $attendance->clock_in = Carbon::parse(
                            $attendance->date->format('Y-m-d') . ' ' . $amendmentApplication->new_clock_in_time
                        );
                        $attendance->clock_out = Carbon::parse(
                            $attendance->date->format('Y-m-d') . ' ' . $amendmentApplication->new_clock_out_time
                        );
                        $attendance->comment = $amendmentApplication->new_comment;
                        $attendance->save();

                        $attendance->attendanceBreaks()->delete();
                        $amendmentApplicationBreaks = $amendmentApplication->amendmentApplicationBreaks()->get();
                        foreach ($amendmentApplicationBreaks as $amendmentApplicationBreak) {
                            AttendanceBreak::factory()->create([
                                'attendance_id' => $attendance->id,
                                'break_start' => $amendmentApplicationBreak->new_break_start,
                                'break_end' => $amendmentApplicationBreak->new_break_end,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
