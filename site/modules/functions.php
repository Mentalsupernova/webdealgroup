<?php 
    define('DELIVERY_TYPE_SHIPPING', 0);
    define('DELIVERY_TYPE_PICKUP', 1);

    define('MENU_TYPE_PIZZA', 10);
    define('MENU_TYPE_DRINK', 80);

    define('ORDER_CONFIRM_METHOD_CALL', 0);
    define('ORDER_CONFIRM_METHOD_SMS', 1);

    define('ORDER_PAY_TYPE_CACHE', 0);

    define('ORDER_STATUS_NEW', 0);
    define('ORDER_STATUS_ACCEPTED', 1);

    define('COMET_EVENT_NEW_ORDER', 'new_order');

	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
// ================================================================================================================================================================        

	function getRequest()
        {
            $params = array_merge($_GET, $_POST);
            reset($params);
            while(list($key,$value) = each($params)){
                if (gettype($params[$key]) != "array"){
                    if (get_magic_quotes_gpc()){
                        $value = stripslashes(trim($value));
                    }
                    $params[$key] = $value;
                }
            } 
            return $params; 
        }

// ================================================================================================================================================================        
	
	function now()
	{
		$now = array();
		list($now['year'], $now['month'], $now['months'], $now['day'], $now['hour'], $now['hours'], $now['minute'], $now['minutes'], $now['second']) = sscanf(date("Y m m d H H i i s"), "%d %d %s %d %d %s %d %s %d");
		return $now;
	}
    
	function time_format($time)
	{
		$now = array();
		list($now['year'], $now['month'], $now['months'], $now['day'], $now['hour'], $now['hours'], $now['minute'], $now['minutes'], $now['second']) = sscanf(date("Y m m d H H i i s",$time), "%d %d %s %d %d %s %d %s %d");
		return $now;
	}    

// ================================================================================================================================================================        


function comet_send()
    {
        $c = array('dev_id' => "0",
            'dev_key' => "hVowVjSDRWQllsROlTtEwCnOVvJK0vHFjza2NyrD3stXw2qvai4WAH9nkYMqfZTt");

        $link = mysqli_connect("app.comet-server.ru", $c['dev_id'], $c['dev_key'], "CometQL_v1");


        send($link, $res, "webtest");








    }




	function get_period() 
	{ 
	    global $ts;
	    list($usec, $sec) = explode(" ", microtime()); 
	    $time = ((float)$usec + (float)$sec);
	    $period = $time - $ts;
	    $ts = $time ;
	    return $period;
	} 

// ================================================================================================================================================================        

    function noSQL($string)
    {
    	return mysql_escape_string(trim($string)); // !!  не изменять (влияет на логин)
    }	

