<?php

namespace Schnittstabil\Harmonizer;

class Harmonizer
{
    public $server;

    public function __construct(&$server)
    {
        $this->server = &$server;
    }

    private function arrayAdd(&$array, $key, $value)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }
    }

    public function harmonizeRedirectVariables()
    {
        foreach (array_keys($this->server) as $redirectKey) {
            $key = $redirectKey;
            while (substr($key, 0, 9) === 'REDIRECT_') {
                $key = substr($key, 9);
                if (isset($this->server[$key])) {
                    $redirectKey = $key;
                    continue;
                }
                $this->server[$key] = $this->server[$redirectKey];
            }
        }

        return $this;
    }

    public function harmonizeUserVariables()
    {
        if (isset($this->server['PHP_AUTH_USER'])) {
            $this->arrayAdd($this->server, 'REMOTE_USER', $this->server['PHP_AUTH_USER']);
        }
        if (isset($this->server['REMOTE_USER'])) {
            $this->arrayAdd($this->server, 'PHP_AUTH_USER', $this->server['REMOTE_USER']);
        }

        return $this;
    }

    public function harmonizeHttpAuth()
    {
        if (isset($this->server['HTTP_AUTHORIZATION'])) {
            $auth = $this->server['HTTP_AUTHORIZATION'];
            if ('Basic ' === substr($auth, 0, 6)) {
                list($username, $password) = explode(':', base64_decode(substr($auth, 6)));
                $this->arrayAdd($this->server, 'AUTH_TYPE', 'Basic');
                $this->arrayAdd($this->server, 'REMOTE_USER', $username);
                $this->arrayAdd($this->server, 'PHP_AUTH_USER', $username);
                $this->arrayAdd($this->server, 'PHP_AUTH_PW', $password);
            } elseif (preg_match('/^Digest .*username="(?P<username>[^"]*)".*$/', $auth, $matches)) {
                $this->arrayAdd($this->server, 'AUTH_TYPE', 'Digest');
                $this->arrayAdd($this->server, 'PHP_AUTH_DIGEST', substr($auth, 7));
                $this->arrayAdd($this->server, 'REMOTE_USER', $matches['username']);
                $this->arrayAdd($this->server, 'PHP_AUTH_USER', $matches['username']);
            }
        }

        return $this;
    }

    public static function harmonize(&$server)
    {
        return (new self($server))
        ->harmonizeRedirectVariables()
        ->harmonizeUserVariables()
        ->harmonizeHttpAuth();
    }
}
