<?php

namespace Schnittstabil\Harmonizer;

/**
 * Harmonizer.
 */
class Harmonizer
{
    public $server;

    /**
     * Create a new Harmonizer instance.
     *
     * @param array $server array to infer
     */
    public function __construct(array &$server)
    {
        $this->server = &$server;
    }

    /**
     * Add entry to an array iff it doesn't exist an entry with the same key.
     *
     * @param array $array array of entries
     * @param mixed $key   key of new entry
     * @param mixed $value value of new entry
     */
    private function arrayAdd(array &$array, $key, $value)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }
    }

    /**
     * Infering (in-place) missing REDIRECT_ variables in `$server`.
     *
     * @return static
     */
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

    /**
     * Infering (in-place) missing user variables in `$server`.
     *
     * @return static
     */
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

    /**
     * Infering (in-place) missing authorization variables in `$server`.
     *
     * @return static
     */
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

    /**
     * Infering (in-place) missing variables in `$server`.
     *
     * @param array $server array to infer
     *
     * @return array $server
     */
    public static function harmonize(array &$server)
    {
        return (new self($server))
        ->harmonizeRedirectVariables()
        ->harmonizeUserVariables()
        ->harmonizeHttpAuth();
    }
}
