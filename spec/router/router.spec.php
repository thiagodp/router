<?php
require_once 'src/fake-http-request.php';
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

    describe( 'callback to HTTP method', function () {

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


        describe( 'middleware', function() {

            it( 'should not call the next callback when the prior callback indicates a stop', function() {

                $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

                $callback1 = function( $req, $res, &$stop ) { $stop = true; };

                $count = 0;
                $callback2 = function( $req, $res ) use ( &$count ) { $count++; };

                $this->router->get( '/foo', $callback1, $callback2 );
                list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // It should NOT call $calllback

                expect( $ok )->toBe( true );
                expect( $count )->toBe( 0 );
                expect( $res->isStatus( STATUS_NOT_FOUND ) )->toBeFalsy();
            } );


            it( 'should call all the callbacks when the prior callback indicates a stop', function() {

                $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

                $count = 0;
                $callback1 = function( $req, $res ) use ( &$count ) { $count++; };
                $callback2 = function( $req, $res ) use ( &$count ) { $count++; };
                $callback3 = function( $req, $res ) use ( &$count ) { $count++; };

                $this->router->get( '/foo', $callback1, $callback2, $callback3 );
                list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] ); // It should NOT call $calllback

                expect( $ok )->toBe( true );
                expect( $count )->toBe( 3 );
                expect( $res->isStatus( STATUS_NOT_FOUND ) )->toBeFalsy();
            } );

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


        it( 'should find the route in a group with an http method that points to the root', function() {
            // Faking the request
            $this->fakeReq->withURL( '/foo' )->withMethod( 'POST' );

            $count = 0;
            $callback = function( $req, $res ) use ( &$count ) { $count++; };

            // Making the expectation
            $this->router->group( '/foo' )->post( '/', $callback );
            list( $ok, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );

            expect( $ok )->toBe( true );
            expect( $count )->toBe( 1 );
        } );


        it( 'differs a route to the root from a route with a parameter', function() {

            $count1 = 0;
            $callback1 = function( $req, $res ) use ( &$count1 ) { $count1++; };
            $count2 = 0;
            $callback2 = function( $req, $res ) use ( &$count2 ) { $count2++; };

            // Making the expectation
            $this->router->group( '/foo' )
                ->get( '/', $callback1 )
                ->get( '/:bar', $callback2 );

            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );
            list( $ok1, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok1 )->toBe( true );
            expect( $count1 )->toBe( 1 );
            expect( $count2 )->toBe( 0 );

            $this->fakeReq->withURL( '/foo/Íon' )->withMethod( 'GET' );
            list( $ok2, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok2 )->toBe( true );
            expect( $count2 )->toBe( 1 );
            expect( $count1 )->toBe( 1 );
        } );


        it( 'differs two routes to the root with different methods', function() {

            $count1 = 0;
            $callback1 = function( $req, $res ) use ( &$count1 ) { $count1++; };
            $count2 = 0;
            $callback2 = function( $req, $res ) use ( &$count2 ) { $count2++; };

            // Making the expectation
            $this->router->group( '/foo' )
                ->get( '/', $callback1 )
                ->post( '/', $callback2 );

            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );
            list( $ok1, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok1 )->toBe( true );
            expect( $count1 )->toBe( 1 );
            expect( $count2 )->toBe( 0 );

            $this->fakeReq->withURL( '/foo' )->withMethod( 'POST' );
            list( $ok2, , $res ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok2 )->toBe( true );
            expect( $count2 )->toBe( 1 );
            expect( $count1 )->toBe( 1 );
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


    describe( 'middleware', function() {

        it( 'must be called', function() {
            // Faking the request
            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res, &$stop ) use ( &$count ) { $count++; };
            $this->router
                ->use( $callback )
                ->get( '/foo' );

            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok )->toBe( true );
            expect( $count )->toBeGreaterThan( 0 );
        } );

        it( 'can stop the router', function() {
            // Faking the request
            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

            $count = 0;
            $callback = function( $req, $res, &$stop ) use ( &$count ) { $count++; $stop = true; };
            $countRoute = 0;
            $callbackRoute = function( $req, $res ) use ( &$countRoute ) { $countRoute++; };
            $this->router
                ->use( $callback )
                ->get( '/foo', $callbackRoute );

            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok )->toBe( false );
            expect( $count )->toBeGreaterThan( 0 );
            expect( $countRoute )->toBe( 0 );
        } );

        it( 'works per group', function() {
            // Faking the request
            $this->fakeReq->withURL( '/foo' )->withMethod( 'GET' );

            $count = 0;
            $callback1 = function( $req, $res, &$stop ) use ( &$count ) { $count++; };
            $callback2 = function( $req, $res, &$stop ) use ( &$count ) { $count++; };

            $this->router->group( '/foo' )
                ->use( $callback1 )
                ->post( '/' )
                ->get( '/', $callback2 );

            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok )->toBe( true );
            expect( $count )->toBe( 2 );
        } );


        it( 'is not called when defined for other group', function() {
            // Faking the request
            $this->fakeReq->withURL( '/bar' )->withMethod( 'GET' );

            $count = 0;
            $callback1 = function( $req, $res, &$stop ) use ( &$count ) { $count += 10; };
            $callback2 = function( $req, $res, &$stop ) use ( &$count ) { $count += 20; };
            $callback3 = function( $req, $res, &$stop ) use ( &$count ) { $count += 1; };

            $this->router
                ->use( $callback1 )
                ->group( '/foo' )
                    ->use( $callback2 )
                    ->get( '/' )
                    ->end()
                ->get( '/bar', $callback3 );

            list( $ok ) = $this->router->listen( '', [ 'req' => $this->fakeReq ] );
            expect( $ok )->toBe( true );
            expect( $count )->toBe( 11 );
        } );

    } );


} );

?>