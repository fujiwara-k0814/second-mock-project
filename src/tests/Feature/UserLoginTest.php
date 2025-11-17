<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UsersTableSeeder;

class UserLoginTest extends TestCase
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
    //ユーザーログイン バリデーション メールアドレス
    public function test_login_user_validate_email()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ユーザーログイン バリデーション パスワード
    public function test_login_user_validate_password()
    {
        $response = $this->post('/login', [
            'email' => 'user1@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ユーザーログイン バリデーション ログイン情報未登録
    public function test_login_user_validate_login_unregister()
    {
        $response = $this->post('/login', [
            'email' => 'wrong_user1@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}
