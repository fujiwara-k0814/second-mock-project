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
use App\Enums\ApplicationStatus;

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
    
        $users = User::all();

        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();
        
        //前後2か月分の勤怠レコードを作成
        foreach ($users as $user) {
            foreach ($startDate->toPeriod($endDate) as $date) {
                //20%の確率、かつ、テストユーザーは本日を空欄処理 (本日空欄処理はUIで手動確認の為)
                $breakCheck = $faker->boolean(20);
                $testCheck = $date->isSameDay(now()) && in_array($user->id, [1, 2]);

                if ($breakCheck || $testCheck) {
                    $attendance = Attendance::factory()->create([
                        'user_id' => $user->id,
                        'date' => $date,
                        'clock_in' => null,
                        'clock_out' => null,
                        'comment' => null,
                    ]);
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
                //'1' → '追加休憩無し', '2' → '追加休憩有り'
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
                $applicationCheck = $faker->boolean(10);

                if ($applicationCheck) {
                    //承認待ち、承認済みのステータス分け
                    //'1' → 'pending', '2' → 'approved'
                    $approvalStatus = $faker->numberBetween(1, 2);

                    $amendmentApplication = AmendmentApplication::factory()->create([
                        'attendance_id' => $attendance->id,
                        'approval_status_id' => $approvalStatus,
                        'date' => $date,
                        'clock_in' => $date->copy()->setTime(rand(8, 10), 0),
                        'clock_out' => $date->copy()->setTime(rand(17, 19), 0),
                        'comment' => $faker->randomElement(['電車遅延のため', '体調不良のため',])
                    ]);

                    //昼休憩以外もランダム取得
                    //'1' → '追加休憩無し', '2' → '追加休憩有り'
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
                            'break_start' => $newStart,
                            'break_end' => $newEnd,
                        ]);
                    }

                    //承認済みの場合レコード更新
                    if ($approvalStatus === ApplicationStatus::APPROVED->value) {
                        $attendance->clock_in = $amendmentApplication->clock_in;
                        $attendance->clock_out = $amendmentApplication->clock_out;
                        $attendance->comment = $amendmentApplication->comment;
                        $attendance->save();

                        $attendance->attendanceBreaks()->delete();
                        $amendmentApplicationBreaks = $amendmentApplication
                            ->amendmentApplicationBreaks()
                            ->get();
                        foreach ($amendmentApplicationBreaks as $amendmentApplicationBreak) {
                            AttendanceBreak::factory()->create([
                                'attendance_id' => $attendance->id,
                                'break_start' => $amendmentApplicationBreak->break_start,
                                'break_end' => $amendmentApplicationBreak->break_end,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
