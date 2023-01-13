<?php
/**
 * This file is inspired by Troy Goode's CORS Router for ExpressJS,
 * available at https://github.com/expressjs/cors with a MIT License.
 */
namespace phputil\router;

// ===> THIS FILE IS UNFINISHED <===

require_once 'http.php';

function cors( array $options ) {
    $opt = ( new CorsOptions() )->fromArray( $options );
    return function ( &$req, &$res, &$stop ) use ( &$opt ) {

        $headers = [];
        if ( $req->method === METHOD_OPTIONS ) { // Preflight

            makeOrigin( $req, $opt, $headers );
            makeCredentials( $opt, $headers );
            makeMethods( $opt, $headers );

            if ( $opt->preflightContinue ) {
                return;
            }

            $res->status( $opt->optionsSuccessStatus )->header( 'Content-Length', 0 )->end();
        } else {

        }

    };
}

function makeOrigin( &$req, CorsOptions &$opt, array &$headers ) {
    $value = $opt->origin;
    if ( \empty( $value ) ) {
        $value = '*';
    }
    if ( \is_string( $value ) ) {
        $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] = $value;
    } else if ( \is_array( $value ) ) {
        $origin = $req->header( 'Origin' );
        if ( ! isset( $origin ) ) {
            $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] = false; // Deny
            return;
        }
        if ( isOriginAllowed( $origin, $opt->origin ) ) {
            $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] = $origin; // Reflect origin
        }
    }
    // Add Vary header
    if ( $value != '*' ) {
        $headers[ HEADER_VARY ] = 'Origin';
    }
}


function isOriginAllowed( $requestOrigin, $originToCheck ) {
    if ( $requestOrigin === $originToCheck ) {
        return true;
    }
    if ( is_array( $originToCheck ) ) {
        foreach ( $originToCheck as $origin ) {
            if ( isOriginAllowed( $requestOrigin, $origin ) ) {
                return true;
            }
        }
    }
    return false;
}


const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
const HEADER_VARY = 'Vary';


function makeCredentials( CorsOptions &$opt, array &$headers ) {

}


function makeMethods( CorsOptions &$opt, array &$headers ) {

}


function makeAllowedHeaders( CorsOptions &$opt, array &$headers ) {

}


function makeMaxAge( CorsOptions &$opt, array &$headers ) {

}


function makeExposedHeaders( CorsOptions &$opt, array &$headers ) {

}

//
// Validation messages
//

const MSG_INVALID_ORIGIN = 'The option "origin" must be a string.';
const MSG_INVALID_METHODS_TYPE = 'The option "methods" must be a string or an array.';
const MSG_INVALID_HTTP_METHOD = 'Invalid HTTP method.';
const MSG_INVALID_SUCCESS_STATUS = 'Invalid success status code.';

//
// Options
//

class CorsOptions {

    public $origin = '*';
    public $methods = 'GET,HEAD,PUT,PATCH,POST,DELETE';
    public $preflightContinue = false;
    public $optionsSuccessStatus = 204; // No Content

    public function fromArray( array $options, $validate = true ) {
        $attributes = \get_object_vars( $this );
        foreach ( $options as $key => $value ) {
            if ( isset( $attributes[ $key ] ) ) {
                $this[ $key ] = $value;
            }
        }
        if ( $validate ) {
            $this->validate();
        }
        return $this;
    }

    /**
     * Validates the options and throws an exception in case of a problem.
     *
     * @throws \RuntimeException
     */
    public function validate() {
        // Origin
        if ( ! is_string( $this->origin ) ) {
            throw new \RuntimeException( MSG_INVALID_ORIGIN );
        }
        // Methods
        $methods = [];
        if ( is_string( $this->methods ) ) {
            $methods = explode( ',', $this->methods );
        } else if ( is_array( $this->methods ) ) {
            $methods = $this->methods;
        } else {
            throw new \RuntimeException( MSG_INVALID_METHODS_TYPE );
        }
        // Check HTTP methods
        foreach ( $methods as $m ) {
            if ( ! isHttpMethodValid( trim( $m ) ) ) {
                throw new \RuntimeException( MSG_INVALID_HTTP_METHOD );
            }
        }
        // Status
        if ( ! is_numeric( $this->optionsSuccessStatus ) ||
            $this->optionsSuccessStatus < 200 ||
            $this->optionsSuccessStatus > 399
        ) {
            throw new \RuntimeException( MSG_INVALID_SUCCESS_STATUS );
        }
    }
}

?>
