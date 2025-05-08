<?php
require_once __DIR__ . '/../../src/RealHttpResponse.php';

use \phputil\router\RealHttpResponse;

describe( 'response', function() {

    $this->resp = null;

    beforeEach( function() {
        $avoidOutput = true;
        $this->resp = new RealHttpResponse( $avoidOutput );
    } );

    describe( 'send', function() {

        it( 'adds any content to the body', function() {
            $r = $this->resp->send( '' )->dump();
            expect( $r[ 'body' ] )->toHaveLength( 1 );
            expect( $r[ 'body' ][ 0 ] )->toBe( '' );
        } );

        it( 'adds an array content as JSON', function() {
            $r = $this->resp->send( [ 'foo' => 'bar', 'zoo' => 1 ] )->dump();
            expect( $r[ 'body' ][ 0 ] )->toBe( '{"foo":"bar","zoo":1}' );
        } );

        it( 'adds an object as JSON', function() {
            $obj = (object) [ 'foo' => 'bar', 'zoo' => 1 ];
            $r = $this->resp->send( $obj )->dump();
            expect( $r[ 'body' ][ 0 ] )->toBe( '{"foo":"bar","zoo":1}' );
        } );

    } );


    describe( 'json', function() {

        it( 'adds a header', function() {
            $r = $this->resp->json( '' )->dump();
            expect( $r[ 'headers' ] )->toHaveLength( 1 );
            expect( $r[ 'body' ] )->toHaveLength( 1 );
            expect( $r[ 'body' ][ 0 ] )->toBe( '' );
        } );

        it( 'transforms an array to a JSON body', function() {
            $r = $this->resp->json( [ 'foo' => 'bar', 'zoo' => 1 ] )->dump();
            expect( $r[ 'body' ][ 0 ] )->toBe( '{"foo":"bar","zoo":1}' );
        } );

        it( 'transforms an object to a JSON body', function() {
            $obj = (object) [ 'foo' => 'bar', 'zoo' => 1 ];
            $r = $this->resp->json( $obj )->dump();
            expect( $r[ 'body' ][ 0 ] )->toBe( '{"foo":"bar","zoo":1}' );
        } );

    } );


    describe( 'redirect', function() {

        it( 'adds the given path to a Location header', function() {
            $r = $this->resp->redirect( 301, "/foo" )->dump();
            expect( $r[ 'statusCode' ] )->toBe( 301 );
            expect( $this->resp->getHeader( 'Location' ) )->toBe( '/foo' );
        } );

    } );


    describe( 'cookie', function() {

        it( 'adds a Set-Cookie header', function() {
            $this->resp->cookie( 'foo', 'bar' );
            $value = $this->resp->getHeader( 'Set-Cookie' );
            expect( $value )->toBe( 'foo=bar' );
        } );

        it( 'adds the cookie option "Secure" without a value it is set to 1', function() {
            $this->resp->cookie( 'foo', 'bar', [ 'secure' => 1 ] );
            $value = $this->resp->getHeader( 'Set-Cookie' );
            expect( $value )->toBe( 'foo=bar; Secure;' );
        } );

        it( 'add the cookie option "Secure" without a value it is set to true', function() {
            $this->resp->cookie( 'foo', 'bar', [ 'secure' => true ] );
            $value = $this->resp->getHeader( 'Set-Cookie' );
            expect( $value )->toBe( 'foo=bar; Secure;' );
        } );

        it( 'adds the cookie option HttpOnly without a value it is set to 1', function() {
            $this->resp->cookie( 'foo', 'bar', [ 'httpOnly' => 1 ] );
            $value = $this->resp->getHeader( 'Set-Cookie' );
            expect( $value )->toBe( 'foo=bar; HttpOnly;' );
        } );

        it( 'adds the cookie option HttpOnly without a value it is set to true', function() {
            $this->resp->cookie( 'foo', 'bar', [ 'httpOnly' => true ] );
            $value = $this->resp->getHeader( 'Set-Cookie' );
            expect( $value )->toBe( 'foo=bar; HttpOnly;' );
        } );

        it( 'adds other options with their corresponding values', function() {

            $this->resp->cookie( 'foo', 'bar', [
                'domain' => 'sub.example.com',
                'path' => '/dir',
                'maxAge' => 0,
                'secure' => true,
                'httpOnly' => true
            ] );

            $value = $this->resp->getHeader( 'Set-Cookie' );

            expect( $value )->toBe( 'foo=bar; Domain=sub.example.com; Path=/dir; Max-Age=0; Secure; HttpOnly;' );
        } );

    } );


    describe( 'clearCookie', function() {

        it( 'clears the cookie value', function() {

            $this->resp->clearCookie( 'foo', [
                'domain' => 'sub.example.com',
                'path' => '/dir',
                'maxAge' => 0,
                'secure' => true,
                'httpOnly' => true
            ] );

            $value = $this->resp->getHeader( 'Set-Cookie' );

            expect( $value )->toBe( 'foo=; Domain=sub.example.com; Path=/dir; Max-Age=0; Secure; HttpOnly;' );
        } );

    } );

} );

?>