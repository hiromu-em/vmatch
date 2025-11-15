<?php
    namespace Vmatch\certification;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\OAuth;
    use League\OAuth2\Client\Provider\Google;

    use Vmatch\certification\GenerateRegistrationToken;


    class RegisterMail{

        /**
         * 新規登録認証メール送信
         * @param string $email
         */
        public function send(string $newUserEmail){

            date_default_timezone_set("Asia/Tokyo");

            $mail = new PHPMailer();
            $mail -> isSMTP();
            // $mail -> SMTPDebug = SMTP::DEBUG_OFF;
            $mail -> SMTPDebug = SMTP::DEBUG_CONNECTION;
            $mail -> Debugoutput = 'error_log';
            $mail -> Host = "smtp.gmail.com";
            $mail -> Port = 587;
            $mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail -> SMTPAuth = true;
            $mail -> AuthType = "XOAUTH2";

            $email = getenv('EMAIL');
            $clientId = getenv('CLIENTID');
            $clientSecret = getenv('CLIENTSECRET');
            $refreshToken = getenv('REFRESHTOKEN');
            
            $provider = new Google(
                [
                    'clientId' => $clientId,
                    'clientSecret'=> $clientSecret,
                ]
            );

            $mail -> setOAuth(new OAuth([
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken'=> $refreshToken,
                'userName' => $email
            ]));

            $mail -> setFrom($email,"Vmatch");
            $mail -> addAddress($newUserEmail);
            $mail -> Subject = "test";
            $mail -> CharSet = PHPMailer::CHARSET_UTF8;
 
            //URLトークン生成
            $generateRegistrationToken = new GenerateRegistrationToken();
            $token = $generateRegistrationToken -> tokenGenerate();
 
            $url = "https://vmatch.up.railway.app/src/php/certification/verify_email.php?token={$token}";
            $verificationEmailTemplate = file_get_contents(__DIR__ ."/../../registration_verification_email.html");
            $verificationEmailTemplate =str_replace("{url}", $url, $verificationEmailTemplate);
 
            $mail -> isHTML(true);
            $mail -> Body = $verificationEmailTemplate;
            $mail -> AltBody = "アカウントを有効化するには、次のURLにアクセスしてください。\n" . $url;
 
            if(!$mail -> send()){
                echo $mail -> ErrorInfo;
            }else{
                echo "成功";
            }
        }
    }