<?php
	class router extends aModule{
	    function execute($arr)
	    {


	    	//echo "<pre >"; print_r($arr); echo "</pre>"; //die();

	    	if (isset($arr['unset']) && strlen($arr['unset'])>0)  {$this->clear_sess($arr);}

			if (empty($arr['q']))
			{
				$arr['q']='/index';
			} // если пусто, считаем что это index
			
            if(preg_match("/\/order\/(?:pay|thankyou)\/(.*)\//is", $arr['q'], $matches)) {
                $arr['q'] = str_replace($matches[1], rawurlencode($matches[1]), $arr['q']);
            }
			
			$node = array(); // текущая страница
			$menu = array(); // меню каталога
			$user = array(); // текущий поьзователь 


			$alias = split("[\/]+", $arr['q']); // разбираем строку
			foreach($alias as $a)	{if($a!='') {$post[] = $a; $aliases[] = $a;}}
			$page = "";
			
			foreach($post as $k=>$v) {if ($k==0) {$page .= $v;}else{$page .= "/".$v;}}

			$dir = $post;                    // $dir[0] - алиас модуля
			$post = array_reverse($post);    // $post[0] - алиас запрашиваемой страницы

			$post['path'] = $arr['q'];
			
			if(isset($arr['debug'])) $_SESSION['smarty']->assign('debug', '1');


			//***********************************************************************
            
            if(!isset($_COOKIE['order_fingerprint']) || empty($_COOKIE['order_fingerprint'])) {
                
                setcookie("order_fingerprint", md5(time().rand(10000, 99999)), time()+3600*24*365);
                
            }

            $res['menu_cat_Get_list'] = $this->get_data('menu_cat_Get_list', array());  // поучаем меню каталога
			$res['filials_Get_list'] = $this->get_data('filials_Get_list', array());	// поучаем список всех филиалов
			$res['menu_Get_list'] = $this->get_data('menu_Get_list', array());			// поучаем меню
			$res['pages_Get_list'] = $this->get_data('pages_Get_list', array());		// поучаем список страниц

			$filial = $this->set_filial($arr, $res); // устанавливаем филиал, передаем массив филиалов


			$page = $this->set_pages($post, $res); // формируем меню страниц, получаем станицу
			$_SESSION['smarty']->assign('page', $page);	// передаем параметры страницы в шаблон


			$node = $this->set_menu_cat($post, $res); // формируем меню каталога, получаем  узел
			//echo "<pre >"; print_r($node); echo "</pre>"; //die();

			$prod = $this->set_products($post, $res, $_SESSION['menu']);

			//echo "<pre >"; print_r($prod); echo "</pre>"; die();
			
			if(isset($arr['sort'])) $this->set_sort($arr['sort']);

			// получаем пользователя
			$a = new auth();
			$user = $a->execute($arr);
			if(!empty($user))
			{
				$_SESSION['user'] = $user;	
				$_SESSION['smarty']->assign('user', $user);	
			}

            if(filter_has_var(INPUT_SERVER, "HTTP_X_REQUESTED_WITH") && filter_input(INPUT_SERVER, "HTTP_X_REQUESTED_WITH")==="XMLHttpRequest" && isset($_POST['user_login']) && isset($_POST['password'])) {
                    
                header("Content-Type: application/json");
                
                if(!empty($user))
                {
                    
                    echo json_encode(array('result'=>TRUE,'message'=>'Авторизация успешна'));
                    
                }
                else if (!empty ($_POST) && (empty($_POST['user_login']) || empty($_POST['password']))) {

                    echo json_encode(array('result'=>FALSE,'message'=>'Пустой логин или пароль!'));
                    
                }
                else {
                    
                    echo json_encode(array('result'=>FALSE,'message'=>'Неверный логин/пароль!'));
                    
                }
                exit();
            }

		    if(!isset($_SESSION['cart']))
		    {
		        $_SESSION['cart']['total_cart_amount'] = 0;
		        $_SESSION['cart']['total_cart_sum'] = 0;  
		        $_SESSION['cart']['chopsticks_max'] = 0; 
		        $_SESSION['cart']['chopsticks'] = 0;  
		        $_SESSION['cart']['items'] = array();  
		    }
		    $_SESSION['smarty']->assign('cart', $_SESSION['cart']);

		    // определение viewmode для отображения элементов управления товарами
	        $_SESSION['smarty']->assign('viewmode', 'main');
	        $_SESSION['smarty']->assign('viewmode_params', json_encode(array('show_controls'=>1)));

			//*********************************************************************** switch

			if(isset($arr['mod_name']) && strlen($arr['mod_name']) > 0) // если идет непосредственное обращение к модулю
			{

				print ($_SESSION['smarty']->fetch('str:'.$arr['mod_name'])); // передаем управление модулю

			}

			$gifts = $this->get_data('Get_gifts', array($_SESSION['filial']['id_filial']));
         	$_SESSION['smarty']->assign('gifts', isset($gifts) ? $gifts : array());
            $chopsticks = $this->get_data('Get_chopsticks', array());
        	$_SESSION['smarty']->assign('chopsticks', isset($chopsticks) ? $chopsticks : array());


			if (isset($arr['unset'])  && $arr['unset'] == 'user'){ unset($_SESSION['user']);} // перенести в модуль работы с авторизацией пользователя




			// получаем всю продукцию, отправляем json меню продукции в шаблон, 
			$this->set_json_vars($filial);

			if (!empty($node)) // если обнаружена категория
			{
				$_SESSION['smarty']->assign('post', $node);

                //echo "<pre>"; print_r($node); echo "</pre>"; //die();

				if(isset($post[1]) && $post[1] == "sostav") {
                    
                    $meta = row("SELECT * FROM `_sostav` WHERE `url` LIKE '".  noSQL(filter_input(INPUT_SERVER, "REQUEST_URI"))."%' LIMIT 1");

                    if(empty($meta) && !empty($page)) {
                        $meta = array(
                            "title" => $page["title"],
                            "keywords" => $page["meta_k"],
                            "description" => $page["meta_d"],
                            "h1" => $page["h1"]
                        );
                    }
                    elseif(empty($meta) && empty($page)) {
                        $meta = array(
                            "title" => "",
                            "keywords" => "",
                            "description" => "",
                            "h1" => ""
                        );
                    }
                    $_SESSION['smarty']->assign('page', $meta);

                    //echo "<pre>"; print_r($post); echo "</pre>"; die();


                    //$sostav_alias = last(explode("/",trim(filter_input(INPUT_SERVER, "REQUEST_URI"),"/")));
                    //$sostav_name = row("SELECT `pc_name` FROM `psw_product_compositions` WHERE `pc_alias` = '".noSQL($sostav_alias)."'");

                    $sostav_name = row("SELECT `pc_name` FROM `psw_product_compositions` WHERE `pc_alias` = '".noSQL($post[0])."'"); 

                    if(!empty($sostav_name)) {
                        $_SESSION['smarty']->assign('sostav_name', $sostav_name['pc_name']);
                    }                                
                    print ($_SESSION['smarty']->fetch('category_sostav.tpl')); // вызываем шаблон состав
                }
                else {


				print ($_SESSION['smarty']->fetch('category.tpl')); // вызываем шаблон категории
                }
				exit (0);
			}
            elseif ($post[0] === "new") {
                $_SESSION['smarty']->assign('cat_alias', "new");
                $_SESSION['smarty']->assign('viewmode_params', json_encode(array('show_controls'=>0)));
                print ($_SESSION['smarty']->fetch('category.tpl')); // вызываем шаблон категории
                exit (0);
                
            }
            elseif ($post[0] === "post") {
                $_SESSION['smarty']->assign('cat_alias', "post");
                $_SESSION['smarty']->assign('viewmode_params', json_encode(array('show_controls'=>0)));
                print ($_SESSION['smarty']->fetch('category.tpl')); // вызываем шаблон категории
                exit (0);
                
            }
			else if (!empty($prod)) // если обнаружен продукт
			{
				$_SESSION['smarty']->assign('post', $prod);
        		$_SESSION['smarty']->assign('viewmode', 'item');
				print ($_SESSION['smarty']->fetch('item.tpl')); // вызываем шаблон карточки продукта
				exit (0);				
			}
			else
			{
				// иначе переключаем на соответствующие модули по имени первой директории после домена
				
				$_SESSION['smarty']->assign('post', $post);

				if(isset($dir[0]) && strlen($dir[0])>0)
				{
					switch ($dir[0]) {

						case 'filial': 			print ($_SESSION['smarty']->fetch('str:filial'));  			exit (0); break;

                        case 'room': 			print ($_SESSION['smarty']->fetch('str:room'));  			exit (0); break;
						case 'page': 			print ($_SESSION['smarty']->fetch('str:page'));  			exit (0); break; 
						case 'admin': 			print ($_SESSION['smarty']->fetch('str:admin'));  			exit (0); break;
						case 'vacancies': 		print ($_SESSION['smarty']->fetch('str:vacancies'));  		exit (0); break;
						case 'promotions': 		print ($_SESSION['smarty']->fetch('promotions.tpl'));  		exit (0); break;
						case 'classes': 		print ($_SESSION['smarty']->fetch('classes.tpl'));  		exit (0); break;
						case 'order': 			print ($_SESSION['smarty']->fetch('str:order'));  			exit (0); break;
						case 'registration': 	print ($_SESSION['smarty']->fetch('str:registration'));  	exit (0); break;
						case 'reviews': 		print ($_SESSION['smarty']->fetch('str:reviews'));  		exit (0); break;	
						case 'cart': 			print ($_SESSION['smarty']->fetch('str:cart'));  			exit (0); break;	
						
						case 'feedback': 		print ($_SESSION['smarty']->fetch('feedback.tpl'));  		break;	// вызов мастер шаблона
								
						case 'index': 			

												$index['params'] = $arr;
												$_SESSION['smarty']->assign('post', $index);

												
												$GLOBALS['index'] = 1;
												$_SESSION['smarty']->assign('index', $GLOBALS['index']);


												if(isset($GLOBALS['mobile']) && $GLOBALS['mobile'] == 1)
												{


                                                    $cats = rows('SELECT * FROM menu_cat WHERE active=1 ORDER BY order_cat');

                                                    $items = array();
                                                    foreach ($cats as $v) {  // перебираем меню с целю проставить ключи
                                                        $items[$v['id_cat']] = $v;
                                                    }


                                                    foreach($items as $k=>$v)
                                                    {

                                                        if($v['parent']!=0) continue;

                                                        // получить все id узлов, которые подчинены данному узлу
                                                        $condition = array();
                                                        foreach($items as $c)
                                                        {
                                                            if($c['parent']==0) continue;
                                                            $i = $c['id_cat'];

                                                            do
                                                            {
                                                                if($i==$v['id_cat'])
                                                                {
                                                                    $condition[] = $c['id_cat'];
                                                                    break;
                                                                }
                                                                $i = $items[$i]['parent'];

                                                            } while($i!=0);
                                                        }
                                                        $condition[] = $v['id_cat']; // добавляем сам узел для которого проводился поиск
                                                        $items[$k]['nodes'] = implode(",", $condition);

                                                    }



                                                    foreach($items as $k=>$v)
                                                    {
                                                        if($v['parent']==0)
                                                        {
                                                            $top_categories[$k] = $v;
                                                            //echo "<pre>"; print_r( $v); echo "</pre>"; //die();
                                                            $top_categories[$k]['top_product'] = rows('SELECT * FROM menu m INNER JOIN price p ON m.id_menu=p.id_menu WHERE m.id_cat IN('.$v['nodes'].') AND m.active=1 ORDER BY p.rate LIMIT 1 ');
                                                        }
                                                    }


                                                    //echo "<pre>"; print_r($top_categories); echo "</pre>"; //die();

                                                    $_SESSION['smarty']->assign('cats', $top_categories);


                                                    print($_SESSION['smarty']->fetch('mobile.tpl'));
												}
												else
												{
													print($_SESSION['smarty']->fetch('index.tpl'));
												}


							break;														

						default:
                            http_response_code(404);
                            print ($_SESSION['smarty']->fetch('404.tpl'));
                            
							break;
				    }
	
				}			
				else if (!empty($page)) // если обнаружена страница (таблица _pages)
				{
				    $_SESSION['smarty']->assign('post', $page);
				    print ($_SESSION['smarty']->fetch('main.tpl')); // вызываем мастер шаблон простых страниц
				    exit (0);				
				}
				else if(isset($post[0]) && strlen($post[0])>0) // если в последней секции в URL все таки что-то есть, но это не было распознано
				{
					http_response_code(404);
					print ($_SESSION['smarty']->fetch('404.tpl'));
                    
				}
			}

			unset($_SESSION['smarty']);
			session_write_close();
				

	    }
	}
   
?>