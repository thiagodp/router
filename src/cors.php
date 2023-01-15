<?php
/**
 * This file is inspired by Troy Goode's CORS Router for ExpressJS,
 * available at https://github.com/expressjs/cors with a MIT License.
 */
namespace phputil\router;

require_once 'http.php';

/**
 * CORS options
 */
class CorsOptions {

    public $origin = '*';
    public $methods = 'GET,HEAD,PUT,PATCH,POST,DELETE';
    public $preflightContinue = false;
    public $optionsSuccessStatus = 204; // No Content
    public $credentials = false;
    public $allowedHeaders = [];
    public $exposedHeaders = [];
    public $maxAge = null;

    /**
     * Reads options from an array with the same keys.
     *
     * @param array $options Options
     * @param bool $validate Validate or not (the default value is true)
     * @return CorsOptions
     */
    public function fromArray( array $options, $validate = true ) {
        $attributes = \get_object_vars( $this );
        foreach ( $options as $key => $value ) {
            if ( isset( $attributes[ $key ] ) ) {
                $this->{ $key } = $value;
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
        validateOptions( $this );
    }

    //
    // Build methods
    //

    /**
     * Sets the origin.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withOrigin( $value ) { $this->origin = $value; return $this; }

    /**
     * Sets the methods.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withMethods( $value ) { $this->methods = $value; return $this; }

    /**
     * Sets the preflightContinue.
     *
     * @param bool $value
     * @return CorsOptions
     */
    public function withPreflightContinue( $value ) { $this->preflightContinue = $value; return $this; }

    /**
     * Sets the optionsSuccessStatus.
     *
     * @param int $value
     * @return CorsOptions
     */
    public function withOptionsSuccessStatus( $value ) { $this->optionsSuccessStatus = $value; return $this; }

    /**
     * Sets the credentials.
     *
     * @param bool $value
     * @return CorsOptions
     */
    public function withCredentials( $value ) { $this->credentials = $value; return $this; }

    /**
     * Sets the allowedHeaders.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withAllowedHeaders( $value ) { $this->allowedHeaders = $value; return $this; }

    /**
     * Sets the exposedHeaders.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withExposedHeaders( $value ) { $this->exposedHeaders = $value; return $this; }

    /**
     * Sets the maxAge.
     *
     * @param int $value
     * @return CorsOptions
     */
    public function withMaxAge( $value ) { $this->maxAge = $value; return $this; }

}

/**
 * CORS middleware.
 *
 * @param array|phputil\router\CorsOptions $options CORS options.
 * @return callable
 */
function cors( $options = [] ) {

    $opt = is_array( $options )
        ? ( new CorsOptions() )->fromArray( $options )
        : ( ( is_object( $options ) && ( $options instanceof CorsOptions ) )
            ? $options : new CorsOptions() );

    return function ( &$req, &$res, &$stop ) use ( &$opt ) {

        $headers = [];
        if ( $req->method() === METHOD_OPTIONS ) { // Preflight

            makeOrigin( $req, $opt, $headers );
            makeCredentials( $opt, $headers );
            makeMethods( $opt, $headers );
            makeAllowedHeaders( $opt, $headers );
            makeMaxAge( $opt, $headers );
            makeExposedHeaders( $opt, $headers );

            if ( $opt->preflightContinue ) { // Don't stop
                return;
            }

            $stop = true;
            $res->status( $opt->optionsSuccessStatus )->header( 'Content-Length', 0 )->end();
        } else {
            makeOrigin( $req, $opt, $headers );
            makeCredentials( $opt, $headers );
            makeExposedHeaders( $opt, $headers );
        }

    };
}

function makeOrigin( &$req, CorsOptions &$opt, array &$headers ) {
    $value = $opt->origin;
    if ( empty( $value ) ) {
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


const HEADER_VARY = 'Vary';
const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
const HEADER_ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
const HEADER_ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
const HEADER_ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';


function makeCredentials( CorsOptions &$opt, array &$headers ) {
    if ( $opt->credentials ) {
        $headers[ HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS ] = 'true';
    }
}


function makeMethods( CorsOptions &$opt, array &$headers ) {
    $headers[ HEADER_ACCESS_CONTROL_ALLOW_METHODS ] = \is_array( $opt->methods )
        ? \implode( ',', $opt->methods ) : $opt->methods;
}


function makeAllowedHeaders( CorsOptions &$opt, array &$headers ) {
    if ( ! is_array( $opt->allowedHeaders ) || empty( $opt->allowedHeaders ) ) {
        $headers[ HEADER_VARY ] = HEADER_ACCESS_CONTROL_REQUEST_HEADERS;
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_REQUEST_HEADERS ] = implode( ',', $opt->allowedHeaders );
}


function makeMaxAge( CorsOptions &$opt, array &$headers ) {
    if ( ! is_numeric( $opt->maxAge ) ) {
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_MAX_AGE ] = $opt->maxAge;
}


function makeExposedHeaders( CorsOptions &$opt, array &$headers ) {
    if ( ! is_array( $opt->exposedHeaders ) || empty( $opt->exposedHeaders ) ) {
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_EXPOSE_HEADERS ] = implode( ',', $opt->exposedHeaders );
}

//
// Validation
//

const MSG_INVALID_ORIGIN = 'The option "origin" must be a string.';
const MSG_INVALID_METHODS_TYPE = 'The option "methods" must be a string or an array.';
const MSG_INVALID_HTTP_METHOD = 'Invalid HTTP method.';
const MSG_INVALID_SUCCESS_STATUS = 'Invalid success status code.';

function validateOptions( CorsOptions $co ) {
    // Origin
    if ( ! is_string( $co->origin ) ) {
        throw new \RuntimeException( MSG_INVALID_ORIGIN );
    }
    // Methods
    $methods = [];
    if ( is_string( $co->methods ) ) {
        $methods = explode( ',', $co->methods );
    } else if ( is_array( $co->methods ) ) {
        $methods = $co->methods;
    } else {
        throw new \RuntimeException( MSG_INVALID_METHODS_TYPE );
    }
    // HTTP methods
    foreach ( $methods as $m ) {
        if ( ! isHttpMethodValid( trim( $m ) ) ) {
            throw new \RuntimeException( MSG_INVALID_HTTP_METHOD );
        }
    }
    // Status
    if ( ! is_numeric( $co->optionsSuccessStatus ) ||
        $co->optionsSuccessStatus < 200 ||
        $co->optionsSuccessStatus > 399
    ) {
        throw new \RuntimeException( MSG_INVALID_SUCCESS_STATUS );
    }
}

?>
