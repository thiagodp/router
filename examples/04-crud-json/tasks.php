<?php
//
// Fake model for the example
//

$tasks = [ // Some data for the example
    ['id'=>1, 'what'=>'Buy beer'],
    ['id'=>2, 'what'=>'Wash the dishes']
];

function generateId( $arrayCopy ) { // Just for the example
    $last = end( $arrayCopy );
    return isset( $last, $last['id'] ) ? 1 + $last['id'] : 1;
}

function createTask( array $t ) {
    global $tasks;
    $id = generateId( $tasks );
    $t['id'] = $id;
    $tasks []= $t;
    return $id;
}

function taskKey( $id ) {
    global $tasks;
    return array_search( $id, array_column( $tasks, 'id' ) );
}