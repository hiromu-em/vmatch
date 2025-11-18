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
     * @return array `exists` `true`:登録済み `false`:未登録 \
     * `error_code`:エラーコードが1ならエラーメッセージ表示
     */
    public function emailExists(string $newUserEmail): array
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?) as exists";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newUserEmail]);
        $result = $statement->fetch();

        $result['error_code'] = $result['exists'] ? 1 : 0;
        return $result;
    }


    /**
     * メールアドレス検証
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return array `validationResult` `true`:メール形式成功 `false`:メール形式失敗 \
     * `error_code`:エラーコードが2ならエラーメッセージ表示
     */
    public function validateEmail(string $newUserEmail): array
    {
        $validationResult = [
            'error_code' => 0,
            'validation_check' => true
        ];

        $email = trim($newUserEmail);

        // 空文字チェック、メールアドレスの形式チェック
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationResult['error_code'] = 2;
            $validationResult['validation_check'] = false;
            return $validationResult;
        }

        //ドメイン存在チェック
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            $validationResult['error_code'] = 2;
            $validationResult['validation_check'] = false;
        }

        return $validationResult;
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
            }
        }

        return $errorMessage;
    }
}
