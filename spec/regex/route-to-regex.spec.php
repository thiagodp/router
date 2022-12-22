<?php
require_once 'src/regex.php';

use function phputil\router\routeToRegex;

describe( 'routeToRegex', function() {

    describe( 'slash handling', function() {

        it( 'converts an empty route into an optional slash', function() {
            $r = routeToRegex( '' );
            expect( '' )->toMatch( $r->regex );
            expect( '/' )->toMatch( $r->regex );
        } );

        it( 'converts a single slash into an optional slash', function() {
            $r = routeToRegex( '/' );
            expect( '' )->toMatch( $r->regex );
            expect( '/' )->toMatch( $r->regex );
        } );

        it( 'converts a route with multiple slashes', function() {
            $r = routeToRegex( '/foo/1/bar/zoo/1/' );
            expect( '/foo/1/bar/zoo/1/' )->toMatch( $r->regex );
        } );

        it( 'turns a final slash optional', function() {
            $r = routeToRegex( '/foo/' );
            expect( '/foo' )->toMatch( $r->regex );
            expect( '/foo/' )->toMatch( $r->regex );
        } );

        it( 'adds an optional, final slash', function() {
            $r = routeToRegex( '/foo/bar' );
            expect( '/foo/bar' )->toMatch( $r->regex );
            expect( '/foo/bar/' )->toMatch( $r->regex );
        } );

    } );


    describe( 'parameter handling', function() {

        it( 'accepts a parameter without slashes as a root url', function() {
            $r = routeToRegex( ':id' );
            expect( $r->params )->toBe( [ 'id' ] );
            expect( 'foo' )->toMatch( $r->regex );
            expect( 'foo/' )->toMatch( $r->regex );
            expect( '123' )->toMatch( $r->regex );
            expect( '123/' )->toMatch( $r->regex );
        } );

        it( 'accepts a parameter in the root url', function() {
            $r = routeToRegex( '/:id' );
            expect( $r->params )->toBe( [ 'id' ] );
            expect( '/foo' )->toMatch( $r->regex );
            expect( '/foo/' )->toMatch( $r->regex );
            $r = routeToRegex( '/:id/' );
            expect( $r->params )->toBe( [ 'id' ] );
            expect( '/foo' )->toMatch( $r->regex );
            expect( '/foo/' )->toMatch( $r->regex );
        } );

        it( 'accepts a parameter in the root url with a final slash', function() {
            $r = routeToRegex( '/:id/' );
            expect( $r->params )->toBe( [ 'id' ] );
            expect( '/foo' )->toMatch( $r->regex );
            expect( '/foo/' )->toMatch( $r->regex );
        } );

        it( 'accepts non consecutive parameters, starting with a parameter', function() {
            $r = routeToRegex( '/:a/foo/:b/bar/:c' );
            expect( $r->params )->toBe( [ 'a', 'b', 'c' ] );
            expect( '/10/foo/20/bar/30' )->toMatch( $r->regex );
            expect( '/A1/foo/2B/bar/C3C/' )->toMatch( $r->regex );
        } );

        it( 'accepts non consecutive parameters, starting with a route', function() {
            $r = routeToRegex( '/foo/:a/bar/:b/zoo/:c/zzz' );
            expect( $r->params )->toBe( [ 'a', 'b', 'c' ] );
            expect( '/foo/10/bar/20/zoo/30/zzz' )->toMatch( $r->regex );
            expect( '/foo/A1/bar/2B/zoo/C3C/zzz' )->toMatch( $r->regex );
        } );

        it( 'accepts consecutive parameters', function() {
            $r = routeToRegex( ':a/:b/:c' );
            expect( $r->params )->toBe( [ 'a', 'b', 'c' ] );
            expect( 'foo/bar/zoo' )->toMatch( $r->regex );
        } );

    } );


    describe( 'asterisk handling', function() {

        describe( 'only an asterisk', function() {

            beforeAll( function() {
                $this->r = routeToRegex( '*' );
            } );

            it( 'accepts empty', function() {
                expect( '' ) ->toMatch( $this->r->regex );
            } );

            it( 'accepts a slash', function() {
                expect( '/' ) ->toMatch( $this->r->regex );
            } );

            it( 'accepts valid URL characters', function() {
                expect( '#!foo?hèllö=1&x=W,&z=[]&a=%20;.' ) ->toMatch( $this->r->regex );
            } );

        } );

    } );


    describe( 'url match', function() {

        it( 'keeps a quantifier inside brackets', function() {
            $r = routeToRegex( '/hel{2}o' );
            expect( '/hello' ) ->toMatch( $r->regex );
        } );

        it( 'keeps the quantifier "+"', function() {
            $r = routeToRegex( '/hel+o' );
            expect( '/hello' ) ->toMatch( $r->regex );
            expect( '/helllo' ) ->toMatch( $r->regex );
        } );

    } );

} );

?>