// ================================================================================================================================================================        





    function set_directories()
    {
		
    	$time_start = microtime_float();

		$id_filial = $_SESSION['filial']['id_filial'];

		if(!isset($_SESSION['sort'])) 
		{
			$sort = "rate";
		}
		else
		{
			if($_SESSION['sort']=='rate') $sort = "rate"; 
			if($_SESSION['sort']=='cost') $sort = "cost";
		}

 		// получаем всё меню по филиалу
        $products = f_get_data('Get_products', array($id_filial, $sort)); // получаем ингредиенты по пицце
		$ingr['pizza'] = f_get_data('Get_pizza_ingrids', array($id_filial)); // получаем ингредиенты по пицце
		$ingr['wok'] = f_get_data('Get_wok_ingrids', array($id_filial)); // получаем ингредиенты по вок
		$sauces = f_get_data('Get_wok_sauce', array($id_filial)); // получаем соусы
		$default_sauce = $sauces[0]['id_menu'];

		foreach($sauces as $k => $sauce)
		{
			$ingr['sauce'][$sauce['id_menu']] = $sauce;
		}



		$ps = array (

            '0' => array(
                'doughType' => 'standart',
                'size' => '26'
            ),
            '1' => array(
                'doughType' => 'standart',
                'size' => '36'
            ),
            '2' => array(
                'doughType' => 'standart',
                'size' => '45'
            ),
            '3' => array(
                'doughType' => 'thin',
                'size' => '26'
            ),
            '4' => array(
                'doughType' => 'thin',
                'size' => '36'
            ),
            '5' => array(
                'doughType' => 'thin',
                'size' => '45'
            )
        ); // массив размеров пицц

		$prods = array();


		// создаем массив дополнительных ингредиентов для каждого размера пиццы
		$ingrids = array();
		foreach($ingr['pizza'] as $k=>$v) // для каждого дополнительного ингридиента
		{
			$prices = explode("^", $ingr['pizza'][$k]['price']);
			//$weights = explode("^", $ingr['pizza'][$k]['weight']);
			$items = array();
			foreach($prices as $i=>$j) // составляем массив для каждой цены 
			{
				if($prices[$i]!='')
				{
					$items[] = array(
								"id_menu" => $v['id_menu'],
								"name_menu" => $v['name_menu'],
								//"kcal" => $v['kcal'],
								//"protein" => $v['protein'],
								//"fat" => $v['fat'],
								//"carbo" => $v['carbo'],
								"price" => $prices[$i],
								//"weight" => $weights[$i], // нет в таблице данных по весам доп. ингредиентов
								"amount" =>  0
							);
					
				}
			}
			$ingrids['pizza'][] = $items;
		}


		// создаем массив дополнительных ингредиентов для вок
		foreach($ingr['wok'] as $k=>$v) // для каждого дополнительного ингридиента
		{
			$prices = explode("^", $ingr['wok'][$k]['price']);
			//$weights = explode("^", $ingr['wok'][$k]['weight']);
			$items = array();
			foreach($prices as $i=>$j) // составляем массив для каждой цены 
			{
				if($prices[$i]!='')
				{
					$items[] = array(
								"id_menu" => $v['id_menu'],
								"name_menu" => $v['name_menu'],
								//"kcal" => $v['kcal'],
								//"protein" => $v['protein'],
								//"fat" => $v['fat'],
								//"carbo" => $v['carbo'],
								"price" => $prices[$i],
								//"weight" => $weights[$i], // нет в таблице данных по весам доп. ингредиентов
								"amount" =>  0
							);
					
				}
			}
			$ingrids['wok'][] = $items;
		}	
			


		$positionSizes = array();

		foreach($ps as $kk=>$vv)
		{
			foreach($ingrids['pizza'] as $i=>$j)
			{
				$positionSizes['pizza'][$kk][$ingrids['pizza'][$i][$kk]['id_menu']] = $ingrids['pizza'][$i][$kk];  // массив ингредиентов с ключами id_menu
			}
		}					

		foreach($ps as $kk=>$vv)
		{
			foreach($ingrids['wok'] as $i=>$j)
			{
				$positionSizes['wok'][$kk][$ingrids['wok'][$i][$kk]['id_menu']] = $ingrids['wok'][$i][$kk];  // массив ингредиентов с ключами id_menu
			}

			break;
		}	


		$M = array();

		$menu = $_SESSION['menu'];


		foreach($products as $k=>$p)
		{
            $p['id_filial'] = $id_filial;
			
			//if($p['id_menu'] == 29) continue;

			$prices = explode("^", $p['price']);
			$weights = explode("^", $p['weight']);
			$thin_count = 0;
			$items = array(); // обнуляем массив вариаций пицц


			
			foreach($prices as $i=>$j) // составляем массив блюд одного названия с соответствующими параметрами : цена и вес
			{
				if($prices[$i]!='') // i выступает в качестве ключа ко всем массивам он соединяет цену и раземр пиццы
				{
					if($ps[$i]['doughType']=='thin') $thin_count++; // считаем количество тонких
					$items[] = array(
						"price" => $prices[$i], 
						"weight" => $weights[$i], 
						"type" => $ps[$i]['doughType'],
						"size" => $ps[$i]['size']
					);
				}
			}
			
			//echo "<pre >"; print_r($p); echo "</pre>"; //die();
			//echo "<pre >"; print_r($items); echo "</pre>"; //die();


			$p['items'] = $items;

			$cat = array();
			$remark = array();
			
			if ($p['id_cat'] != 0)
			{
				$cat = $menu[$p['id_cat']];

				$i = $p['id_cat'];

				while ($i != 0)
				{
					$j = array();

					$n = $menu[$i];
					$j['id_cat'] = $menu[$i]['id_cat'];
					$j['alias'] = $menu[$i]['alias'];
					//$j['name'] = $menu[$i]['name_menu'];
					$remark[] = $j;
					$i = $n['parent'];
				}
				$cat['breadcrumbs'] = array_reverse($remark);

				$p['cat'] = $cat;	

			}
			else
			{
				$p['cat'] = null;
			}
			
			// добавляем текущее состояние по позиции меню
			

			$p['current_item'] = 0;


			$p['type'] = 'standart';
			$p['amount'] = 1;
			$p['ingrid_amount'] = 0;
			$p['cost_ingr'] = 0;
			$p['total_sum'] = $p['items'][0]['price']*$p['amount'];
			//$p['total_weight'] = $p['items'][0]['weight'];


			$p['size'] = $p['items'][0]['size'];
            $p['alias'] = $p['alias']; // test



			$p['thin_count'] = $thin_count; 
			$p['add_ingr'] = array();
			
			$p['sauce'] = 556;
			$p['show_desc'] = 0;

			if(isset($type_ingr) && $type_ingr == 'wok')
			{
				$p['sauce'] = $default_sauce;
				$p['sauces'] = $ingr['sauce'];
			}

/**/


			//echo "<pre >".$p['id_menu']." ".$p['name_menu']."</pre>"; //die();
			/*$p['sostav'] = f_get_data('Get_ingrids', array($p['id_menu']));
            
            $sostav_alias = array();
            
            foreach ($p['sostav'] as $s) {

                $sostav_alias[] = $s['pc_alias'];

            }
            $p['sostav_alias'] = $sostav_alias;*/
            


			//echo "<pre >"; print_r($p['sostav']); echo "</pre>"; //die();

            if($p['description'] == '')
            {
                $sostav = f_get_data('Get_ingrids', array($p['id_menu']));
                $description = '';

                foreach ($sostav as $s) {

                    if ($description == '')
                    {
                        $description = $s['pc_name'];
                    }
                    else
                    {
                        $description .= ', '.$s['pc_name'];
                    }


                }

                $p['description'] = $description;
            }




			$prods_tpl[$p['id_menu']] = $p;

			unset($p['price']);
			unset($p['weight']);
			unset($p['seo_text']);
			//unset($p['description']);


			//unset($p['isHot']);
			//unset($p['isNew']); 
			unset($p['kcal']);
			unset($p['protein']);
			unset($p['fat']);
			unset($p['carbo']);
			unset($p['rate']);
			unset($p['isGift']);

			//unset($p['sauces']);

			$prods[$p['id_menu']] = $p;  // расстановка индексов для продукции в соответствии с их id

			$M[$p['cat']['breadcrumbs'][0]['id_cat']][$p['id_menu']] = $p; // сортировка по категориям
		}

		//$time_end = microtime_float();
		//echo $time_end - $time_start; echo "<br>";


		/*$time_end = microtime_float();
		$time = $time_end - $time_start;

		echo "Did nothing in $time seconds\n";*/

		$result = array();
		$result['prods'] = $prods;
		$result['prods_tpl'] = $prods_tpl;
		$result['M'] = $M;
		$result['positionSizes'] = $positionSizes;
		$result['sauces'] = $ingr['sauce'];




		return $result;    	
    }


    function f_call_sp($sp_arr) {

		$db = $GLOBALS['db'];
        $sql = "";
        foreach ($sp_arr as $sp => $value) {
            $quotedParams = array();
            $storeProcedureName = $value['proc'];
            foreach ($value['p'] as $param) {
                array_push($quotedParams, $param === null ? 'NULL' : '"' . $param . '"');
            }
            $sql .= 'CALL ' . $storeProcedureName . '(' . implode(',', $quotedParams) . ');';
        }

        $db->multi_query($sql);
        $results = array ();
        do
        {
            if ($result = $db->store_result())
            {
                $rows = array ();
                while ($row = $result->fetch_assoc())
                {
                    array_push($rows, $row);
                }
                $result->close();
                array_push($results, $rows);
            }
        } while ($db->more_results() && $db->next_result());

        return ($results);
    }

    function f_get_data($proc, $p) {
        $out[] = array(
            'proc' => $proc,
            'p' => $p
                );
        $res = f_call_sp($out);
        $r = $res[0];

        return $r;
    }
    
    function f_child_nodes($id_cat)
    {
		
    	$menu = $_SESSION['menu'];

		// получить все id узлов, которые подчинены данному узлу 
		$condition = array();
		foreach($menu as $m)
		{
			if($m['parent']==0) continue;
			$i = $m['id_cat'];
			do
			{
				if($i==$id_cat) 
				{
					$condition[] = $m['id_cat']; 
					break;
				}
				$i = $menu[$i]['parent'];
			}while($i!=0);
		}
		$condition[] = $id_cat;
		//$nodes = implode(",", $condition);
		
		return $condition;
	}  

    function JavaScriptEncode($string) {

        $string = "document.write('".$string."');";

        $js_encode = '';

        for ($x=0; $x < strlen($string); $x++) {
            $js_encode .= '%' . bin2hex($string[$x]);
        }

        return '<script type="text/javascript">eval(decodeURIComponent(\''.$js_encode.'\'))</script>';

    }   
    
