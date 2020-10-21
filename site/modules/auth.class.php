<?php
class auth extends aModule{
    function execute($arr)
    {
    	//echo "<pre >"; print_r($arr); echo "</pre>"; //die();
		//echo "<pre style='display:none;'>"; print_r($_SERVER); echo "</pre>"; //die();
		//echo "<pre >"; print_r($_SESSION['user']); echo "</pre>"; //die();

    	$user = array();

		if (empty($_SESSION['user']))
		{

			if(isset($_POST['token'])&&strlen($_POST['token'])>0)
			{
			    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
			    
			    $u = json_decode($s);

				//echo "<pre >"; print_r($u); echo "</pre>"; //die();

				$usersList=rows("SELECT * FROM mypizza_users WHERE email LIKE '".$u->email."' ");
				
				if(count($usersList)==1)
				{
				    $user = $usersList[0];
				    $user['role']="user";
				}
				else
				{
				    $user['role']="guest";
				    $user['email']=$user->email;
				}            
			}
			else if (!empty($arr['user_login']) && !empty($arr['password']))//|| $arr['verified_email'] == 1)
			{   

				//echo "ok";

				if($arr['verified_email'] == 1)
				{
					$where = " email LIKE '".noSQL($arr['confirmed_email'])."' ";
				}
				else
				{
					//echo "ok";

					if(!strstr($arr['user_login'], '_'))
					{
						$phone = noSQL($arr['user_login']);
					}
					
					if (strlen($arr['user_login'])>0)
					{
						$email = noSQL($arr['user_login']);
						$login = noSQL($arr['user_login']);
					}
					else
					{
						$login = str_replace(array("(",")"," ","-"), "", $arr['user_login']);
					}

					//echo $login;
					
					$where = "";
					if(strlen($arr['password'])>0 && strlen($phone)>0) { $where .= "(pass LIKE '".md5($arr['password'])."' AND	phone LIKE '".$phone."')";}
					if(strlen($arr['password'])>0 && strlen($email)>0) { if (strlen($where)>0) {$where .= " OR ";} $where .= "(pass LIKE '".md5($arr['password'])."' AND	email LIKE '".$email."')";}
					if(strlen($arr['password'])>0 && strlen($email)>0) { if (strlen($where)>0) {$where .= " OR ";} $where .= "(pass LIKE '".md5($arr['password'])."' AND	login LIKE '".$email."')";}
					if(strlen($arr['password'])>0 && strlen($login)>0) { if (strlen($where)>0) {$where .= " OR ";} $where .= "(pass LIKE '".md5($arr['password'])."' AND	login LIKE '".$login."')";}

					//echo $where;
				}

				$List=rows("SELECT * FROM mypizza_users WHERE (".$where.")");

				//echo "<pre >"."SELECT * FROM mypizza_users WHERE (".$where.")"; print_r($List); echo "</pre>"; //die();

				//echo "<pre >"."SELECT * FROM mypizza_users WHERE (".$where.")"; print_r($List); echo "</pre>"; //die();

				if(count($List)==1)
				{
				    $user = $List[0];
				    $user['role'] = "user";
				}            
			}
			
		}
		else
		{
			$user = $_SESSION['user'];
		}


		if(isset($user['Id']) && strlen($user['Id']) >0)
		{
			$r = $this->get_data('Get_discount', array( 
				$user['Id']
			));
			$user['discount'] = $r[0];

			$user['addresses'] = $this->get_data('User_addresses', array( 
				$user['Id']
			));

			$addresses = array();
			foreach($user['addresses'] as $k=>$v)
			{
				$addresses[$v['id']] = $user['addresses'][$k];
			}
			$user['addresses_json'] = json_encode($addresses);


			//echo "<pre >"; print_r($user); echo "</pre>"; //die();


			$r = rows("SELECT * FROM _cart_sessions WHERE id_user = ".$user['Id']);


			//echo "<pre >"; print_r($_SESSION['cart']); echo "</pre>"; //die();
			//echo "<pre >"; print_r($r); echo "</pre>"; //die();
			/*
			if(isset($_SESSION['cart']) && count($_SESSION['cart'][items])>0) // если в корзине до логина что-то было
			{
				
				//echo "ok";

				if(count($r)>0) // при присутствии в таблице update
				{
					q("UPDATE _cart_sessions SET session ='".json_encode($_SESSION['cart'])."' WHERE id_user=".$user['Id']);
				}
				else  // при отсутствии в таблице insert
				{
					q("INSERT INTO _cart_sessions (id_user, session) VALUE (".$user['Id'].",'".json_encode($_SESSION['cart'])."')");
				}					
			}
			else  // если ничего не было
			{
				if(count($r)>0) // и в таблице что-то было, то в сессию пишем из таблицы
				{
					$_SESSION['cart'] = json_decode($r[0]['session'], true);
				}				
			}
			*/

			
		}



		return $user;
    }
}

?>