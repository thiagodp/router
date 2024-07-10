<?php
require_once __DIR__ . '/../../src/FakeHttpResponse.php';

use phputil\router\FakeHttpResponse;

describe( 'FakeHttpResponse', function() {

    it('should add the given header', function() {
        $req = new FakeHttpResponse();
        $req->header('Foo', 'bar');
        expect( $req->getHeader('Foo') )->toBe('bar');
    } );

    it('should a previously set header', function() {
        $req = new FakeHttpResponse();
        $req->header('Foo', 'bar');
        expect( $req->getHeader('Foo') )->toBe('bar');
        $req->removeHeader('Foo');
        expect( $req->getHeader('Foo') )->toBeNull();
    } );

} );
?>