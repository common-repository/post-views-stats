<?php
/*
Plugin Name: Post Views Stats
Plugin URI: http://www.cybernetikz.com
Description: This plugins will track each post/page views by visitor. You will be able to see the post view count in "Posts" and "All posts" page with a "View count" column, also you can use the widget to display the most popular post in the sidebar ares.
Version: 1.4.1
Author: CyberNetikz
Author URI: http://www.cybernetikz.com
License: GPL2
*/

if( !defined('ABSPATH') ) die('-1');

$pluginURI = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));

function cn_tpv_admin_sidebar() {

	$banners = array(
		array(
			'url' => 'http://www.cybernetikz.com/wordpress-magento-plugins/wordpress-plugins/?utm_source=post-views-stats&utm_medium=banner&utm_campaign=wordpress-plugins',
			'img' => 'banner-1.jpg',
			'alt' => 'Banner 1',
		),
		array(
			'url' => 'http://www.cybernetikz.com/portfolio/web-development/wordpress-website/?utm_source=post-views-stats&utm_medium=banner&utm_campaign=wordpress-plugins',
			'img' => 'banner-2.jpg',
			'alt' => 'Banner 2',
		),
		array(
			'url' => 'http://www.cybernetikz.com/seo-consultancy/?utm_source=post-views-stats&utm_medium=banner&utm_campaign=wordpress-plugins',
			'img' => 'banner-3.jpg',
			'alt' => 'Banner 3',
		),
	);
	shuffle( $banners );
	?>
	<div class="cn_admin_banner">
	<?php
	$i = 0;
	foreach ( $banners as $banner ) {
		echo '<a target="_blank" href="' . esc_url( $banner['url'] ) . '"><img width="261" height="190" src="' . plugins_url( 'images/' . $banner['img'], __FILE__ ) . '" alt="' . esc_attr( $banner['alt'] ) . '"/></a><br/><br/>';
		$i ++;
	}
	?>
	</div>
<?php
}

function cn_tpv_admin_style() {
	global $pluginURI;
	wp_register_style( 'cn_tpv_admin_css', $pluginURI . '/css/admin-style.css', false, '1.0' );
	wp_enqueue_style( 'cn_tpv_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'cn_tpv_admin_style' );

function cn_tpv_db_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "cn_track_post";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
		`id` bigint(20) NOT NULL AUTO_INCREMENT,
		`post_id` int(11) NOT NULL,
		`created_at` varchar(20) NOT NULL,
		`create_date` varchar(20) default NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

register_activation_hook(__FILE__,'cn_tpv_db_install');

function wpgt_add_pages() {
	global $pluginURI;
	add_menu_page('Post Views Stats', 'Post Views Stats', 'manage_options', 'cn_tpv_view_post', 'cn_tpv_view_post_fn',$pluginURI.'/images/stat.png' );
}
add_action('admin_menu', 'wpgt_add_pages');

function cn_tpv_view_post_fn() { 
	ob_start();
	include_once('view.php');
	$out1 = ob_get_contents();
	ob_end_clean();	
	echo $out1;
}

function cn_tpv_most_popular($num) { 
	ob_start();
	include_once('view-most-popular.php');
	$out1 = ob_get_contents();
	ob_end_clean();	
	return $out1;
}

function cn_tpv_isBot($user_agent){
	$bots = array ( 0 => 'bot', 1 => 'spider', 2 => 'crawl', 3 => 'google', 4 => 'msn', 5 => 'aol', 6 => 'yahoo' );
    $user_agent = strtolower($user_agent);
    foreach($bots as $bot) {
        if(strpos($user_agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

function track_post_view() {
	
	global $wpdb, $post;
	$ua = $_SERVER['HTTP_USER_AGENT'];
	
	if( !wp_is_post_revision( $post ) && !is_preview() ) {

		if( is_single() || is_page() ) {
			
			if( !cn_tpv_isBot($_SERVER['HTTP_USER_AGENT']) ) {
			
				$table_name = $wpdb->prefix . "cn_track_post";
				$insert = "INSERT INTO " . $table_name . "( post_id, created_at, create_date ) VALUES (" . $post->ID . ", '" . time() . "', '" . date('Y-m-d')."')";
				$results = $wpdb->query( $insert );
				//if($results) file_put_contents(dirname(__FILE__).'/log.txt',$post->ID.", ",FILE_APPEND);
			}
		}
	}
}

add_action('wp_head', 'track_post_view');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

function cn_tpv_jQuery_files() {

echo '
<script>
jQuery(function() {
	jQuery( "#from" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		//changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#to" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	jQuery( "#to" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		//changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#from" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});// JavaScript Document    
</script>
';
}

function cn_tpv_my_script() {
	global $pluginURI;
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
	wp_register_style('jquery-ui-css', $pluginURI . '/css/jquery-ui.css', array(), '1.11.4' );
	wp_enqueue_style( 'jquery-ui-css' );	
}

add_action('admin_init', 'cn_tpv_my_script');
add_action('admin_head', 'cn_tpv_jQuery_files');

function cn_tpv_columns_head($defaults) { 
	$defaults['view_count'] = 'View Count';  
	return $defaults;
}

function cn_tpv_get_view_count($post_ID) {  
	global $wpdb;
	$table_name = $wpdb->prefix . "cn_track_post";
	$select="SELECT *,count(*) as counts FROM $table_name WHERE post_id=$post_ID group by post_id order by counts desc";
	$tabledata = $wpdb->get_row($select);
	$view_count = isset($tabledata->counts)?$tabledata->counts:0;
	return $view_count;
}

function cn_tpv_columns_content($column_name, $post_ID) {  
	if ($column_name == 'view_count') { 
		echo cn_tpv_get_view_count($post_ID);
	}  
}

class Cntpv_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'cntpv_widget', // Base ID
			'Post Views Stats', // Name
			array( 'description' => __( 'Most Popular Post' ) ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$number_of_post = $instance['number_of_post'];
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo cn_tpv_most_popular($number_of_post);
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_of_post'] = strip_tags( $new_instance['number_of_post'] );
		return $instance;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Most Popular Post' );
		}
		if ( isset( $instance[ 'number_of_post' ] ) ) {
			$number_of_post = $instance[ 'number_of_post' ];
		}
		else {
			$number_of_post = 5;
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /><br /><br />
		<label for="<?php echo $this->get_field_id( 'number_of_post' ); ?>"><?php _e( 'Number of post to view:' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'number_of_post' ); ?>" name="<?php echo $this->get_field_name( 'number_of_post' ); ?>" type="text" value="<?php echo esc_attr( $number_of_post ); ?>" /></p>
		<?php 
	}

} // class Cnss_Widget

//add_action( 'widgets_init', create_function( '', 'register_widget( "Cntpv_Widget" );' ) );
$callback = function() {
    echo register_widget( "Cntpv_Widget" );
};
add_action( 'widgets_init', $callback );
add_filter('manage_posts_columns', 'cn_tpv_columns_head');  
add_action('manage_posts_custom_column', 'cn_tpv_columns_content', 10, 2);  

add_filter('manage_pages_columns', 'cn_tpv_columns_head');  
add_action('manage_pages_custom_column', 'cn_tpv_columns_content', 10, 2);  