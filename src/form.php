<?php
namespace phputil\router;

// TO-DO Deal with multipart/form-data, eg.:
// curl -X PUT --form "name=bob" --form "age=20" http://localhost:80/x.php
//
// See more cURL parameters at:
// https://gist.github.com/joyrexus/524c7e811e4abf9afe56
//
// TO-DO Deal with files, see:
// https://github.com/notihnio/php-multipart-form-data-parser/blob/master/src/MultipartFormDataParser.php
// https://github.com/alireaza/php-form-data/blob/main/src/FormData.php
// https://gist.github.com/devmycloud/df28012101fbc55d8de1737762b70348
//
// W3C spec at https://www.w3.org/TR/html401/interact/forms.html#h-17.13.4.2
//


class ExtractionResult {
    public $data = [];
    public $files = [];
}


function extractFormDataAndFiles( $httpMethod, $contentType ) {
    $result = new ExtractionResult();

    // HTTP POST: $_POST and $_FILES works for both "application/x-www-form-urlencoded" and "multipart/form-data"
    if ( $httpMethod === 'POST' ) {
        $hasPost = \isset( $_POST );
        $hasFiles = \isset( $_FILES );
        if ( $hasPost ) {
            $result->data = $_POST; // copy
        }
        if ( $hasFiles ) {
            $result->files = $_FILES; // copy
        }
        if ( $hasPost && $hasFiles ) {
            return $result;
        }
    }

    $content = @\file_get_contents( 'php://input' );
    if ( $content === false ) {
        return null;
    }

    $contentType = \mb_strtolower( $contentType );
    if ( $contentType === 'application/x-www-form-urlencoded' ) { // Form data only
        handleFormUrlEncoded( $content, $result );
    } else if ( $contentType === 'multipart/form-data' ) { // Form data, files or both
        return handleMultipartFormData( $content, $result );
    } else if ( $contentType === 'application/octet-stream' ) { // Single file
        return handleOctetStream( $content, $result );
    }
    return $result;
}



// function arrayParam( &$params, $key, $preventInjection ) {
//     if ( ! \isset( $params[ $key ] ) ) {
//         return null;
//     }
//     return $preventInjection
//         ? \htmlspecialchars( $params[ $key ] )
//         : $params[ $key ];
// }


function handleFormUrlEncoded( &$content, ExtractionResult &$result ) {
    $params = [];
    if ( \mb_parse_str( $content, $params ) !== false ) {
        $result->data = $params;
    }
}


/**
 * Extracts the file sent as "application/octet-stream".
 *
 * @param string $content Request content (reference to save memory).
 */
function handleOctetStream( &$content, ExtractionResult &$result ) {
    $matches = [];
    $ok = \preg_match( '/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $content, $matches );
    if ( $ok ) {
        list( , $fileName, &$fileContent ) = $matches;
        $result->files[ $fileName ] = $fileContent;
    }
    return $ok;
}


function handleMultipartFormData( &$content, ExtractionResult &$result ) {

}


// ============================================================================


/**
 * Mimics PHP's upload file array structure
 */
class FileData {
    public $name = '';
    public $type = '';
    public $tmp_name = '';
    public $error = 0;
    public $size = 0;
}

class TempSaveOptions {
    public $tempDir = '';
    public $tempPrefix = '';
}

/** Prefix for temporary files */
const DEFAULT_TEMP_PREFIX = 'tmp';

/**
 * Save to a temp file and returns the content or `null` in case of error.
 *
 * @param string $content File content, passed by reference to save memory.
 * @param TempSaveOptions $options
 */
function saveToTempFile( &$content, TempSaveOptions $options = new TempSaveOptions() ) {
    $dir = \empty( $options->tempDir ) ? sys_get_temp_dir() : $options->tempDir;
    $prefix = \empty( $options->tempPrefix ) ? DEFAULT_TEMP_PREFIX : $options->tempPrefix;
    $fileName = @\tempnam( $dir, $prefix );
    if ( $fileName === false ) {
        return null;
    }
    $fp = @\fopen( $fileName, 'w' );
    if ( $fp === false ) {
        return null;
    }
    $writeResult = @\fwrite( $fp, $content );
    $closeResult = @\fclose( $fp );
    if ( $writeResult === false || $closeResult === false ) {
        return null;
    }
    return $fileName;
}


// ============================================================================


const STATUS_NONE = 0;
const STATUS_STARTED = 1;
const STATUS_COMPLETED = 2;

const DISPOSITION_TYPE_NONE = 0;
const DISPOSITION_TYPE_FORM_DATA = 10;
const DISPOSITION_TYPE_FILE = 20;

const CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';

class Context {
    public $status = STATUS_NONE;
    public $lastLineWasHeader = false;

    public $dispositionType = DISPOSITION_TYPE_NONE;
    public $contentType = null;
    public $boundary = null;
    public $key = null;
    public $value = null;
}


// TO-DO: Consider caching strategies to not parse the body again and again
// on each key request. Maybe put it on a global variable, like $GLOBALS[ '_PUT' ] ?
function extractMultipart( $rawBody ) {
    $current = new Context();
    $params = [];
    $lines = \explode( "\r\n", $rawBody );
    foreach ( $lines as $line ) {

        if ( empty( $line ) && $current->lastLineWasHeader ) {
            $current->lastLineWasHeader = false;
            continue;
        }

        if ( ! handleAsHeader( $current, $line, $params ) ) {
            handleAsContent( $current, $line, $params );
        }

        // MIMES from "multipart/form-data":
        // a) "text/plain" -> usual form data
        // b) "application/octet-stream" -> single file
        // c) "multipart/form-data" -> multiple files
    }
}

function handleAsHeader( Context &$current, $line ) {

    if ( \mb_stripos( 'Content-Disposition', $line ) === 0 ) {
        $current->lastLineWasHeader = true;
        $matches = [];
        \preg_match( '/^Content\-Disposition\: ([a-z -\/]+); (?:name|filename)\="([^"]+)"/iu', $line, $matches );
        list( $type, $name ) = $matches;
        if ( $type === 'form-data' ) {

        } else if ( $type === 'file' ) {
        }

        return true;

    } else if ( \mb_stripos( 'Content-Type', $line ) === 0 ) {
        $current->lastLineWasHeader = true;
        $matches = [];
        \preg_match( '/^Content\-Type\: ([a-z -\/]+)(?:; boundary=)?([a-zA-Z0-9]+)?$/i', $line, $matches );
        list( , $contentType, $boundary ) = $matches;
        $current->contentType = $contentType;
        if ( isset( $boundary ) ) {
            $current->boundary = $boundary;
        }
        return true;
    }
    return false;
}


function handleAsContent( Context &$current, $line, array &$params ) {
    $current->lastLineWasHeader = false;
}


?>