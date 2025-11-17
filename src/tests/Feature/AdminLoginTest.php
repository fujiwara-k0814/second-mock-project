<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AdminsTableSeeder;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminsTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //管理者ログイン バリデーション メールアドレス
    public function test_login_admin_validate_email()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //管理者ログイン バリデーション パスワード
    public function test_login_admin_validate_password()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin1@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //管理者ログイン バリデーション ログイン情報未登録
    public function test_login_admin_validate_login_unregister()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong_admin1@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}
