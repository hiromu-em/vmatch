<?php
declare(strict_types=1);

namespace Request;

class Request
{
    private array $get;
    private array $post;
    private array $server;

    /**
     * @param array|null $get
     * @param array|null $post
     */
    public function __construct(?array $get = null, ?array $post = null)
    {
        $this->get = $get ?? $_GET;
        $this->post = $post ?? $_POST;
    }

    /**
     * 指定されたキーの入力値を取得
     * @param string $key
     * @return mixed|null
     */
    public function input(string $key)
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        return null;
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
