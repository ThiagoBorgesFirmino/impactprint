<?php

function get_ultimo_post_fan_page($page_id){

	$opts = array(
		'http'=>array(
			'method'=>"GET"
			,'header'=>"Accept-language: en\r\n"
			."User-Agent: DoCoMo/1.0/P503i\r\n"
			."Cookie: foo=bar\r\n"
		)
	);

	$context = stream_context_create($opts);

	$str = "http://pt-br.facebook.com/feeds/page.php?id={$page_id}&format=rss20";
	
	// http://pt-br.facebook.com/feeds/page.php?id=219794864835342&format=rss20

	$xml = file_get_contents($str, false, $context);

	$obj = @new SimpleXMLElement($xml);
	return $obj;

}

function get_ultima_foto_instagram($instagram_username){

	$opts = array(
		'http'=>array(
			'method'=>"GET"
			,'header'=>"Accept-language: en\r\n"
			."User-Agent: DoCoMo/1.0/P503i\r\n"
		)
	);

	$context = stream_context_create($opts);

	$str = "http://statigr.am/feed/{$instagram_username}";
	
	// printr($str);

	// printr($str);
	// http://pt-br.facebook.com/feeds/page.php?id=219794864835342&format=rss20

	$xml = file_get_contents($str, false, $context);
	
	// printr($xml);
	
	$obj = @new SimpleXMLElement($xml);
	return $obj;

}

function get_ultimo_post_blog(){

	$opts = array(
		'http'=>array(
			'method'=>"GET"
			,'header'=>"Accept-language: en\r\n"
			."User-Agent: DoCoMo/1.0/P503i\r\n"
		)
	);

	$context = stream_context_create($opts);

	$str = "http://blogpulcor.blogspot.com/feeds/posts/default?alt=rss";
	
	// http://pt-br.facebook.com/feeds/page.php?id=219794864835342&format=rss20

	$xml = file_get_contents($str, false, $context);

	$obj = @new SimpleXMLElement($xml);
	return $obj;

}
