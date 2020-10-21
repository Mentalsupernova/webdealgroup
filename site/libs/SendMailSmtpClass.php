<?php
/**
* SendMailSmtpClass
* 
* Класс для отправки писем через SMTP с авторизацией
* Может работать через SSL протокол
* Тестировалось на почтовых серверах yandex.ru, mail.ru и gmail.com
* 
*/
class SendMailSmtpClass {

    /**
    * 
    * @var string $smtp_username - логин
    * @var string $smtp_password - пароль
    * @var string $smtp_host - хост
    * @var string $smtp_from - от кого
    * @var integer $smtp_port - порт
    * @var string $smtp_charset - кодировка
    *
    */   
    public $smtp_username;
    public $smtp_password;
    public $smtp_host;
    public $smtp_from;
    public $smtp_port;
    public $smtp_charset;
    private $log = "/../smtp_mail.log";
    private $debug = true/*false*/;

    
    public function __construct($smtp_username, $smtp_password, $smtp_host, $smtp_from, $smtp_port = 25, $smtp_charset = "utf-8") {
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;
        $this->smtp_host = $smtp_host;
        $this->smtp_from = $smtp_from;
        $this->smtp_port = $smtp_port;
        $this->smtp_charset = $smtp_charset;
    }
    
    /**
    * Отправка письма
    * 
    * @param string $mailTo - получатель письма
    * @param string $subject - тема письма
    * @param string $message - тело письма
    * @param string $headers - заголовки письма
    *
    * @return bool|string В случаи отправки вернет true, иначе текст ошибки    *
    */
    
   function fputs2($socket, $data) {
       if($this->debug) {
           file_put_contents(filter_input(INPUT_SERVER, "DOCUMENT_ROOT").$this->log, date("Y-m-d H:i:s")." => ".$data, FILE_APPEND | LOCK_EX);
       }     
       fputs($socket, $data);
   }
    
    function send($mailTo, $subject, $message, $headers) {
        $contentMail = "Date: " . gmdate("D, d M Y H:i:s") . " GMT\r\n";
        $contentMail .= 'Subject: ' .mb_encode_mimeheader($subject, "UTF-8", "B") . "\r\n";
        $contentMail .= $headers . "\r\n";
        $contentMail .= $message . "\r\n";
        
        try {
            if(!$socket = @fsockopen($this->smtp_host, $this->smtp_port, $errorNumber, $errorDescription, 30)){
                throw new Exception($errorNumber.".".$errorDescription);
            }
            if (!$this->_parseServer($socket, "220")){
                throw new Exception('Connection error');
            }
			
			$server_name = $_SERVER["SERVER_NAME"];
            $this->fputs2($socket, "HELO $server_name\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: HELO');
            }
            
            $this->fputs2($socket, "AUTH LOGIN\r\n");
            if (!$this->_parseServer($socket, "334")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
			
			
            
            $this->fputs2($socket, base64_encode($this->smtp_username) . "\r\n");
            if (!$this->_parseServer($socket, "334")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
            
            $this->fputs2($socket, base64_encode($this->smtp_password) . "\r\n");
            if (!$this->_parseServer($socket, "235")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
			
            $this->fputs2($socket, "MAIL FROM: <".$this->smtp_username.">\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: MAIL FROM');
            }
            
            $addr = explode(",", $mailTo);
            $addr_success = FALSE;
            if(is_array($addr)) {
                foreach ($addr as $ak => $av) {
                    $av = trim($av);
                    if(preg_match("/([^<@>]+@[^<@>]+\.[^<@>\.]+)/is",$av,$match)) {
                        $this->fputs2($socket, "RCPT TO: <" . $match[1] . ">\r\n");   
                        if ($this->_parseServer($socket, "250")) {
                            /*fclose($socket);
                            throw new Exception('Error of command sending: RCPT TO '.$av);*/
                            $addr_success = TRUE;
                        }                        
                    }                      
                }            
            }
            elseif (preg_match("/([^<@>]+@[^<@>]+\.[^<@>\.]+)/is",$mailTo,$match)) {
                    $mailTo = trim($mailTo);
                        $this->fputs2($socket, "RCPT TO: <" . $match[1] . ">\r\n");  
                        if ($this->_parseServer($socket, "250")) {
                            $addr_success = TRUE;
                        } 
                        else {
                fclose($socket);
                            throw new Exception('Error of command sending: RCPT TO '.$mailTo);                            
            }
            
            }
            else {
                fclose($socket);
                throw new Exception('Incorrect address '.$mailTo);
            }
            
            if(!$addr_success) {
                fclose($socket);
                throw new Exception('No address to send ');
            }
            
            $this->fputs2($socket, "DATA\r\n");     
            if (!$this->_parseServer($socket, "354")) {
                fclose($socket);
                throw new Exception('Error of command sending: DATA');
            }
            
            $this->fputs2($socket, $contentMail."\r\n.\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception("E-mail didn't sent");
            }
            
            $this->fputs2($socket, "QUIT\r\n");
            fclose($socket);
        } catch (Exception $e) {
            return  $e->getMessage();
        }
        return true;
    }
    
    private function _parseServer($socket, $response) {
        while (@substr($responseServer, 3, 1) != ' ') {
            if (!($responseServer = fgets($socket, 256))) {
                return false;
            }
        }
        if($this->debug) {
            file_put_contents(filter_input(INPUT_SERVER, "DOCUMENT_ROOT").$this->log, date("Y-m-d H:i:s")." <= ".$responseServer, FILE_APPEND | LOCK_EX);
        }           
        if (!(substr($responseServer, 0, 3) == $response)) {
            return false;
        }
        return true;
        
    }
}