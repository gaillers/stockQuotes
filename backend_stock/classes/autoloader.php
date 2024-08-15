<?php

require_once(__DIR__ . DS . 'App.php');

require HD . '/vendor/autoload.php';

function app_autoloader($class_name)
{
  if (is_null($class_name)) {
    return false;
  }
  
  $class_filename = $class_name . '.php';
  $filename = HD . DS . 'classes' . DS . $class_filename;
  $filename2 = HD . DS . 'controllers' . DS . $class_filename;
  $filename3 = HD . DS . 'models' . DS . $class_filename;

  if (file_exists($filename) && (is_file($filename))) {
    require_once($filename);
  } elseif (file_exists($filename2) && (is_file($filename2))) {
    require_once($filename2);
  } elseif (file_exists($filename3) && (is_file($filename3))) {
    require_once($filename3);
  } else {
    $err_text = 'Class ' . $class_name . ' (file ' . $class_filename . ' ) not exist';
    App::json(['reason' => $err_text], 500);
    die($err_text);
  }
}

spl_autoload_register('app_autoloader');