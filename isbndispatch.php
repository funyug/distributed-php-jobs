<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.0.106', 5672, 'test', 'test123');
$channel = $connection->channel();
$file=fopen('isbns.csv','r');
$channel->queue_declare('task_queue', false, true, false, false);
while($data=fgetcsv($file))
{
$msg = new AMQPMessage($data[0],
                        array('delivery_mode' => 2) # make message persistent
                      );

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data[0], "\n";
}
$channel->close();
$connection->close();

?>
