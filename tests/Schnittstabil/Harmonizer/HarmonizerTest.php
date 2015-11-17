<?php

namespace Schnittstabil\Harmonizer;

class HarmonizerTest extends \PHPUnit_Framework_TestCase
{
    protected static $basicAuthAlice;
    protected static $digestAuthBob;

    public static function setUpBeforeClass()
    {
        self::$basicAuthAlice = 'Basic '.base64_encode('Alice:secret');
        self::$digestAuthBob = 'Digest username="Bob", realm="Schnittstabil", nonce="bobsNonce", uri="bobsUri", algorithm=MD5, response="bobsResponse", qop=auth, nc=00000001, cnonce="bobsCnonce"';
    }

    public static function tearDownAfterClass()
    {
        self::$basicAuthAlice = null;
        self::$digestAuthBob = null;
    }

    public function testHarmonizeRedirectVariablesShouldInferREDIRECT_()
    {
        $array = ['REDIRECT_HTTP_AUTHORIZATION' => self::$basicAuthAlice];
        $this->assertTrue($array === (new Harmonizer($array))->harmonizeRedirectVariables()->server);
        $this->assertEquals(self::$basicAuthAlice, $array['REDIRECT_HTTP_AUTHORIZATION']);
        $this->assertEquals(self::$basicAuthAlice, $array['HTTP_AUTHORIZATION']);
    }

    public function testHarmonizeRedirectVariablesShouldInferREDIRECT_REDIRECT_REDIRECT_()
    {
        $array = [];
        $array['REDIRECT_REDIRECT_REDIRECT_HTTP_AUTHORIZATION'] = self::$basicAuthAlice;
        $array['REDIRECT_HTTP_AUTHORIZATION'] = self::$digestAuthBob;

        $this->assertTrue($array === (new Harmonizer($array))->harmonizeRedirectVariables()->server);
        $this->assertEquals(self::$basicAuthAlice, $array['REDIRECT_REDIRECT_REDIRECT_HTTP_AUTHORIZATION']);
        $this->assertEquals(self::$basicAuthAlice, $array['REDIRECT_REDIRECT_HTTP_AUTHORIZATION']);
        $this->assertEquals(self::$digestAuthBob, $array['REDIRECT_HTTP_AUTHORIZATION']);
        $this->assertEquals(self::$digestAuthBob, $array['HTTP_AUTHORIZATION']);
    }

    public function testHarmonizeRedirectVariablesShouldInferREDIRECT__IN_SERVER()
    {
        $this->assertFalse(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']));
        $this->assertFalse(isset($_SERVER['HTTP_AUTHORIZATION']));
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = self::$basicAuthAlice;
        $this->assertArraySubset($_SERVER, (new Harmonizer($_SERVER))->harmonizeRedirectVariables()->server);
        $this->assertArraySubset(['REDIRECT_HTTP_AUTHORIZATION' => self::$basicAuthAlice], $_SERVER);
        $this->assertArraySubset(['HTTP_AUTHORIZATION' => self::$basicAuthAlice], $_SERVER);
    }

    public function testHarmonizeUserVariablesShouldInferREMOTE_USER()
    {
        $array = ['REMOTE_USER' => 'Alice'];
        $this->assertTrue($array === (new Harmonizer($array))->harmonizeUserVariables()->server);
        $this->assertArraySubset(['REMOTE_USER' => 'Alice'], $array);
        $this->assertArraySubset(['PHP_AUTH_USER' => 'Alice'], $array);
    }

    public function testHarmonizeUserVariablesShouldInferPHP_AUTH_USER()
    {
        $array = ['PHP_AUTH_USER' => 'Bob'];
        $this->assertTrue($array === (new Harmonizer($array))->harmonizeUserVariables()->server);
        $this->assertArraySubset(['REMOTE_USER' => 'Bob'], $array);
        $this->assertArraySubset(['PHP_AUTH_USER' => 'Bob'], $array);
    }

    public function testHarmonizeUserVariablesShouldInferPHP_AUTH_USER_IN_SERVER()
    {
        $this->assertFalse(isset($_SERVER['REMOTE_USER']));
        $this->assertFalse(isset($_SERVER['PHP_AUTH_USER']));
        $_SERVER['REMOTE_USER'] = 'Carol';
        $this->assertArraySubset($_SERVER, (new Harmonizer($_SERVER))->harmonizeUserVariables()->server);
        $this->assertArraySubset(['REMOTE_USER' => 'Carol'], $_SERVER);
        $this->assertArraySubset(['PHP_AUTH_USER' => 'Carol'], $_SERVER);
    }

    public function testHarmonizeShouldInferREDIRECT_()
    {
        $array = ['REDIRECT_HTTP_AUTHORIZATION' => self::$basicAuthAlice];
        $this->assertTrue($array === Harmonizer::harmonize($array)->server);
        $this->assertEquals(self::$basicAuthAlice, $array['REDIRECT_HTTP_AUTHORIZATION']);
        $this->assertEquals(self::$basicAuthAlice, $array['HTTP_AUTHORIZATION']);
        $this->assertArraySubset(['AUTH_TYPE' => 'Basic'], $array);
        $this->assertArraySubset(['REMOTE_USER' => 'Alice'], $array);
        $this->assertArraySubset(['PHP_AUTH_USER' => 'Alice'], $array);
        $this->assertArraySubset(['PHP_AUTH_PW' => 'secret'], $array);
    }

    public function testHarmonizeShouldInferREDIRECT__IN_SERVER()
    {
        $this->assertFalse(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']));
        $this->assertFalse(isset($_SERVER['HTTP_AUTHORIZATION']));
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = self::$digestAuthBob;
        $this->assertArraySubset($_SERVER, Harmonizer::harmonize($_SERVER)->server);
        $this->assertArraySubset(['REDIRECT_HTTP_AUTHORIZATION' => self::$digestAuthBob], $_SERVER);
        $this->assertArraySubset(['HTTP_AUTHORIZATION' => self::$digestAuthBob], $_SERVER);
        $this->assertArraySubset(['AUTH_TYPE' => 'Digest'], $_SERVER);
        $this->assertArraySubset(['PHP_AUTH_DIGEST' => substr(self::$digestAuthBob, 7)], $_SERVER);
        $this->assertArraySubset(['REMOTE_USER' => 'Bob'], $_SERVER);
        $this->assertArraySubset(['PHP_AUTH_USER' => 'Bob'], $_SERVER);
    }
}
