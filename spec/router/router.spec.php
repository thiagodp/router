<?php
require_once 'src/request.php';
require_once 'src/router.php';

use \phputil\router\FakeHttpRequest;
use \phputil\router\Router;

use const phputil\router\STATUS_METHOD_NOT_ALLOWED;
use const phputil\router\STATUS_NOT_FOUND;

describe( 'router', function() {

    $this->fakeReq = null;
    $this->router = null;

    beforeEach( function() {
        $this->fakeReq = new FakeHttpRequest();
        $this->router = new Router();
    } );

    afterEach( function() {
        $this->fakeReq = null;
        $this->router = null;
    } );

    describe( 'direct methods', function () {

        it( 'should invoke the given callback when it finds the route', function() {

            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            $this->router->get( '/foo', $callback );
            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // it should call $calllback

            expect( $ok )->toBe( true );
            expect( $count )->toBeGreaterThan( 0 );
        } );

        it( 'should not invoke the given callback when it does not find the route', function() {

            $this->fakeReq->withURL( '/bar' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            $this->router->get( '/foo', $callback );
            list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // it should NOT call $calllback

            expect( $ok )->toBe( false );
            expect( $count )->toBe( 0 );
            expect( $res->isStatus( STATUS_NOT_FOUND ) )->toBeTruthy();
        } );


        it( 'should not invoke the given callback when it does not find the right METHOD', function() {

            $this->fakeReq->withURL( '/foo' )->withMethod( 'POST' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            $this->router->get( '/foo', $callback );
            list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // it should NOT call $calllback

            expect( $ok )->toBe( false );
            expect( $count )->toBe( 0 );
            expect( $res->isStatus( STATUS_METHOD_NOT_ALLOWED ) )->toBeTruthy();
        } );

    } );

    describe( 'under a single group', function() {

        it( 'should invoke the given callback when it finds the route', function() {

            // Faking the request
            $this->fakeReq->withURL( '/foo/bar' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            // Making the expectation
            $this->router->group( '/foo' )->get( '/bar', $callback );
            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );

            expect( $ok )->toBe( true );
            expect( $count )->toBeGreaterThan( 0 );
        } );


        it( 'should not invoke the given callback when it does not find the right METHOD', function() {

            $this->fakeReq->withURL( '/foo/bar' )->withMethod( 'POST' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            $this->router->group( '/foo' )->get( '/bar', $callback );
            list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // it should NOT call $calllback

            expect( $ok )->toBe( false );
            expect( $count )->toBe( 0 );
            expect( $res->isStatus( STATUS_METHOD_NOT_ALLOWED ) )->toBeTruthy();
        } );


        it( 'should not find the route with just the group', function() {

            // Faking the request
            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

            // $count = 0;
            // $callback = function( $req, $res ) use ( &$count ) { $count++; };

            // Making the expectation
            $this->router->group( '/foo' );
            list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );

            expect( $ok )->toBe( false );
            expect( $res->isStatus( STATUS_NOT_FOUND ) )->toBeTruthy();
        } );

    } );

    describe( 'under two groups', function() {

        it( 'should invoke the given callback when it finds the route', function() {

            // Faking the request
            $this->fakeReq->withURL( '/foo/bar/zoo' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            // Making the expectation
            $this->router->group( '/foo' )->group( '/bar' )->get( '/zoo', $callback );
            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );

            expect( $ok )->toBe( true );
            expect( $count )->toBeGreaterThan( 0 );
        } );

    } );


} );

?>