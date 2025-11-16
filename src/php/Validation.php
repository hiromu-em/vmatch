<?php
namespace Vmatch;

class Validation
{

    /**
     * メールアドレス検証
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return bool `true`:メール形式成功 `false`:メール形式失敗
     */
    public function ValidationCheck(string $newUserEmail): bool
    {

        $email = trim($newUserEmail);

        //空文字チェック
        if (empty($email))
            return false;

        //メールアドレスの形式チェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return false;

        //ドメイン存在チェック
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return false;
        }

        return true;
    }

}