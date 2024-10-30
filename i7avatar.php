<?php
/**
 * @package i7avatar
 * @version 1.2
 */
/*
Plugin Name: i7avatar
Plugin URI: http://wordpress.org/plugins/i7avatar/
Description: 这是一个基于“i7avatar 头像服务”的插件，安装并启用此插件后，就能在您的博客中使用更优秀的头像服务了。
Author: shiqiren
Version: 1.2
Author URI: http://www.i7avatar.com/
*/

function i7_get_avatar($avatar) {

	// 本地默认头像
	// 此值不设置的话，将把原来 gravatar 的图片地址作为默认图片
	// 即在不设置的情况下，如果用户没有在 i7avatar 注册的话，会自动再从 gravatar 获取图片
	// PS:gravatar 可另外再设置默认图片，即当用户也没在 gravatar 注册的话，再经过 gravatar 返回您设置的默认图片
	$default_img="";//如：/wp-content/avatar/default.gif
	//echo $avatar;

	// 得到图片属性 src 的值
	preg_match_all("/src='(.*?)'/", $avatar, $match);
	if(!is_array($match[1]) || empty($match[1])){
		preg_match_all('/src="(.*?)"/', $avatar, $match);
	}

	$img_src = $match[1][0];

	// 截取加密后的 e-mail 地址
	$arr_temp = explode('?', $img_src);
	$arr_url = explode('/', $arr_temp[0]);
	$email_hash = empty($arr_url[4])?'empty':$arr_url[4];
	
	// 组合获取头像的 API
	$result = 'http://v'.substr($email_hash,0,1).'.i7avatar.com/'.$email_hash.'?'.(empty($default_img)? $img_src: get_bloginfo('wpurl').$default_img);
	
	// 生成新的图片 html 字符串
	$avatar = str_replace($img_src, $result, $avatar);

	// 是否启用头像缓存，第一次缓存的时候，页面打开也许会慢，这由服务器的性能决定
	// 请根据情况考虑是否启用
	$use_imgCache = false;

	if($use_imgCache){

		$cache_path='wp-content/avatar/';

    	$domain = get_bloginfo('wpurl').'/';
    	$cache_file = ABSPATH .$cache_path. $email_hash .'.jpg';
    	$t = 2592000; // 缓存保存的时间，这里是30天, 单位:秒

	    if ( !is_file($cache_file) || (time() - filemtime($cache_file)) > $t ) { //當头像不存在或文件超过30天才更新

	        copy(htmlspecialchars_decode($result), $cache_file);

	    }else $avatar = strtr($avatar, array($result => $domain.$cache_path.$email_hash.'.jpg'));

	    if ( filesize($cache_file) < 500 ) copy($domain.$cache_path.'default.jpg', $cache_file);

	    return $avatar;
	}

    return $avatar;

}add_filter('get_avatar', 'i7_get_avatar');

?>
