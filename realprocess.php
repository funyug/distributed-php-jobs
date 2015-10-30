<?php

  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPStreamConnection;
  $con=mysqli_connect('localhost','root','');
  mysqli_select_db($con,'test');
  $connection = new AMQPStreamConnection('192.168.0.106', 5672, 'test', 'test123');
  $channel = $connection->channel();

  $channel->queue_declare('response_queue', false, true, false, false);

  echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

  $callback = function($msg){
  echo " [x] Received  \n";
  $response=$msg->body;
  preg_match('/\w+/',$response,$match);
  $count=null;
  $returnvalue = preg_replace('/\\w+,/', '', $response, 1, $count);
  $response=$returnvalue;
  global $con;
  $isbn[0]=$match[0];

 if($response=="{}")
    {
      echo "Skipped";
    }


    else {
      $response=json_decode($response,true);
  $publishers=isset($response['ISBN:'.$isbn[0]]['publishers'])?$response['ISBN:'.$isbn[0]]['publishers']:"";
  $title=isset($response['ISBN:'.$isbn[0]]['title'])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['title']):"";
  $authors=isset($response['ISBN:'.$isbn[0]]['authors'])?$response['ISBN:'.$isbn[0]]['authors']:"";
  $isbn_13=isset($response['ISBN:'.$isbn[0]]['identifiers']['isbn_13'][0])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['identifiers']['isbn_13'][0]):"";
  $goodreads=isset($response['ISBN:'.$isbn[0]]['identifiers']['goodreads'][0])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['identifiers']['goodreads'][0]):"";
  $weight=isset($response['ISBN:'.$isbn[0]]['weight'])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['weight']):"";
  $subjects=isset($response['ISBN:'.$isbn[0]]['subjects'])?$response['ISBN:'.$isbn[0]]['subjects']:"";
  $pages=isset($response['ISBN:'.$isbn[0]]['number_of_pages'])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['number_of_pages']):"";
  $subject="";
  $author="";
  $publisher="";
  if($publishers!="")
  {
  foreach($publishers as $x) {
    $publisher=$publisher.$x['name'].",";
  }
  }
  if($authors!="")
  {
  foreach($authors as $x) {
    $author=$author.$x['name'].",";
  }
  }
  if($subjects!="")
  {
  foreach($subjects as $x)
  {
    $subject=$subject.$x['name'].",";
  }
  }
  $subject=rtrim(mysqli_real_escape_string($con,$subject),",");
  $author=rtrim(mysqli_real_escape_string($con,$author),",");
  $publisher=rtrim(mysqli_real_escape_string($con,$publisher),",");
  $publish_date=isset($response['ISBN:'.$isbn[0]]['publish_date'])?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['publish_date']):"";
  mysqli_query($con,"Insert ignore into isbns values('','$title','$author','$isbn[0]','$publisher','$isbn_13','$goodreads','$weight','$pages','$subject','$publish_date','','')") or die(mysqli_error($con));
}
  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
  };

  $channel->basic_qos(null, 1, null);
  $channel->basic_consume('response_queue', '', false, false, false, false, $callback);

  while(count($channel->callbacks)) {
    $channel->wait();
  }

  $channel->close();
  $connection->close();

?>