function replaceUTMphone() {
    
    $utms = array();
    
    $utms_sql = rows("SHOW COLUMNS FROM `call_tracking` LIKE 'utm_%'");
    
    foreach ($utms_sql as $utm) {
        array_push($utms, $utm['Field']);
    }
    $utm_values = array();
    
    $search = "";
    
    foreach ($utms as $u) {
        if(isset($_SESSION[$u]) && !empty($_SESSION[$u])) {
            $utm_values[':'.$u] = '\''.strval($_SESSION[$u]).'\'';
            $search .= " AND $u=:$u";             
        }         
    }
    
    if(!empty($utm_values)) {  
        $phone = row(strtr("SELECT `phoneCode`,`phone` FROM `call_tracking` WHERE 1 $search AND `active`=1 ORDER BY `order`", $utm_values));       
        if(empty($phone)) {
            unset($utms[array_search('utm_campaign', $utms)]);
            $utm_values =array();
            $search = "";        
            foreach ($utms as $u) {
                if(isset($_SESSION[$u]) && !empty($_SESSION[$u])) {
                    $utm_values[':'.$u] = '\''.strval($_SESSION[$u]).'\'';
                    $search .= " AND $u=:$u";             
                }         
            }
            $phone = row(strtr("SELECT `phoneCode`,`phone` FROM `call_tracking` WHERE 1 $search AND `active`=1 ORDER BY `order`", $utm_values));  
        }
        return $phone;
    }

    
    return array();
           
}   

