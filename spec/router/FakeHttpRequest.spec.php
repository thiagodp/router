<?php
require_once __DIR__ . '/../../src/FakeHttpRequest.php';

use phputil\router\FakeHttpRequest;

describe( 'FakeHttpRequest', function() {

    describe('#withHeader', function() {

        it('should add the header', function() {
            $req = new FakeHttpRequest();
            $req->withHeader('Foo', 'bar');
            expect( $req->header('Foo') )->toBe('bar');
        } );

    });


    describe( 'headers', function() {

        it( 'returns all headers by default', function() {
            $req = new FakeHttpRequest();
            $req->withHeaders( [
                'foo' => 'bar',
                'zoo' => [ 'hello', 'world' ]
            ] );
            $r = $req->headers();

            expect( $r[ 'foo' ] )->toBe('bar' );
            expect( $r[ 'zoo' ] )->toBe('hello' );
        } );
    } );

} );
?>