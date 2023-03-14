<?php
require_once __DIR__ . '/../../src/regex.php';

use function phputil\router\extractVariables;

describe( 'extractVariables', function() {

    it( 'returns false and an empty array when the URL does not match the route', function() {
        $route = '/bar';
        $url = '/foo';
        list( $r, $v ) = extractVariables( $url, $route );
        expect( $r )->toBe( false );
        expect( $v )->toBeEmpty();
    } );

    it( 'returns true and an array with the route variable and URL value', function() {
        $route = '/:x';
        $url = '/foo';
        list( $r, $v ) = extractVariables( $url, $route );
        expect( $r )->toBe( true );
        expect( array_key_exists( 'x', $v ) )->toBeTruthy();
        expect( $v[ 'x' ] )->toBe( 'foo' );
    } );

    it( 'extracts all the route variables and URL values - URL starting with route', function() {
        $route = '/foo/:x/bar/:y';
        $url = '/foo/123/bar/hello-world';
        list( $r, $v ) = extractVariables( $url, $route );
        expect( $r )->toBe( true );
        expect( array_key_exists( 'x', $v ) )->toBeTruthy();
        expect( array_key_exists( 'y', $v ) )->toBeTruthy();
        expect( $v[ 'x' ] )->toBe( '123' );
        expect( $v[ 'y' ] )->toBe( 'hello-world' );
    } );

    it( 'extracts all the route variables and URL values - URL starting with variable', function() {
        $route = '/:x/bar/:y';
        $url = '/123/bar/hello-world';
        list( $r, $v ) = extractVariables( $url, $route );
        expect( $r )->toBe( true );
        expect( array_key_exists( 'x', $v ) )->toBeTruthy();
        expect( array_key_exists( 'y', $v ) )->toBeTruthy();
        expect( $v[ 'x' ] )->toBe( '123' );
        expect( $v[ 'y' ] )->toBe( 'hello-world' );
    } );

} );

?>