function remove_emoji($text){
    return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
}

function remove_emoji_array($array){
    
    if(is_array($array)) {
        foreach ($array as $k => $v) {
            if(is_string($v)) {
                $array[$k] = remove_emoji($v);
            }
        }
    }
    
    return $array;
}

    function isProduction()
    {

        $env = getenv('ENV') ?: 'production';
        return $env == 'production';

        //return false;
    }

    function getOrderConfirmMethod($id = null)
    {
	    $methods = array(
	        ORDER_CONFIRM_METHOD_CALL => 'Звонок оператора',
	        ORDER_CONFIRM_METHOD_SMS => 'SMS',
        );

	    if (null === $id) {
	        return $methods;
        }

	    if (isset($methods[$id])) {
            return $methods[$id];
        }

	    return null;
    }

    function getOrderPayType($id = null)
    {
	    $types = array(
	        ORDER_PAY_TYPE_CACHE => 'Наличные',
        );

	    if (null === $id) {
	        return $types;
        }

	    if (isset($types[$id])) {
            return $types[$id];
        }

	    return null;
    }

    function getOrderStatusLabel($id = null)
    {
	    $types = array(
	        ORDER_STATUS_NEW => 'Новый',
	        ORDER_STATUS_ACCEPTED => 'Принят',
        );

	    if (null === $id) {
	        return $types;
        }

	    if (isset($types[$id])) {
            return $types[$id];
        }

	    return null;
    }

    function getFilialById($id)
    {
        $filials = $_SESSION['filials'];

        return !empty($filials[$id]) ? $filials[$id] : null;
    }

    function sendSms($phoneNumber, $message)
    {
        $phoneNumber = str_replace(['-', ' '], '',$phoneNumber);

        // отправка смс
        require_once 'site/libs/sms.ru.php';

        try {
            $smsru = new SMSRU('ABBA6B02-7D43-208B-76E3-51C6E7C0ED70'); // Ваш уникальный программный ключ, который можно получить на главной странице

            $data = new stdClass();
            $data->to = $phoneNumber;
            $data->text = $message; // Текст сообщения

            if (!isProduction()) {
                $data->test = 1; // Позволяет выполнить запрос в тестовом режиме без реальной отправки сообщения
            }

            // $data->from = ''; // Если у вас уже одобрен буквенный отправитель, его можно указать здесь, в противном случае будет использоваться ваш отправитель по умолчанию
            // $data->time = time() + 7*60*60; // Отложить отправку на 7 часов
            // $data->translit = 1; // Перевести все русские символы в латиницу (позволяет сэкономить на длине СМС)
            // $data->partner_id = '1'; // Можно указать ваш ID партнера, если вы интегрируете код в чужую систему

            return $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную
        } catch (Throwable $e) {
        }

        return false;
    }

    function getJsOrderData($order)
    {
        $orderDate = $order['order_date_init']
            ? DateTime::createFromFormat('Y-m-d H:i:s', $order['order_date_init'])
            : false;
        $deliveryTime = $order['order_time_delivery']
            ? DateTime::createFromFormat('Y-m-d H:i:s', $order['order_time_delivery'])
            : false;
	    $data = [
            'id' => (int)$order['order_id'],
            'positions' => [],
            'is_shipping' => (int)$order['order_delivery_type'] === DELIVERY_TYPE_SHIPPING,
            'address' => [
                'street' => $order['order_street_name'],
                'building' => $order['order_home_user'],
                'corp' => $order['order_corp_user'],
                'flat' => $order['order_apart_user'],
                'pod' => $order['order_pod_user'],
                'floor' => $order['order_floor_user'],
                'code' => $order['order_code_dom_user'],
            ],
            'date' => $orderDate ? $orderDate->format('d M Y H:i') : null,
            'delivery_time' => $deliveryTime ? $deliveryTime->format('d M Y H:i') : null,
            'client_name' => $order['order_name'],
            'client_phone' => $order['order_phone'],
            'client_comment' => $order['order_comment_user'],
            'confirm_method' => getOrderConfirmMethod((int)$order['confirm_method']),
            'pay_type' => getOrderPayType((int)$order['order_pay_type']),
            'is_new_order' => (ORDER_STATUS_NEW === (int)$order['order_status']),
            'status_label' => getOrderStatusLabel((int)$order['order_status']),
            'price' => $order['order_full_sum'] !== null ? (float)$order['order_full_sum'] : null,
            'discount' => $order['order_discount'] !== null ? (int)$order['order_discount'] : null,
            'nickelback_from' => $order['order_change'] !== null ? (float)$order['order_change'] : null,
        ];

	    $positions = rows(sprintf('SELECT * FROM psw_order_positions position INNER JOIN menu ON menu.id_menu = position.op_menu_id WHERE op_order_id = %d', (int)$order['order_id']));

        foreach ($positions as $position) {
            $positionData = [
                'id' => (int)$position['op_id'],
                'menu_id' => (int)$position['id_menu'],
                'size_index' => (int)$position['op_menu_size_index'],
                'is_drink' => (int)$position['id_type'] === MENU_TYPE_DRINK,
                'quantity' => (int)$position['op_amount'],
            ];

            $data['positions'][]  = $positionData;
	    }

	    return $data;
    }

    function getFilialCometChannel($filialId)
    {
	    return sprintf('filial_%d_orders', ($filialId . (isProduction() ? '' : '_dev')));
    }

    function pushCometMessage($channel, $event, $message)
    {
	    try {
	        $link = mysqli_connect(COMET_SERVER_HOST, COMET_DEV_ID, COMET_DEV_KEY, "CometQL_v1");

            if (!$link) {
                return false;
            }

            $query = "INSERT INTO pipes_messages (name, event, message) VALUES ('%s', '%s', '%s')";

            if (!is_string($message)) {
                $message = json_encode($message);
            }

            return mysqli_query($link, sprintf($query, $channel, $event, $message));
        } catch (Throwable $e) {
        }

        return false;
    }

    function pushOrderToComet($order)
    {
	    return pushCometMessage(getFilialCometChannel((int)$order['order_filial_id']), COMET_EVENT_NEW_ORDER, getJsOrderData($order));
    }




