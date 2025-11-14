<?php
    namespace Vmatch\certification;

    use Vmatch\DatabaseConfig;
    
    class GenerateRegistrationToken{

        /**
         * 新規登録用のトークン生成
         * @return string URLに付随するトークン
         */
        public function tokenGenerate(): string{

            $plainToken = bin2hex(random_bytes(32));
            $hash = hash("sha256", $plainToken);
            $expireDateTime = new \DateTime() -> add(new \DateInterval("PT30M")) -> format("Y-m-d H:i:s");
            
            $databaseConfig = new DatabaseConfig();
            $statement = $databaseConfig -> connection() -> prepare("INSERT INTO email_verifications(token_hash, expires_at) VALUES(?, ?)");
            $statement -> execute([$hash, $expireDateTime]);

            return $plainToken;

        }
    }