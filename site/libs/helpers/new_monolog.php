<?php

use Monolog\Logger;
use Monolog\Registry;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

call_user_func(function(){

  // 1. Создать новый экземеляр monolog с каналом "debug"
  $log = new Logger('debug');

  // 2. Подготовить новый обработчик
  $handler = new StreamHandler('site/libs/helpers/psw.log');
  
  // 3. Организовать и добавить новый Line Formatter
  $lineformatter = new LineFormatter(null, null, true, true);
  $handler->setFormatter($lineformatter);  
  
  // 4. Добавить новый обработчик сообщений
  $log->pushHandler($handler);

  // 5. Добавить $log в реестр
  Registry::addLogger($log);

});


