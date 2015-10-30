<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.0.106', 5672, 'test', 'test123');
$channel = $connection->channel();

$channel->queue_declare('image_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo " [x] Received \n";
  $data=$msg->body;
  preg_match('/\w+/',$data,$match);
  $filename=$match[0];
  $count=null;
  $filedata=str_replace($filename.",","", $data);
  $handler=fopen('images/'.$filename.".jpg",'w+');
  fwrite($handler,$filedata);

  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('image_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
