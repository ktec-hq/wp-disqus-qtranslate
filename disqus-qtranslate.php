<?php
/*
Plugin Name: Disqus qTranslate
Plugin URI: http://grishka.me
Description: Adds qTranslate support to the Disqus comments plugin by allowing you to register a separate website for each language in a single WordPress installation.
Version: 1.0
Author: Grishka
Author URI: http://grishka.me
*/

function dsq_qtrans_translate($text){
	if(function_exists('qtranxf_useCurrentLanguageIfNotFoundShowEmpty')){
		return qtranxf_useCurrentLanguageIfNotFoundShowEmpty($text);
	}else{
		return qtrans_useCurrentLanguageIfNotFoundShowEmpty($text);
	}
}

function dsq_qtrans_get_blocks($s){
	if(function_exists('qtranxf_get_language_blocks')){
		return qtranxf_get_language_blocks($text);
	}else{
		return qtrans_get_language_blocks($text);
	}
}

function dsq_qtrans_get_lang(){
	if(function_exists('qtranxf_getLanguage')){
		return qtranxf_getLanguage($text);
	}else{
		return qtrans_getLanguage($text);
	}
}

function dsq_qtrans_parse($s){
	$blocks=dsq_qtrans_get_blocks($s);
	if(count($blocks)<3)
		return [];
	$res=[];
	for($i=0;$i<count($blocks)-1;$i+=2){
		$res[preg_replace('/\[:([a-z]+)\]/', '$1', $blocks[$i])]=$blocks[$i+1];
	}
	return $res;
}

function dsq_qtrans_serialize($a){
	$s=[];
	foreach($a as $k=>$v){
		$s[]="[:$k]";
		$s[]=$v;
	}
	$s[]="[:]";
	return implode("", $s);
}


/*
* This works by adding the qTranslate tags to the options that Disqus plugin stores its configuration in
* and then returning an appropriate value depending on the current language.
*/
$dsq_qtrans_hook_options=["disqus_api_key", "disqus_active", "disqus_cc_fix", "disqus_forum_url", "disqus_last_comment_id", "disqus_user_api_key"];
foreach($dsq_qtrans_hook_options as $v){
	add_filter("option_$v", "dsq_qtrans_translate_option");
	add_filter("pre_update_option_$v", create_function('$value', 'return dsq_qtrans_update_option("'.$v.'", $value);'));
}

function dsq_qtrans_get_option_raw($option){
	global $dsq_qtrans_bypass;
	$dsq_qtrans_bypass=true;
	$result=get_option($option);
	$dsq_qtrans_bypass=false;
	return $result;
}

function dsq_qtrans_translate_option($option){
	global $dsq_qtrans_bypass;
	if($dsq_qtrans_bypass)
		return $option;
	return dsq_qtrans_translate($option);
}

function dsq_qtrans_update_option($name, $new){
	$old=dsq_qtrans_get_option_raw($name);
	$a=dsq_qtrans_parse($old);
	$a[dsq_qtrans_get_lang()]=$new;
	$result=dsq_qtrans_serialize($a);
	return $result;
}


