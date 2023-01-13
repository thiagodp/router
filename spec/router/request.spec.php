<?php

use function phputil\router\extractCookies;
use function phputil\router\headerWithName;
use function phputil\router\removeQueries;

describe( 'request', function() {

    describe( 'removeQueries', function() {

        it( 'removes any content after question mark', function() {
            $r = removeQueries( 'foo?bar=1&zoo=hello' );
            expect( $r )->toBe( 'foo' );
        } );

    } );

    describe( 'extractCookies', function() {

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


    describe( 'headerWithName', function() {

        it( 'gets the header case-insentively', function() {
            $r = headerWithName( 'content-type', [
                'Cookie' => 'foo=bar',
                'Content-Type' => 'application/json'
            ] );
            expect( $r )->toBe( 'application/json' );
        } );

    } );

} );