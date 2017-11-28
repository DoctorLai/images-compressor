<?php
  $obj = new ImagesCompressor($app_key, $app_secret);
  print_r($obj->check());
  $response = $obj->optimize("/var/www/imagerecycle/test.jpg");
  echo ($response->errCode);
  echo ($response->result->optimize); 
