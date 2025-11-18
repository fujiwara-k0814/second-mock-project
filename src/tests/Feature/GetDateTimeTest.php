<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;

//ID:4 日時取得機能
class GetDateTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //日時取得 現在時刻表示
    public function test_get_date_time()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)'));
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('HH:mm'));
    }
}
