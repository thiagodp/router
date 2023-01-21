<?php
namespace phputil\router;

const MIME_ANY      = '*/*';
const ENCODING_ANY  = '*';

function areMimePartCompatible( $first, $second ) {
    return $first == $second || $first == '*' || $second == '*';
}

function areMimeCompatible( $first, $second ) {
    if ( $first === MIME_ANY || $second === MIME_ANY ) {
        return true;
    }
    $firstPieces = \explode( '/', $first ); // type/subtype
    $secondPieces = \explode( '/', $second ); // type/subtype
    $firstCount = \count( $firstPieces );
    $secondCount = \count( $secondPieces );
    if ( $firstCount == 1 && $secondCount == 1 ) { // Subtypes only
        return areMimePartCompatible( $firstPieces[ 0 ], $secondPieces[ 0 ] );
    } else if ( $firstCount == 1 && $secondCount == 2 ) {
        return areMimePartCompatible( $firstPieces[ 0 ], $secondPieces[ 1 ] );
    } else if ( $firstCount == 2 && $secondCount == 1 ) {
        return areMimePartCompatible( $firstPieces[ 1 ], $secondPieces[ 0 ] );
    } else if ( $firstCount == 2 && $secondCount == 2 ) { // Types and subtypes
        return areMimePartCompatible( $firstPieces[ 0 ], $secondPieces[ 0 ] ) &&
            areMimePartCompatible( $firstPieces[ 1 ], $secondPieces[ 1 ] );
    }
    return false;
}

function compareMimes( array $desired, array $received ) {
    // If it is accepting all, there is no need to check the received headers
    if ( \array_search( MIME_ANY, $desired ) !== false ) {
        return true;
    }

    foreach ( $desired as $d ) {
        foreach ( $received as $r ) {
            if ( areMimeCompatible( $d, $r ) ) {
                return true;
            }
        }
    }
    return false;
}


// ---

// const MIME_HTML_UTF8 = 'text/html;charset=UTF-8';
const MIME_JSON_UTF8 = 'application/json;charset=UTF-8';

const SHORT_MIMES = [
    'exe' => 'application/octet-stream',
    'gif' => 'image/gif',
    'gzip' => 'application/g-zip',
    'html' => 'text/html',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpg',
    'json' => 'application/json',
    'mp3' => 'audio/mpeg',
    'mp4' => 'video/mp4',
    'ogg' => 'video/ogg',
    'pdf' => 'application/pdf',
    'png' => 'image/png',
    'text' => 'text/plain',
    'txt' => 'text/plain',
    'xml' => 'application/xml',
    'zip' => 'application/zip',
];

/**
 * Returns the file MIME or null in case of error.
 *
 * @param string $path File path
 * @return string
 */
function getFileMime( $path ) {
    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    if ( $finfo === false ) {
        return null;
    }
    $mime = finfo_file( $finfo, $path );
    finfo_close( $finfo );
    return $mime;
}

?>