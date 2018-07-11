<?php
/*
 * Plugin Name: Kommento
 * Version: 1.0.0 
 * Description: Integrates Kommento Comments into Wordpress Site.
 * Author: George Cyriac
 * Author URI: https://www.webospace.com
 * Plugin URI: https://kommento.webospace.com
 * Text Domain: kommento
 * License: GPLv2 or later
*/



// We need some CSS to position the paragraph
function kommento_plugin_css() {

	echo "
	<style type='text/css'>

	label
	{
	   font-weight:bold;
	   margin-right:5px;
	   font-size:1.1em;
	   
    }
    input
    {
      margin-bottom:10px;
      font-size:1.1em;
    }
    code
    {
       display:block;
       margin-bottom:20px;
    }
	</style>
	";
}

add_action( 'admin_head', 'kommento_plugin_css' );

function kommento_plugin_menu() {
  if ( current_user_can( 'manage_options' ) ) 
  {
  	 add_options_page( 'Kommento Options', 'Kommento', 'read', 'kommento', 'kommento_plugin_options' );
  }
}
add_action( 'admin_menu', 'kommento_plugin_menu' );	



function kommento_plugin_options() {
    if ( !current_user_can( 'manage_options' ) ) 
    {
       return '';
    }
	$komSettingsSaved = false;

	if ( isset( $_POST[ 'save' ] ) ) {	
	  	
		update_option( 'kommento_id', isset($_POST['kommento_id']) ?  sanitize_text_field( $_POST['kommento_id'] ) : '' , true );
		update_option( 'kommento_secret', isset($_POST['kommento_secret']) ?  sanitize_text_field( $_POST['kommento_secret'] ) : '' , true );	
		update_option( 'kommento_sso_enable', isset($_POST['kommento_sso_enable']) ?  sanitize_text_field( $_POST['kommento_sso_enable'] ) : '0' , true );
		update_option( 'kommento_site_name', isset($_POST['kommento_site_name']) ?  sanitize_text_field( $_POST['kommento_site_name'] ) : '' , true );
		$komSettingsSaved = true;
	}
	$komYesNoOptions=['0'=>'No','1'=>'Yes'];
	
   $valueKommentoId=get_option('kommento_id','');
   $valueKommentoSecret=get_option('kommento_secret','');
   $valueKommentoSSOEnable=get_option('kommento_sso_enable',0);
   $valueKommentoSitename=get_option('kommento_site_name','');
   
   
	?>
  
	<div class="wrap">
		<h1 style='margin-bottom:20px;'><?php _e( 'Kommento Settings', 'kommento-plugin' ); ?></h1>
       	<code>Please login to your <a href='https://kommento.webospace.com' target='_blank'>Kommento</a> Control Panel for Kommento Options</code>
		<?php if ( $komSettingsSaved ) : ?>
			<div id="message" class="updated fade">
				<p><strong><?php _e( 'Options saved.' ) ?></strong></p>
			</div>
		<?php endif ?>
		
		<form method="post" action="">
			
			<table class="form-table">
			<tbody>
			<tr>
			   <th scope="row"><label for="default_category">Kommento ID</label></th>
			    <td>
			        <input type='text'  name='kommento_id' value="<?php  echo $valueKommentoId; ?>" placeholder='Kommento ID'><br>
			</td>
			</tr>
			<tr>
			   <th scope="row"><label for="default_category">Kommento Secret</label></th>
			    <td>
			        <input type='password'  name='kommento_secret' value="<?php  echo $valueKommentoSecret; ?>" placeholder='Kommento Secret'><br>
			    </td>
			</tr>
		
			<tr>
				<th scope="row"><label for="default_post_format">Enable Single Sign On</label></th>
				<td>
					<select name="kommento_sso_enable" id="default_post_format">
					   <?php foreach($komYesNoOptions as $key=>$value){?>
					     	<option <?php if($key==$valueKommentoSSOEnable){?> selected <?php }?> value="<?php  echo $key; ?>"><?php  echo $value; ?></option>
					   <?php }?>				
					</select>
				</td>
			</tr>
			<tr>
			   <th scope="row"><label for="default_category">Website Title</label></th>
			    <td>
			        <input type='text'  name='kommento_site_name' value="<?php  echo $valueKommentoSitename; ?>" placeholder='Website Title'><br>
			</td>
			</tr>
			</tbody></table>
			<div>
			
			
		
		
			</div>
			<p class="submit">
				<input class="button-primary" name="save" type="submit" value="<?php _e( 'Save Changes' ) ?>" />
			</p>
		</form>
	</div>

<?php
}


function add_action_links ( $links ) {
 $mylinks = array(
 '<a href="' . admin_url( 'options-general.php?page=kommento' ) . '">Settings</a>',
 );
return array_merge( $links, $mylinks );

}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );


function kommento_comments_enabled()
{
	$valueKommentoId=get_option('kommento_id','');
   $valueKommentoSecret=get_option('kommento_secret','');
   $valueKommentoSSOEnable=get_option('kommento_sso_enable',0);
   if($valueKommentoId && $valueKommentoSecret)
   {
     return true;   
   }
  return false;
}

function kommento_comments_open($open, $post_id=null) {

	if(kommento_comments_enabled())
   {
     return true;
   } 
  return $open;
}
add_filter('comments_open', 'kommento_comments_open');


function kommento_comments_template($value) {
   if(kommento_comments_enabled())
   {
     return dirname(__FILE__) . '/comments.php';
   } 
   return $value;    
}

add_filter ('comments_template', 'kommento_comments_template', 100);

function kommento_menu_page_removing() {
	if(function_exists('remove_menu_page'))
     {
	  remove_menu_page( 'edit-comments.php' );
     }	   
}
add_action( 'admin_menu', 'kommento_menu_page_removing' );

function kommento_count_update() {
	if(!kommento_comments_enabled())
	{
	   echo 'Kommento Comments not enabled';
	   die();
	}
	$countData=$_POST['count_data'];
	$parts=explode(' ',$countData);
	if(sizeof($parts)>4)
	{
      echo 'Size of Count Data greater than 4';	
      die();
	}
	$kommentoSecret=get_option('kommento_secret','');
	$verifyHash=hash('sha256',$parts[0].' '.$parts[1].' '.$parts[2].' '.$kommentoSecret);
	if( $verifyHash!=$parts[3] )
	{
      echo 'Unable to verify hash. Please check Secret Key is Correct';	
      die();
	}
	else {
		 $post=get_post($parts[0]);
		 global $wpdb;
		 $wpdb->update( 
			"{$wpdb->prefix}posts", 
			array( 
				'comment_count' => $parts[1],	// string	
			), 
		 	array( 'ID' => $parts[0] ),
		 	array( 
		       '%s',	// value1
	      ), 
	      array( '%s' ) 
		 );
		 echo 'Success';
	}
	die();
}
add_action( 'wp_ajax_nopriv_kommento_count_update', 'kommento_count_update' );
add_action( 'wp_ajax_kommento_count_update', 'kommento_count_update' );
