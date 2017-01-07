<?php
require __DIR__ . '/init.php';

//create the main loop
$loop = React\EventLoop\Factory::create();

//create a mysql connection for executing queries
$connection = new React\MySQL\Connection($loop, array(
    'dbname' => 'test',
    'user'   => 'test',
    'passwd' => 'test',
));

//connecting to mysql server, not required.

$connection->connect(function () {});

$connection->query('select * from book', function ($command, $conn) use ($loop) {
    if ($command->hasError()) { //test whether the query was executed successfully
        //error
        $error = $command->getError();// get the error object, instance of Exception.
    } else {
        $results = $command->resultRows; //get the results
        $fields  = $command->resultFields; // get table fields
    }
    $loop->stop(); //stop the main loop.
});

$loop->run();
