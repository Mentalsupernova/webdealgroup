<?php
////======================================================////
////																										  ////
////              	Библиотека php-хелперов	              ////
////																											////
////======================================================//*/
//// 			        		         ////
//// 	   Подключение классов	 ////
//// 			         		         ////
////===========================////





////======================================================//*/
//// 			         ////
//// 	   Функции	 ////
//// 			         ////
////===============////

  //---------//
  // german_ //
  //---------//
  if(!function_exists('german_')) {
    /**
     *  <h1>Список хелперов пакета R1</h1>
     *  <pre>
     *
     *    write2log                  | Сделать запись в лог приложения
     *
     *  </pre>
     * @return bool
     */
    function german_() {

      return true;

    }
  }
  
  //-----------//
  // write2log //
  //-----------//
  if(!function_exists('write2log')) {
    /**
     *  <h1>Описание</h1>
     *  <pre>
     *    Сделать запись в лог приложения devtools/psw.log
     *  </pre>
     *  <h1>Пример использования</h1>
     *  <pre>
     *    write2log("сообщение");
     *  </pre>
     *
     * @param  string $msg
     * @param  array $tags
     *
     * @return mixed
     */
    function write2log($msg, $tags = []) {

      // 1. Преобразовать $msg в строку
      switch (gettype($msg)) {
        case 'boolean':       $msg = '(boolean) '.$msg; break;
        case 'integer':       $msg = '(integer) '.$msg; break;
        case 'double':        $msg = '(double) '.$msg; break;
        case 'string':        $msg = '(string) '.$msg; break;
        case 'array':         $msg = '(array) '.json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); break;
        case 'object':        $msg = '(object) '.json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); break;
        case 'resource':      $msg = '(resource) '.'write2log не может преобразовать переменную типа resource в строку'; break;
        case 'NULL':          $msg = 'NULL'; break;
        case 'unknown type':  $msg = '(unknown type) '.'write2log не может преобразовать переменную типа unknown type в строку'; break;
        default:              $msg = 'write2log не может преобразовать переменную не опознанного типа в строку'; break;
      }

      // 2. Сделать запись в монолог
      \Monolog\Registry::debug()->warning($msg);

    }
  } 


  //-------------------------//
  // german_escapeJsonString //
  //-------------------------//
  if(!function_exists('german_escapeJsonString')) {
    /**
     *  <h1>Описание</h1>
     *  <pre>
     *    Для обработки json, отправляемого с сервера на клиент,
     *    чтобы у клиента не было ошибки Unexpected Token
     *    при парсинге JSON.parse
     *  </pre>
     *  <h1>Пример использования</h1>
     *  <pre>
     *    $result = escapeJsonString("json строка");
     *  </pre>
     *
     * @param  string $value
     *
     * @return mixed
     */
      function german_escapeJsonString($value)
      {
          # list from www.json.org: (\b backspace, \f formfeed)
          $escapers =     array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c");
          $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b");
          $result = str_replace($escapers, $replacements, $value);
          return $result;
      }

  }





