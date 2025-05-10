<?php

use Symfony\Component\HttpClient\HttpClient;

const SERVER = [ 'domain' => 'localhost', 'port' => '9999' ];

describe( 'server', function(){

    $rootDir = dirname( __FILE__ );
    $this->server = SERVER[ 'domain' ] . ':' . SERVER[ 'port' ];
    $this->process = null;


    beforeAll( function() use ( $rootDir ) {

        $cmd = "cd $rootDir && php -S " . $this->server;
        // echo 'COMMAND ' . $cmd, PHP_EOL;
        $spec = [
            [ 'pipe', 'r' ], // stdin
            [ 'pipe', 'w' ], // stdout
            [ 'pipe', 'w' ], // stderr
        ];
        $this->process = @proc_open( $cmd, $spec, $exitPipes );
        if ( $this->process === false ) {
            throw new Exception( 'Cannot run the HTTP server.' );
        }

        // HTTP Client
        $this->url = 'http://' . $this->server;
        $this->client = HttpClient::create();
    } );


    afterAll( function() {
        $this->client = null;

        if ( $this->process === false ) {
            return;
        }
        $exitCode = proc_terminate( $this->process ) ? 0 : -1;
        if ( $exitCode < 0 ) {
            throw new Exception( 'Cannot close the HTTP server.' );
        }
    } );


    it( 'returns 404 when a route is not found', function() {
        $response = $this->client->request( 'GET', $this->url . '/unexisting', [] );
        expect( $response->getStatusCode() )->toBe( 404 );
    } );


    it( 'can get a JSON content', function() {

        $response = $this->client->request( 'GET', $this->url . '/customers', [] );
        expect( $response->getStatusCode() )->toBe( 200 );

        $header = ( $response->getHeaders( false )[ 'content-type' ] ?? [ '' ] ) [ 0 ];
        expect( $header )->toContain( 'application/json' );
    } );


    describe( 'middleware', function() {

        it( 'can retrieve a header that was set by a route middleware', function() {

            $response = $this->client->request( 'GET', $this->url . '/fruits', [] );
            expect( $response->getStatusCode() )->toBe( 200 );

            $header = ( $response->getHeaders( false )[ 'foo' ] ?? [ '' ] ) [ 0 ];
            expect( $header )->toContain( 'Bar' );
        } );


        it( 'can retrieve a header that was set by a global middleware', function() {

            $response = $this->client->request( 'GET', $this->url . '/fruits', [] );
            expect( $response->getStatusCode() )->toBe( 200 );

            $header = ( $response->getHeaders( false )[ 'hello' ] ?? [ '' ] ) [ 0 ];
            expect( $header )->toContain( 'World' );
        } );


        it( 'can retrieve a header that was set by a global middleware that stops the request', function() {

            $response = $this->client->request( 'OPTIONS', $this->url . '/fruits', [] );
            expect( $response->getStatusCode() )->toBe( 204 );

            $header = ( $response->getHeaders( false )[ 'content-type' ] ?? [ '' ] ) [ 0 ];
            expect( $header )->not->toContain( 'application/json' );

            $header = ( $response->getHeaders( false )[ 'x' ] ?? [ '' ] ) [ 0 ];
            expect( $header )->toContain( 'Y' );

            $header = ( $response->getHeaders( false )[ 'method' ] ?? [ '' ] ) [ 0 ];
            expect( $header )->toBe( 'OPTIONS' );
        } );

    } );

} );
