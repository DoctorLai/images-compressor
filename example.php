<?php
  require('class-imagescompressor.php');
  $app_key = 'YOUR_APP_KEY';
  $app_secret = 'YOUR_APP_SECRET';

  $obj = new ImagesCompressor($app_key, $app_secret);
  print_r($obj->check());
  $response = $obj->optimize("/var/www/imagerecycle/test.jpg");
  echo ($response->errCode);
  echo ($response->result->optimize); 
