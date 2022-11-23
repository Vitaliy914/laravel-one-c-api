<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi;

class Response
{
    /**
     * Набор опций
     * @var array
     */
    protected $options = [];

    /**
     * Добавить опцию
     * @param string $name
     * @param string|null $val
     * @return Response
     */
    public function set(string $name, string $val = null) : Response
    {
        if(!$val)
            $this->options[] = $name;
        else
            $this->options[] = $name . '=' . $val;

        return $this;
    }

    /**
     * Вернуть ответ
     * @return string
     */
    public function getResponse() : string
    {
        if(count($this->options) > 0)
            return implode("\n", $this->options);
        else
            return '';
    }

    /**
     * Готовый ответ failure
     * @return Response
     */
    public function failure() : Response
    {
        $this->set('failure');
        return $this;
    }

    /**
     * Готовый ответ success
     * @param string $val
     * @return $this
     */
    public function success(string $val)
    {
        $this->set('success');
        $this->set('laravel_session');
        $this->set($val);
        $this->set('timestamp', (string)time());

        return $this;
    }
}
