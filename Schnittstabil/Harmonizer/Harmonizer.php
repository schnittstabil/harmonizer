<?php

namespace Schnittstabil\Harmonizer;

class Harmonizer
{
    public static function harmonize(&$server = null)
    {
        if (is_null($server)) {
            $server = &$_SERVER;
        }
        self::harmonizeRedirectVariables($server);
        self::harmonizeUserVariables($server);

        return $server;
    }

    public static function harmonizeRedirectVariables(&$server = null)
    {
        if (is_null($server)) {
            $server = &$_SERVER;
        }
        foreach (array_keys($server) as $redirectKey) {
            $key = $redirectKey;
            while (substr($key, 0, 9) === 'REDIRECT_') {
                $key = substr($key, 9);
                if (!isset($server[$key])) {
                    $server[$key] = $server[$redirectKey];
                } else {
                    $redirectKey = $key;
                }
            }
        }

        return $server;
    }

    public static function harmonizeUserVariables(&$server = null)
    {
        if (is_null($server)) {
            $server = &$_SERVER;
        }
        if (!isset($server['REMOTE_USER']) && isset($server['PHP_AUTH_USER'])) {
            $server['REMOTE_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['REMOTE_USER']) && !isset($server['PHP_AUTH_USER'])) {
            $server['PHP_AUTH_USER'] = $server['REMOTE_USER'];
        }
        if (isset($server['HTTP_AUTHORIZATION'])) {
            $auth = $server['HTTP_AUTHORIZATION'];
            if ('Basic ' === substr($auth, 0, 6)) {
                list($username, $password) = explode(':', base64_decode(substr($auth, 6)));
                if (!isset($server['AUTH_TYPE'])) {
                    $server['AUTH_TYPE'] = 'Basic';
                }
                if (!isset($server['REMOTE_USER'])) {
                    $server['REMOTE_USER'] = $username;
                }
                if (!isset($server['PHP_AUTH_USER'])) {
                    $server['PHP_AUTH_USER'] = $username;
                }
                if (!isset($server['PHP_AUTH_PW'])) {
                    $server['PHP_AUTH_PW'] = $password;
                }
            } elseif (preg_match('/^Digest .*username="(?P<username>[^"]*)".*$/', $auth, $matches)) {
                if (!isset($server['AUTH_TYPE'])) {
                    $server['AUTH_TYPE'] = 'Digest';
                }
                if (!isset($server['PHP_AUTH_DIGEST'])) {
                    $server['PHP_AUTH_DIGEST'] = substr($auth, 7);
                }
                if (!isset($server['REMOTE_USER'])) {
                    $server['REMOTE_USER'] = $matches['username'];
                }
                if (!isset($server['PHP_AUTH_USER'])) {
                    $server['PHP_AUTH_USER'] = $matches['username'];
                }
            }
        }

        return $server;
    }
}
