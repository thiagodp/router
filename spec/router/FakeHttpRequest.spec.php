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
        
} );
?>