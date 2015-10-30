<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.0.106', 5672, 'test', 'test123');
$channel = $connection->channel();
$channel->queue_declare('task_queue', false, true, false, false);

$channel->queue_declare('response_queue', false, true, false, false);
$channel->queue_declare('image_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo " [x] Received \n";

  global $channel;
  $response=trim(file_get_contents('https://openlibrary.org/api/books?bibkeys=ISBN:'.$msg->body.'&callback=mycallback&jscmd=data'),'mycallback(');
  $response=rtrim($response,');');

  $msg2 = new AMQPMessage($msg->body.",".$response,
                          array('delivery_mode' => 2) # make message persistent
                        );
  echo " [x] Sent ", "\n";

  $channel->basic_publish($msg2, '', 'response_queue');
  $response=json_decode($response,true);
  $cover=isset($response['ISBN:'.$msg->body]['cover']['large'])?$response['ISBN:'.$msg->body]['cover']['large']:"";
  if($cover!="")
  {
    $data=file_get_contents($cover);
    $data=$msg->body.",".$data;
    $msg3 = new AMQPMessage($data,
                            array('delivery_mode' => 2) # make message persistent
                          );
    $channel->basic_publish($msg3,"",'image_queue');
  }


  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
