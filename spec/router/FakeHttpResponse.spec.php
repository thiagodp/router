<?php
require_once __DIR__ . '/../../src/FakeHttpResponse.php';

use phputil\router\FakeHttpResponse;

describe( 'FakeHttpResponse', function() {

    it('adds the given header', function() {
        $req = new FakeHttpResponse();
        $req->header('Foo', 'bar');
        expect( $req->getHeader('Foo') )->toBe('bar');
    } );

    it('removes a previously set header', function() {
        $req = new FakeHttpResponse();
        $req->header('Foo', 'bar');
        expect( $req->getHeader('Foo') )->toBe('bar');
        $req->removeHeader('Foo');
        expect( $req->getHeader('Foo') )->toBeNull();
    } );

    it( 'can count the headers', function() {
        $req = new FakeHttpResponse();
        $req->header( 'Foo', 'bar' );
        $req->header( 'Zoo', 'no' );
        $req->header( 'Foo', 'bar' );
        expect( $req->headerCount('Foo') )->toBe(2);
    } );

    it('can add a header twice', function() {
        $req = new FakeHttpResponse();
        $req->header( 'Foo', 'bar' );
        $req->header( 'Foo', 'car' );
        $req->header( 'Foo', 'bar' );
        expect( $req->getHeader('Foo') )->toBe('bar');
    } );


    // it('can overwrite a header', function() {
    //     $req = new FakeHttpResponse();
    //     $req->header( 'Foo', '1' );
    //     $req->header( 'Bar', '1' );
    //     $req->header( 'Foo', '2', true );
    //     $req->header( 'Bar', '2' );
    //     expect( $req->headerCount('Foo') )->toBe(1);
    //     expect( $req->headerCount('Bar') )->toBe(2);
    //     expect( $req->getHeader('Foo') )->toBe('2');
    // } );

} );
?>