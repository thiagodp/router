<?php
require_once __DIR__ . '/../../src/mime.php';

use function phputil\router\compareMimes;

describe( 'compareMimes', function() {

    it( 'recognizes asterisc as any for the specific part', function() {
        $r = compareMimes( [ 'application/*' ], [ 'application/json' ] );
        expect( $r )->toBeTruthy();
    } );

    it( 'recognizes asterisc as any for the main part', function() {
        $r = compareMimes( [ '*/json' ], [ 'application/json' ] );
        expect( $r )->toBeTruthy();
    } );

    it( 'recognizes asterisc in both parts as any mime', function() {
        $r = compareMimes( [ '*/*' ], [ 'application/json', 'image/jpeg' ] );
        expect( $r )->toBeTruthy();
    } );

} );
?>