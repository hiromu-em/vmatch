<?php
declare(strict_types=1);

namespace Core;

class Request
{
    private array $get;

    private array $post;
    
    /**
     * サーバー情報および実行時の環境情報
     */
    private array $server;

    public function __construct(
        ?array $get = null,
        ?array $post = null,
        ?array $server = null
    ) {
        $this->get = $get ?? [];
        $this->post = $post ?? [];
        $this->server = $server ?? [];
    }

    /**
     * 入力値を取得(GET, POST)
     */
    public function input(string $key): string
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        return '';
    }

    /**
     * HTTP メソッドを取得
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
}
