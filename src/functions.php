<?php

namespace Schnittstabil\Harmonizer;

if (!function_exists('Schnittstabil\Harmonizer\harmonize')) {
    /**
     * Infering (in-place) missing variables in `$server`.
     *
     * @param array $server array to infer
     *
     * @return array $server
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    function harmonize(array &$server)
    {
        return Harmonizer::harmonize($server)->server;
    }
}
