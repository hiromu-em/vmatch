<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Vmatch\UserAuthentication\UserAuthentication;
use PHPUnit\Framework\TestCase;

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
     * メールアドレス存在確認のテスト
     */
    public function testEmailExists()
    {
        $stetementMock = $this->createMock(PDOStatement::class);

        $stetementMock
            ->expects($this->once())
            ->method('execute')
            ->with([self::EMAIL]);

        $stetementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(['status' => false]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status")
            ->willReturn($stetementMock);

        $userAuth = new UserAuthentication($pdoMock);
        $result = $userAuth->emailExists(self::EMAIL);

        $this->assertFalse($result);
    }
}