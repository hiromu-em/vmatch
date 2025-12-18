<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Vmatch\UserAuthentication\UserAuthentication;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserAuthenticationTest extends TestCase
{
    private const string EMAIL = 'sample@example.com';
    /**
     * メールアドレスをDBに登録するテスト
     */
    public function testRegisterEmail()
    {
        // PDDstatementのモックを作成
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([self::EMAIL]);

        // PDOのモックを作成
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("INSERT INTO users_vmatch(email) VALUES (?)")
            ->willReturn($statementMock);

        $userAuth = new UserAuthentication($pdoMock);
        $userAuth->registerEmail(self::EMAIL);
    }

    /**
     * メールアドレスを基にしてユーザーIDを取得するテスト
     */
    public function testGetSearchUserId()
    {
        $userId = '761e3a6e-m502-425v-l1cc-4b644c5989e9';

        $stetementMock = $this->createMock(PDOStatement::class);

        $stetementMock
            ->expects($this->once())
            ->method('execute')
            ->with([self::EMAIL]);

        $stetementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => $userId]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT id FROM users_vmatch WHERE email = ?")
            ->willReturn($stetementMock);

        $userAuth = new UserAuthentication($pdoMock);
        $result = $userAuth->getSearchUserId(self::EMAIL);

        $this->assertSame($userId, $result);
    }

    /**
     * testEmailExists用データプロバイダー
     * @return array<string, array<bool, bool>>
     */
    public static function emailExistsProvider()
    {
        return [
            'DBにメールアドレスが存在する場合' => [true, true],
            'DBにメールアドレスが存在しない場合' => [false, false],
        ];
    }

    /**
     * メールアドレス存在確認テスト
     * @param bool $dbStatus DBからのステータス
     * @param bool $expected 期待値
     */
    #[DataProvider('emailExistsProvider')]
    public function testEmailExists(bool $dbStatus, bool $expected)
    {
        $stetementMock = $this->createMock(PDOStatement::class);

        $stetementMock
            ->expects($this->once())
            ->method('execute')
            ->with([self::EMAIL]);

        $stetementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(['status' => $dbStatus]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status")
            ->willReturn($stetementMock);

        $userAuth = new UserAuthentication($pdoMock);
        $this->assertSame($expected, $userAuth->emailExists(self::EMAIL));
    }

    /**
     * testSetSignInCodes用データプロバイダー
     * @return array<string, array<bool, bool>>
     */
    public static function setSignInCodesProvider()
    {
        return [
            'メールアドレスが存在し、サインインフラグがtrueの場合' => [true, true, [0]],
            'メールアドレスが存在し、サインインフラグがfalseの場合' => [true, false, [1]],
            'メールアドレスが存在せず、サインインフラグがtrueの場合' => [false, true, [0]],
            'メールアドレスが存在せず、サインインフラグがfalseの場合' => [false, false, [0]],
        ];
    }
    /**
     * サインインコード設定テスト
     */
    #[DataProvider('setSignInCodesProvider')]
    public function testSetSignInCodes(bool $emailExists, bool $signInFlag, array $expectedCode)
    {
        $userAuthentication = new UserAuthentication();
        $userAuthentication->setSignInCodes($emailExists, $signInFlag);
        $this->assertSame($expectedCode, $userAuthentication->getErrorCodes());
    }
}