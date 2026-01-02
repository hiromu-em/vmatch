<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;

interface ConfigInterface
{

    /**
     * 環境に応じたデータベース接続の設定を取得
     * @return array データベース接続設定
     */
    public function getDatabaseSettings(): array;

    /**
     * ローカル環境用のデータベース設定を取得
     * @return array データベース接続設定
     */
    public function getLocalDatabaseSettings(): array;

    /**
     * 本番環境用のデータベース設定を取得
     * @return array データベース接続設定
     */
    public function getProductionDatabaseSettings(): array;

    /**
     * データベース設定配列を構築
     * @param string $dsn DSN文字列
     * @param string|false $user ユーザー名
     * @param string|false $password パスワード
     * @return array データベース接続設定
     */
    public function buildDatabaseSettings(string $dsn, $user, $password): array;

    /**
     * 環境変数を取得（テスト時にオーバーライド可能）
     * @param string $key 環境変数のキー
     * @return string|false 環境変数の値
     */
    public function getEnv(string $key);

    /**
     * サーバー変数を取得（テスト時にオーバーライド可能）
     * @param string $key サーバー変数のキー
     * @return string|null サーバー変数の値
     */
    public function getServerVar(string $key): ?string;

    /**
     * ホスト名を取得
     * @return string ホスト名
     */
    public function getHost(): string;

    /**
     * ホスト名を設定
     * @param string $host ホスト名
     * @return string ホスト名
     */
    public function setHost(string $host): string;

    /**
     * ローカル環境かどうかを判定
     * @return bool ローカル環境の場合はtrue
     */
    public function isLocalEnvironment(): bool;

    /**
     * ローカル環境の場合、.envファイルを読み込む
     * @return bool ローカル環境フラグの結果
     */
    public function loadDotenvIfLocal(): bool;

    /**
     * .envファイルを読み込む
     */
    public function loadDotenv(): void;

    /**
     * Dotenvインスタンスを作成（テスト時にオーバーライド可能）
     * @return Dotenv Dotenvインスタンス
     */
    public function createDotenv(): Dotenv;

    /**
     * URLスキームを取得
     * @return string URLスキーム
     */
    public function urlScheme(): string;

    /**
     * Googleクライアントの環境変数を取得
     * @return array Googleクライアントの環境変数
     */
    public function getGoogleClientEnvVars(): array;
}