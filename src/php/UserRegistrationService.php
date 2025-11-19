<?php
namespace Vmatch;

use Vmatch\DatabaseConfig;

class UserRegistrationService
{

    private $pdo;

    private $errorMessageCode;

    public function __construct()
    {
        $databaseConfig = new DatabaseConfig();
        $this->pdo = $databaseConfig->connection();
    }

    /**
     * ユーザーのメールアドレスを仮登録する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     */
    public function registerTemporaryUser(string $newUserEmail): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users (email) VALUES (?)");
        $statement->execute([$newUserEmail]);
    }

    /**
     * ユーザーのメールアドレスを確認する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return array 新規メールアドレスの存在結果情報
     */
    public function emailExists(?string $newUserEmail): array
    {
        //NULLチェック
        if (empty($newUserEmail)) {
            return ['status' => true, 'error_code' => 3];
        }

        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newUserEmail]);
        $result = $statement->fetch();

        $result['error_code'] = $result['status'] ? 1 : 0;
        return $result;
    }


    /**
     * メールアドレス検証
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return array メールアドレス形式の結果情報
     */
    public function validateEmail(?string $newUserEmail): array
    {
        //NULLチェック
        if (empty($newUserEmail)) {
            return ['validation_check' => false, 'error_code' => 3];
        }
        
        $email = trim($newUserEmail);

        // 空文字チェック、メールアドレスの形式チェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['validation_check' => false, 'error_code' => 2];
        }

        //ドメイン存在チェック
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return ['validation_check' => false, 'error_code' => 2];
        }

        return ['validation_check' => true, 'error_code' => 0];
    }

    /**
     * 新規登録時のエラーを表示
     * @return string エラーメッセージ
     */
    public function registrationError(array $errorCodes): string
    {
        $errorMessage = '';
        foreach ($errorCodes as $errorCode) {
            switch ($errorCode) {
                case 1:
                    $errorMessage = "登録済みユーザーです。\nログインしてください";
                    break;
                case 2:
                    $errorMessage = "メールアドレスの形式が正しくありません。";
                    break;
                case 3:
                    $errorMessage = "メールアドレスを入力してください。";
                    break;
            }
        }

        return $errorMessage;
    }
}
