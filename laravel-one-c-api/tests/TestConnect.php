<?php
namespace Vitaliy914\OneCApi\Tests;

class TestConnect extends Orchestra\Testbench\TestCase
{
    public function testErrorConnect()
    {
        $response = $this->get('/' . config('one-c.exchange_path'));
        $response->assertStatus(500);
    }

    public function testSuccessConnect()
    {
        $response = $this->get(
            '/' . config('one-c.exchange_path') . '?type=catalog&mode=checkauth',
            [
                'PHP_AUTH_USER' => config('one-c.auth.login'),
                'PHP_AUTH_PW'   => config('one-c.auth.password'),
            ]
        );
        $response->assertStatus(200);
    }
}
