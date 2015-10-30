<?php

function getData($isbn) {
  $isbn[0]='0752858548';
  $con=mysqli_connect('localhost','root','');
  mysqli_select_db($con,'test');
  $response=trim(file_get_contents('https://openlibrary.org/api/books?bibkeys=ISBN:'.$isbn[0].'&callback=mycallback&jscmd=data'),'mycallback(');
  $response=rtrim($response,');');
  $response=json_decode($response,true);
  $publishers=$response['ISBN:'.$isbn[0]]['publishers']?$response['ISBN:'.$isbn[0]]['publishers']:"";
  $title=$response['ISBN:'.$isbn[0]]['title']?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['title']):"";
  $authors=$response['ISBN:'.$isbn[0]]['authors']?$response['ISBN:'.$isbn[0]]['authors']:"";
  $isbn_13=$response['ISBN:'.$isbn[0]]['identifiers']['isbn_13'][0]?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['identifiers']['isbn_13'][0]):"";
  $goodreads=$response['ISBN:'.$isbn[0]]['identifiers']['goodreads'][0]?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['identifiers']['goodreads'][0]):"";
  $weight=$response['ISBN:'.$isbn[0]]['weight']?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['weight']):"";
  $subjects=$response['ISBN:'.$isbn[0]]['subjects']?$response['ISBN:'.$isbn[0]]['subjects']:"";
  $pages=$response['ISBN:'.$isbn[0]]['number_of_pages']?mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['number_of_pages']):"";
  $subject="";
  $author="";
  $publisher="";
  foreach($publishers as $x) {
    $publisher=$publisher.$x['name'].",";
  }
  foreach($authors as $x) {
    $author=$author.$x['name'].",";
  }
  foreach($subjects as $x)
  {
    $subject=$subject.$x['name'].",";
  }
  $subject=rtrim(mysqli_real_escape_string($con,$subject),",");
  $author=rtrim(mysqli_real_escape_string($con,$author),",");
  $publisher=rtrim(mysqli_real_escape_string($con,$publisher),",");
  $publish_date=mysqli_real_escape_string($con,$response['ISBN:'.$isbn[0]]['publish_date']);
  echo "Insert into isbns values('','$title','$author','$isbn[0]','$publisher','$isbn_13','$goodreads','$weight',$pages,'$subject','$publish_date','','')";
  mysqli_query($con,"Insert into isbns values('','$title','$author','$isbn[0]','$publisher','$isbn_13','$goodreads','$weight',$pages,'$subject','$publish_date','','')") or die(mysqli_error($con));

}


$file=fopen('isbns.csv','r');
//while($isbn=fgetcsv($file))
getData($isbn);

 ?>
