<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi;

use Illuminate\Support\Facades\Config;
use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Auth
{
    protected $request;

    protected $session;

    protected $config;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->config = Config::get('one-c.auth');
        $this->setSession();
    }

    /**
     * @throws ExceptionOneCApi
     */
    public function isAuth(): void
    {
        $user = $this->session->get($this->config['session'], null);

 /**
        if(!$user) {
            throw new ExceptionOneCApi('OneCApi: Auth error: not found session=' . $this->config['session']);
        }
        if ($user != $this->config['login']) {
            throw new ExceptionOneCApi('OneCApi: Auth error');
        }
 */

    }

    /**
     * @return string
     * @throws ExceptionOneCApi
     */
    public function auth()
    {
        $login = $this->request->server->get('PHP_AUTH_USER')?$this->request->server->get('PHP_AUTH_USER'):'';
        $password = $this->request->server->get('PHP_AUTH_PW')?$this->request->server->get('PHP_AUTH_PW'):'';
        $response = new Response();

        if ($login == $this->config['login'] && $password == $this->config['password']) {
            $this->session->save();

            $response->success($this->session->getId());

            if ($this->session instanceof SessionInterface) {
                $this->session->set($this->config['session'], $this->config['login']);
            }
            elseif ($this->session instanceof Session) {
                $this->session->put($this->config['session'], $this->config['login']);
            }
            else {
                throw new ExceptionOneCApi(sprintf('Session is not insatiable interface %s or %s', SessionInterface::class, Session::class));
            }
            $this->session->save();
        }
        else {
            $response->failure();
        }
        return $response->getResponse();
    }

    /**
     *
     */
    private function setSession(): void
    {
        if (!$this->request->getSession()) {
            $session = new \Symfony\Component\HttpFoundation\Session\Session();
            $session->start();
            $this->request->setSession($session);
        }

        $this->session = $this->request->getSession();
    }
}
