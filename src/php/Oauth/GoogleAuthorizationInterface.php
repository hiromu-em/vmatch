<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

/**
 * Google認可インターフェース
 */
interface GoogleAuthorizationInterface
{
    public const string GOOGLE_CALLBACK = '/src/php/Oauth/googleCallback.php';

    /**
     * Google Clientの設定
     * @return \Google\Client Google Clientオブジェクト
     */
    public function setClient(): \Google\Client;

    /**
     * アクセストークンの設定
     * @param array $accessToken アクセストークン
     * @return void
     */
    public function setAccessToken(array $accessToken): void;

    /**
     * 認可サーバーのURLを生成
     * @param string $state stateパラメーター
     * @return string 認可サーバーのURL
     */
    public function createAuthUrl(): string;

    /**
     * stateパラメーターの生成
     * @return string 生成されたstateパラメーター
     */
    public function createState(): string;

    /**
     * stateパラメーターの設定
     * @param string $state stateパラメーター
     * @return void
     */
    public function setClientState(string $state): void;

    /**
     * stateパラメーターの設定
     * @param string $state stateパラメーター
     * @return void
     */
    public function setState(string $state): void;

    /**
     * stateパラメーターの取得
     * @return string stateパラメーター
     */
    public function getState(): string;

    /**
     * コード検証者の生成
     * @return string コード検証者
     */
    public function generateCodeVerifier(): string;

    /**
     * stateパラメーターの検証
     * @param string $state stateパラメーター
     * @return bool 検証結果
     */
    public function verifyState(string $state): void;

    /**
     * 認可コードをアクセストークンと交換
     * @param string $code 認可コード
     * @param string $codeVerifier コード検証者
     * @return void
     */
    public function fetchAccessToken(string $code, string $codeVerifier): void;

    /**
     * アクセストークンの取得
     * @return void
     */
    public function getAccessToken(): array;

    /**
     * IDトークンの取得
     * @return array|false IDトークン情報
     */
    public function getIdToken(): array|false;
}