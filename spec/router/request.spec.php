<?php

use function phputil\router\removeQueries;

describe( 'request', function() {

    describe( 'removeQueries', function() {

        it( 'removes any content after question mark', function() {
            $r = removeQueries( 'foo?bar=1&zoo=hello' );
            expect( $r )->toBe( 'foo' );
        } );

    } );

} );