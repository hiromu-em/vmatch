<?php
    namespace Vmatch;

    class Validation{

        /**
         * メールアドレス検証
         * @param string $email
         * @return bool
         */
        public function ValidationCheck(string $email): bool{
            
            $email = trim($email);
            
            //空文字チェック
            if(empty($email)) return false;
            
            //メールアドレスの形式チェック
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

            //ドメイン存在チェック
            $domain = substr(strrchr($email,"@"),1);
            if (!checkdnsrr($domain, "MX")) {
                return false;
            }

            return true;
        }

    }