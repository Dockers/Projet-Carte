<?php
session_start();
require 'API/facebook.php';
$DB = new PDO('mysql:host=localhost;dbname=tuto','root','');
$DB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$facebook = new Facebook(array{//création de l'objet Facebook
	'appId' => '',
	'secret' => '',
	'cookie' => true
});

$session = $facebook->getSession();
if(empty($session)){//Si pas de session trouvé
	header('Location:'.$facebook->getLoginUrl(array{
		'locale' => 'fr_FR'
		}));
}
else{
	try{//récupérer informations utilisateur
		$uid = $facebook->getUser();
		$me = $facebook->api('/me');//retour JSOn qui récup données utilisateur
	}catch (FacebookApiException $e){
		print_r($e);
	}

if(isset($me)){//exécution d'une requete fql
	$fql = "select uid,name,pic_big from user where uid=$uid";
	$param = array{
		'method' => 'fql.query',
		'query' => $fql,
		'callback' => ''
	};
	$fb = $facebook->api($param);
	$fb = $fb[0];
}

$user = $DB->query('SELECT id,login,password FROM users WHERE gacebook_id='.$uid.'LIMIT 1;');
$user=$user->fetchAll();
if(empty($user)){
	$login = $fb['name'];
	$password = sha1(uniqid());
	$DB->exec("INSERT INTO
				users (login, password, facebook_id)
				VALUES ('".mysql_escape_string($login)."','$password',$uid)");
	$id = $DB->lastInsertId();
}else{
	$user = $user[0];
	$login = $user['login'];
	$password = $user['password'];
	$id = $user['id'];
}
$_SESSION['user']=array();
$_SESSION['user']['login']=$login;
$_SESSION['user']['password']=$password;
$_SESSION['user']['id']=$id;
header('Location:index.php');
}
?>