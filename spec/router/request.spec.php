<?php
require_once __DIR__ . '/../../src/request.php';

use function phputil\router\analizeBody;
use function phputil\router\extractCookies;
use function phputil\router\headerWithName;
use function phputil\router\removeQueries;

describe( 'request', function() {

    describe( '#removeQueries', function() {

        it( 'removes any content after question mark', function() {
            $r = removeQueries( 'foo?bar=1&zoo=hello' );
            expect( $r )->toBe( 'foo' );
        } );

    } );

    describe( '#extractCookies', function() {

        it( 'extracts case-insentively', function() {
            $r = extractCookies( [
                'Content-Type' => 'application/json',
                'cookie' => 'hello=world',
                'Cookie' => 'foo=bar',
            ] );
            expect( $r )->toHaveLength( 2 );
            list( $first, $second ) = array_keys( $r );
            list( $firstV, $secondV ) = array_values( $r );
            expect( $first )->toBe( 'hello' );
            expect( $second )->toBe( 'foo' );
            expect( $firstV )->toBe( 'world' );
            expect( $secondV )->toBe( 'bar' );
        } );

    } );


    describe( '#headerWithName', function() {

        it( 'gets the header case-insentively', function() {
            $r = headerWithName( 'content-type', [
                'Cookie' => 'foo=bar',
                'Content-Type' => 'application/json'
            ] );
            expect( $r )->toBe( 'application/json' );
        } );

    } );


    describe( '#analyzeBody', function() {

        it( 'returns an array when the content type is form', function () {
            $r = analizeBody( 'application/x-www-form-urlencoded', 'name=Alice&age=21' );
            expect( gettype( $r ) )->toBe( 'array' );
            expect( array_key_exists( 'name', $r ) )->toBeTruthy();
            expect( array_key_exists( 'age', $r ) )->toBeTruthy();
            expect( $r[ 'name' ] )->toBe( 'Alice' );
            expect( $r[ 'age' ] )->toBe( '21' );
        } );

        it( 'returns a json object when the content type is json with an object', function () {
            $r = analizeBody( 'application/json', '{"name":"Alice","age":21}' );
            expect( gettype( $r ) )->toBe( 'object' );
            expect( $r->{ 'name' } )->toBe( 'Alice' );
            expect( $r->{ 'age' } )->toBe( 21 );
        } );

        it( 'returns a json array when the content type is json with an array', function () {
            $r = analizeBody( 'application/json', '[ "Alice", 21 ]' );
            expect( gettype( $r ) )->toBe( 'array' );
            expect( $r[ 0 ] )->toBe( 'Alice' );
            expect( $r[ 1 ] )->toBe( 21 );
        } );

    } );

} );