<?php 
if( !defined('ABSPATH') ) die('-1');
global $wpdb;
$table_name = $wpdb->prefix . "cn_track_post";
if(isset($_POST['to']) and isset($_POST['from'])) {
	$select = "SELECT *,count(*) as counts FROM $table_name WHERE create_date >='".$_POST['from']."' AND create_date<='".$_POST['to']."' group by post_id order by counts desc LIMIT 0,".$num;
} else {
	$select = "SELECT *,count(*) as counts FROM $table_name WHERE 1 group by post_id order by counts desc LIMIT 0,".$num;
}
$tabledata = $wpdb->get_results($select);
echo '<ul>';
$i=1; foreach($tabledata as $data) { 
$posts = get_post($data->post_id); 
$title = $posts->post_title;
?><li><a href="<?php echo get_permalink( $data->post_id ); ?>"><?php echo $title?$title:'(No Title)' ?></a></li>
<?php $i++; } 
echo '</ul>';
?>