<?php
namespace phputil\router;

/**
 * Route to Regex result.
 */
class RouteToRegexResult {
    public string $regex;
    public array $params;
    public bool $isGroupRoute;
    public function __construct( string $regex, array $params = [], bool $isGroupRoute = false ) {
        $this->regex = $regex;
        $this->params = $params;
        $this->isGroupRoute = $isGroupRoute;
    }
}
