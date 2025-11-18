# AttendanceManagementApp(勤怠管理アプリ)  
  
## 環境構築  
### Dockerビルド  
 1. クローンを生成  
 ``` bash
 git clone git@github.com:fujiwara-k0814/second-mock-project.git  
 ```
 2. DockerDesktopアプリを立ち上げる  
 3. Dockerをビルドする  
 ``` bash
 docker compose up -d --build  
 ```
※MySQLはOSの都合上、各人でファイルを編集  
  
  
### Laravel環境構築  
 1. PHPコンテナに入り、bashシェルを起動  
 ``` bash
 docker compose exec php bash
 ```
 2. composerをインストール  
 ``` bash
 composer install
 ```
 3. .env.exampleファイルから.envファイルを作成  
 ``` bash
 cp .env.example .env
 ```
 4. 環境変数を設定  
 ``` text
 DB_CONNECTION=mysql
 DB_HOST=mysql
 DB_PORT=3306
 DB_DATABASE=laravel_db
 DB_USERNAME=laravel_user
 DB_PASSWORD=laravel_pass
 ```
 5. アプリケーションキーの生成  
 ``` bash
 php artisan key:generate
 ```
 6. .env.testing.exampleファイルから.env.testingファイルを作成  
 ``` bash
 cp .env.testing.example .env.testing
 ```
 7. テストファイルのキーを生成  
 ``` bash
 php artisan key:generate --env=testing
 ```
 8. マイグレーションの実行  
 ``` bash
 php artisan migrate
 ```
 9. シーディングの実行  
 ``` bash
 php artisan db:seed
 ```
  
### メール認証  
MailHogというツールを使用しています。  
.envの設定が以下になっているか確認してください。  
尚、MAIL_FROM_ADDRESSは任意のメールアドレスを入力してください。  
 ``` bash
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=example@example.com
MAIL_FROM_NAME="${APP_NAME}"
 ```
  
  
## 使用技術  
・PHP 8.3  
・Lravel 8.83  
・MySQL 8.0  
  
  
## ER図  
<img width="797" height="727" alt="image" src="https://github.com/user-attachments/assets/b9c92ae9-ff4a-49a6-b429-3df67320e73f" />  

  
  
## URL  
・開発環境(ユーザー)：http://localhost/  
例：ログイン画面 http://localhost/login  
  
・開発環境(管理者)：http://localhost/admin/  
例：ログイン画面 http://localhost/admin/login  
  
・phpMyAdmin：http://localhost:8080/  
・MailHog：http://localhost:8025/  
  
  
## テストアカウント  
### メール認証済  
name : 認証済ユーザー  
email : user1@example.com  
password : password  
  
### メール未認証  
name : 未認証ユーザー  
email : user2@example.com  
password : password  
  
### テストコマンド  
``` bash
php artisan test tests/Feature
```
  
  
## コーチと合意を取った内容  
・勤怠登録画面での日付、時刻はリアルタイムでなくて良い
