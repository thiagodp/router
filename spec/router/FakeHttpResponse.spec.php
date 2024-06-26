<?php
require_once __DIR__ . '/../../src/FakeHttpResponse.php';

use phputil\router\FakeHttpResponse;

describe( 'FakeHttpResponse', function() {

    describe('#withHeader', function() {

        it('should add the header', function() {
            $req = new FakeHttpResponse();
            $req->header('Foo', 'bar');
            expect( $req->getHeader('Foo') )->toBe('bar');
        } );

    });
        
} );
?>