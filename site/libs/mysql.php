<?php


// MYSQL ============================================================================================
// DB SETTINGS (begin)
/**/
include "mysql_config.php";

if (file_exists(__DIR__ . "/comet_config.php")) {
    include __DIR__ . "/comet_config.php";
}

if (!defined('COMET_SERVER_HOST')) {
    define('COMET_SERVER_HOST', '127.0.0.1');
}
if (!defined('COMET_SERVER_PORT')) {
    define('COMET_SERVER_PORT', null);
}
if (!defined('COMET_DEV_ID')) {
    define('COMET_DEV_ID', 0);
}
if (!defined('COMET_DEV_KEY')) {
    define('COMET_DEV_KEY', '');
}


define('PREFIX', ''); //Prefix
define('PCONNECT', 0);
define('DEBUGMODE', 1);
// DB SETTINGS (end)

  
$GLOBALS['dbid'] = false;

function dbconnect() {
	$F = PCONNECT ? 'mysqli_pconnect' : 'mysqli_connect';
	if (!$GLOBALS['dbid']) $GLOBALS['dbid'] = $F(DBHOST, DBUSER, DBPASS);
	if (!$GLOBALS['dbid'] OR !mysqli_select_db( $GLOBALS['dbid'],DBNAME)) {
		if (DEBUGMODE) dberror(mysqli_error(), mysqli_errno());
		exit;
	}

		q("/* !40101 SET NAMES 'utf8' */");
		q("SET character_set_client='utf8'");
		q("SET character_set_results='utf8'");
		q("SET collation_connection='utf8_general_ci'");

}

function dbnum($show=false) {
	static $qnum=0;
	if (false !== $show) return $qnum;
	$qnum++;
}

function q($query) {


	if (!$GLOBALS['dbid']) dbconnect();
	if (!$result = mysqli_query( $GLOBALS['dbid'],$query))
	{
        dberror(mysqli_error(), mysqli_errno());
        $result = false;
    }
	dbnum();
	return $result;
}

function numrows($q) {
	return mysqli_num_rows($q);
}

function fetch($q) {
	return mysqli_fetch_assoc($q);
}

function row($query,$mode='assoc') {
	$row = array();
	if ($q = q($query)) {
		$F = 'mysql_fetch_'.$mode;
		$row = $F($q);
		mysqli_free_result($q);
	}
	return $row;
}

function rows($query, $setkey=false, $group=false, $subkeys=false) {

	$rows = array();
	if ($q = q($query)) {
		if (false !== $setkey) {
			if (false !== $group) {
				if (false !== $subkeys)
					while ($row = mysqli_fetch_assoc($q)) $rows[$row[$setkey]][$row[$subkeys]] = $row;
				else
					while ($row = mysqli_fetch_assoc($q)) $rows[$row[$setkey]][] = $row;
			} else
				while ($row = mysqli_fetch_assoc($q)) $rows[$row[$setkey]] = $row;
		} else
			while ($row = mysqli_fetch_assoc($q))
				$rows[] = $row;
		mysqli_free_result($q);
	}
	return $rows;
}

function select($table,$what='*',$where='',$limit='') {
    if (!empty($where)) {
		if (is_array($where)) {
			$z = array();
			foreach ($where as $k=>$v)
				$z[] = mysqli_escape_string(stripslashes($k))."='".mysqli_escape_string(stripslashes($v))."'";
			$where = 'WHERE '.implode(' AND ',$z);
		} else {
			//$where = 'WHERE '.$input['where'];
		}
	}
	$q = 'SELECT '.$what.' FROM `'.$table.'`'.($where ? ' '.$where : '');
 //echo "<pre>"; print_r($q); echo "</pre>";
    return row($q);
}

function selects($table,$what='*',$where='',$limit='') {
	$q = 'SELECT '.$what.' FROM `'.$table.'`'.($where ? ' WHERE '.$where : '');
	return rows($q);
}

