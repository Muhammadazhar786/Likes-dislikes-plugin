<?php
/**
 * Plugin Name: Azhar likes dislikes plugin
 * Description: This plugin is developed to give likes or dislikes on posts.
 * Plugin URI: http://www.xyz.com/
 * Author: Muhammad Azhar
 * Author URI: http://www.xyz.com/
 * Version: 1.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: Likes dislikes plugin
 * Name is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Name is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Name. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

defined( 'ABSPATH' ) || exit;
define('PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'TABLE_NAME', 'likesdislikes');

class Azharlikesdislikes {

	private static $_instance = null;

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	private function __construct() {
	add_filter('the_content', array($this, 'add_likes_dislikes'));
	add_action('wp_enqueue_scripts', array($this, 'Azhar_enqueue_script'));
	add_action('wp_ajax_my_likes_dislikes_action', array($this, 'do_ajax_action'));
	add_action('wp_ajax_nopriv_my_likes_dislikes_action',array($this, 'do_ajax_action'));
	}
	public function do_ajax_action(){
           if(isset($_POST['action'])):
           global $wpdb;
           global $post;
           $state = $_POST['state'];
           $post_id = $_POST['post'];
           $table = $wpdb->prefix.TABLE_NAME;
           $user_id = get_current_user_id();
           $row = $wpdb->get_row("SELECT * FROM `{$table}` WHERE 
           	`post_id` =  {$post_id} AND `user_id` = {$user_id}", ARRAY_A);
           if($row == null) {
           	   $wpdb->insert($table, [
           	   	'user_id' => $user_id,
           	   	'post_id' => $post_id,
           	   	 $state => 1,

         ]);
           }elseif ($row['user_id'] == $user_id && $row[$state] == 0) {
           	     $wpdb->delete($table,[
           	     	'post_id' => $post_id,
           	     	'user_id' => $user_id

           	     ]);
           	     $wpdb->insert($table,[
           	   	'post_id' => $post_id,
           	   	'user_id' => $user_id, 
           	   	 $state => 1
           	   	]);
           }else{
           	   $wpdb->delete($table,[
           	     'post_id' => $post_id,
           	     'user_id' => $user_id,
           	 ]);
          
}
        endif;
         $likes = $wpdb->get_row("SELECT COUNT(*) as likes FROM `{$table}` WHERE 
	     `post_id`={$post_id} AND `like` !=0",ARRAY_A);
         $dislikes = $wpdb->get_row("SELECT COUNT(*) as dislikes FROM `{$table}`WHERE 
	`post_id` = {$post_id} AND `dislike` != 0", ARRAY_A);
       echo json_encode(array(
          'like' => $likes['likes'],
          'dislike' => $dislikes['dislikes']
         ));
         wp_die();
	}
	 public function Azhar_enqueue_script() {
	 	global $post;
	 	wp_enqueue_style('like-dislike-style', PLUGIN_URL.'css/app.css');
	 	wp_enqueue_script('like-dislike-script', PLUGIN_URL.'js/app.js', array('jquery'), 'version', true);
	 	wp_localize_script( 'like-dislike-script','ajax_object', array('url'=>admin_url('admin-ajax.php'), 'post' => $post->ID));
	 }
	function add_likes_dislikes($content){
	 	if (is_user_logged_in()) {
	 		global $wpdb;
	 		global $post;
	 		$table = $wpdb->prefix.TABLE_NAME;
	 		$post_id = $post->ID;
$likes = $wpdb->get_row("SELECT COUNT(*) as 'likes' FROM `{$table}` WHERE 'post_id'= {$post_id} AND `like` != 0", ARRAY_A);
         $dislikes = $wpdb->get_row("SELECT COUNT(*) as 'dislikes' FROM `{$table}` WHERE 'post_id' = {$post_id} AND `dislike` != 0", ARRAY_A);
         	 		$description = "
	 	<ul id='likes-dislikes'>
	 <li><a data-val='like' href='javascript:;'>Like</a><span>[".$likes['likes']."]</span></li>
	 		<li><a data-val='dislike' href='javascript:;'>Dislike</a><span>[".$dislikes['dislikes']."]</span></li>
	 	</ul>	
	 		";
	 		return $content.$description;
	 	}
	 	return $content;
	 }

	public static function do_activate( $network_wide ) {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		global $wpdb;
		$table = $wpdb->prefix.TABLE_NAME;
		$collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS `{$table}`(
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` int(11) DEFAULT NULL,
		`post_id` int(11) DEFAULT NULL,
		`like` int(11) DEFAULT 0,
		`dislike` int(11) DEFAULT 0,
		`date_created` timestamp DEFAULT NOW(),
		PRIMARY KEY  (`id`)
		) {$collate};";
		require_once( ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql); 

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );
	}

	public static function do_deactivate( $network_wide ) {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		global $wpdb;
		$table = $wpdb->prefix.TABLE_NAME;
		$sql = "TRUNCATE TABLE `{$table}`";
		$wpdb->query($sql);

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
	}

	public static function do_uninstall( $network_wide ) {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		global $wpdb;
		$table = $wpdb->prefix.TABLE_NAME;
		$sql = "DROP TABLE `{$table}`";
		$wpdb->query($sql);

		check_admin_referer( 'bulk-plugins' );

		if ( __FILE__ != WP_UNINSTALL_PLUGIN  )
			return;
	}
}

add_action( 'plugins_loaded', 'Azharlikesdislikes::instance' );
register_activation_hook( __FILE__, 'Azharlikesdislikes::do_activate' );
register_deactivation_hook( __FILE__, 'Azharlikesdislikes::do_deactivate' );
register_uninstall_hook( __FILE__, 'Azharlikesdislikes::do_uninstall' );