<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

//ID:16 メール認証機能
class EmailVerifyTest extends TestCase
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
    //メール認証機能 メール送信
    public function test_verify_email_send_email()
    {
        //メール送信フェイク
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        //通知機能発火
        $user->sendEmailVerificationNotification();

        //認証メールの送信確認
        Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    //メール認証機能 メール認証サイト
    public function test_verify_email_authentication_site()
    {
        /** @var \App\Models\User $user */
        $user = User::find(2);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);

        $response->assertSee('http://localhost:8025');
        $response->assertSee('認証はこちらから');
    }

    //メール認証機能 完了後画面遷移
    public function test_verify_email_completed()
    {
        /** @var \App\Models\User $user */
        $user = User::find(2);

        $this->actingAs($user);

        //署名パス取得
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirectContains('/attendance');
    }
}