function sel($input,$one=false) {
	$q = array('SELECT');
	$q[] = !empty($input['what']) ? $input['what'] : '*';
	if (is_array($input['from'])) {
		$from = array();
		foreach ($input['from'] as $v)
			$from[] = $v.' as '.$v[0];
		$q[] = 'FROM '.implode(', ', $from);
	} else {
		$q[] = 'FROM '.$input['from'];
	}
	if (!empty($input['where'])) {
		if (is_array($input['where'])) {
			$z = array();
			foreach ($input['where'] as $k=>$v)
				$z[] = mysqli_escape_string(stripslashes($k))."='".mysqli_escape_string(stripslashes($v))."'";
			$q[] = 'WHERE '.implode(' AND ',$z);
		} else {
			$q[] = 'WHERE '.$input['where'];
		}
	}
	if (!empty($input['order'])) $q[] = 'ORDER BY '.$input['order'];
	if (!empty($input['limit'])) $q[] = 'LIMIT '.$input['limit'];
	$F = $one ? 'row' : 'rows';
	if (is_array($input['from'])) die(implode(' ',$q));
	return $F(implode(' ',$q));
}

function insert($table, $values) {
	foreach ($values as $k=>$v)
		$values[$k] = mysqli_escape_string(stripslashes(trim($values[$k])));
    q("INSERT INTO `".mysqli_escape_string(stripslashes($table))."` (`".implode('`,`',array_keys($values))."`) VALUES ('".implode("','",$values)."')");
    return insert_id();
    //return "INSERT INTO `".mysql_escape_string(stripslashes($table))."` (`".implode('`,`',array_keys($values))."`) VALUES ('".implode("','",$values)."')";
}

function update($table, $where, $values, $do = "=") {
	$q = "UPDATE `".mysqli_escape_string(stripslashes($table))."` SET ";
	$z = array();
	foreach ($values as $k=>$v)
		$z[] = mysqli_escape_string(stripslashes($k))."='".mysqli_escape_string(stripslashes(trim($v)))."'";
	$q .= implode(', ',$z);
    if($where && $where > 0) {
    	$q .= ' WHERE ';
    	$z = array();
    	foreach ($where as $k=>$v)
    		$z[] = mysqli_escape_string(stripslashes($k)).$do."'".mysqli_escape_string(stripslashes($v))."'";
    	$q .= implode(' AND ',$z);
    }
	return q($q);
}

function sql_delete($table, $where) {
	$q = "DELETE FROM `".mysqli_escape_string($table)."` WHERE ";
	$z = array();
	foreach ($where as $k=>$v)
		$z[] = mysqli_escape_string(stripslashes($k))."='".mysqli_escape_string(stripslashes($v))."'";
	$q .= implode(' AND ',$z);
	return q($q) ? true : false;
}

function insert_id() {
	return mysqli_insert_id($GLOBALS['dbid']);
}

function freeresult($q) {
	if ($q) mysqli_free_result($q);
}

function get_result_fields($result) {
	$fields = array();
	while ($field = mysqli_fetch_field($result))
		$fields[] = $field;
	return $fields;
}

function fields($table) {
	$names = array();
	$fields = mysqli_list_fields(DBNAME, $table, $GLOBALS['dbid']);
	$columns = mysqli_num_fields($fields);
	for ($i = 0; $i < $columns; $i++)
	    $names[] = mysqli_field_name($fields, $i);
	mysqli_free_result($fields);
	return $names;
}

function dbclose() {
	if ($GLOBALS['dbid']) mysqli_close($GLOBALS['dbid']);
}

function dberror($error, $error_num, $query = '') {
	$query = preg_replace('#([0-9a-f]){32}#', '********************************', $query);
	//include 'templates/messages/db_error.html';
	echo "<pre>"; print_r($error); echo "</pre>"; //die();
	echo "<pre>"; print_r($error_num); echo "</pre>"; //die();
	echo "<pre>"; print_r($query); echo "</pre>"; //die();
	exit;
}

function esc($str) {
	return mysqli_escape_string($str);
}

function i($str) {
	return intval($str);
}
//ФУНКЦИИ ===================================================================================================

// Управление кэшированием
function cache_drive($filename) {
	$lastModified = filemtime($filename);
	header('Last-Modified: '.gmdate('r', $lastModified).' GMT');
	$request = getallheaders();
	if (isset($request['If-Modified-Since'])) {
		$modifiedSince = explode(';', $request['If-Modified-Since']);	// fix for Netscape
		$modifiedSince = strtotime($modifiedSince[0]);
	} else {
		$modifiedSince = 0;
	}
	if ($lastModified <= $modifiedSince) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	}
}

// Возвращает текст в UTF-8
function utf($str) {
	return iconv('windows-1251', 'UTF-8', $str);
}

function win1251($str) {
	return iconv('UTF-8', 'windows-1251', $str);
}
/*
function json_encode($data,$single=false) {

	if ($single) return json($data);

	static $json = false;
	if (!$json) {
		require_once('JSON.php'); //if php<5.2 need JSON class
		$json = new Services_JSON();//instantiate new json object
	}
	return $json->encode($data);

}
*/
// Перевод массива в JSON
function json($out) {
	$params = array();
	$text = "{ ";
	foreach ($out as $k=>$v)
		$params[] = "{$k}: \"".str_replace('"','\"',$v)."\"";
	$text .= implode(", ",$params)." }";
	return $text;
}

function json_objects($data) {
	$rows = array();
	foreach ($data as $k=>$v) {
		$values = array();
		foreach ($data[$k] as $key=>$value) {
			$values[] = $key.':'. (
				is_bool($value)
					? $value
						? 'true' : 'false'
					: (is_numeric($value) OR substr($value,0,4) == 'new ')
						? $value : "'".$value."'"
			);
		}
		$rows[] = '{ '. implode(', ', $values) .' }';
	}
	$rows = implode(','."\n\t\t\t", $rows)."\n";
	return $rows;
}

// Функция перевода массива в JSON для grid-окон
function json_table($data,$total=0) {

$num = $total ? $total : sizeof($data);
$keys = array_keys($data[0]);
$unique = $keys[0];

$fields = array();
foreach ($keys as $k)
	$fields[] = "{name: '{$k}'}";
$fields = implode(', ', $fields);

$rows = json_objects($data);

return <<<JSON
{
	'metaData': {
		totalProperty: 'results',
		root: 'rows',
		id: '{$unique}',
		fields: [ {$fields} ]
	},
	'results': {$num},
	'rows': [ {$rows} ]
}
JSON;

}

function out() {
	ob_end_flush();
	exit;
}

class Tree {

	var $params = array();
	var $cats = array();
	var $names = array();
	var $tree = array();
	var $path = array();
	var $page = array();
	var $parent = array();
	var $found = true;
	var $root = '/';
	var $main = 1;
	var $home = false;

	function __construct() {
		$this->Tree();
	}

	function Tree() {

		foreach (rows("SELECT id,name,title,parent_id,status FROM tree ORDER BY sort ASC") as $row) {
			$this->cats[$row['id']] = $row;
			@$this->tree[$row['parent_id']][$row['name']] = $row['id'];
		}

		$this->path[] = $this->main = $parent_id = reset($this->tree[0]);
		foreach (explode('/', reset(explode('?', $_SERVER['REQUEST_URI']))) as $t) {

			$t = trim(urldecode($t));
			if ('' === $t) continue;

			if (substr_compare($t,'?',1,1)>=0)
            {
                $t = str_replace("?", "", $t);
                parse_str($t, $this->params);

                continue;
            }

			if (isset($this->tree[$parent_id][$t]) AND $this->cats[$this->tree[$parent_id][$t]]['status'] != 4) {
				$this->path[] = $parent_id = $this->tree[$parent_id][$t];
			} else {
				$this->found = false;
				return;
			}

		}

		$this->page = array_pop($this->path);
		$this->parent = array_pop($this->path);

		if ($this->parent) {

			$this->page = row("SELECT * FROM tree WHERE id='".i($this->page)."'");
			$this->parent = $this->cats[$this->page['parent_id']];

		} else {

			$this->page = row("SELECT * FROM tree WHERE id='".i($this->main)."'");
			$this->parent = array('id' => 0);
			$this->home = true;

		}

		if (!$this->page OR in_array($this->page['status'],array(3,4)) OR !$this->parent)
			$this->found = false;

	}

	function getPath($id) {
		if (isset($this->cats[$id]) AND $this->cats[$id]['parent_id'] > 0)
			$cat = $this->cats[$id];
		else return $this->root;
		$out = array();
		while ($cat['parent_id'] > 0) {
			$out[] = $cat['name'];
			$cat = $this->cats[$cat['parent_id']];
		}
		$out = array_reverse($out);
		return $this->root.implode('/',$out).'/';
	}

	function &getNode($id=0) {
		return $this->cats[$id];
	}

	function &getChildrenID($id) {
		return $this->tree[$id];
	}

	function getChildren($id=0) {

		if (!$id) $id = $this->main;

		$out = array();
		if (!empty($this->tree[$id]))
		foreach ($this->tree[$id] as $v) {
			$in = $this->cats[$v];
			if ($in['status'] != 1) continue;
			$in['path'] = $this->getPath($in['id']);
			$out[] = $in;
		}

		return $out;

	}

}


?>