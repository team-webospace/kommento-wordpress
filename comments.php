<?php
   $valueKommentoId=get_option('kommento_id','');
   $valueKommentoSecret=get_option('kommento_secret',''); 
   $valueKommentoSSOEnable=get_option('kommento_sso_enable',0);
   $valueKommentoSitename=get_option('kommento_site_name',get_bloginfo("name"));
   $loginUrl=str_replace('&amp;','&', wp_login_url(get_permalink()) );  //hack for &amp; returned for & character in url
   $logoutUrl=str_replace('&amp;','&', wp_logout_url( get_permalink()) ); //hack for &amp; returned for & character in url
   $currentPost=get_post( get_the_ID() );
  
?>
<a id="comments"></a>
<a id="respond"></a>
<div id="kommento_comments"> 

</div> 

<?php if(is_user_logged_in() && $valueKommentoSecret && $valueKommentoSSOEnable){
	
  $currentUserId=get_current_user_id();
  $userData=get_userdata($currentUserId);

  

  $timestamp=time();
  $ssoOptions=[
   'id'=>  $userData->ID,
   'fullname'=> $userData->user_nicename,
   'email' => $userData->user_email, 
   'avatar_url'=> function_exists('get_avatar_url') ? get_avatar_url($userData->user_email) : '',
   'profile_url'=> function_exists('get_author_posts_url') ? get_author_posts_url( $userData->ID, $userData->user_nicename ) : '',
  ];
  
  $ssoOptionsStr=base64_encode( json_encode( $ssoOptions ) );
  $kommentoSecret=$valueKommentoSecret;
  $ssoHash= hash('sha256',"$ssoOptionsStr $kommentoSecret $timestamp",false);
  $ssoData="$ssoOptionsStr $ssoHash $timestamp"	
	
?>

<script type="text/javascript"> 

  var kommento_config={ 
     'website':'<?php  echo $valueKommentoId; ?>', 
     'permalink':'<?php  echo get_permalink(); ?>', 
     'ssoLogin': '<?php  echo $ssoData; ?>',
     'websiteTitle': '<?php  echo $valueKommentoSitename; ?>',
     'ssoLoginUrl': '<?php  echo  $loginUrl ?>',
	  'ssoLogoutUrl': '<?php  echo $logoutUrl ?>',
	  'comment_count_id': '<?php echo get_the_ID(); ?>', 
	  'comment_count_url': "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=kommento_count_update",
	  'comment_count_ref': "<?php echo $currentPost->comment_count; ?>",	    
  }; 

 (function() { 
     var d = document, s = d.createElement('script'); 
     s.src = 'https://script.webospace.com/kommento.js'; 
     (d.head || d.body).appendChild(s); 
  })(); 

</script>

<?php }else{?>
<script type="text/javascript"> 

  var kommento_config={ 
     'website':'<?php  echo $valueKommentoId; ?>', 
     'permalink':'<?php  echo get_permalink(); ?>', 
     'websiteTitle': '<?php  echo $valueKommentoSitename; ?>',
     <?php if($valueKommentoSSOEnable){?>
		 'ssoLoginUrl': '<?php  echo $loginUrl ?>',
		 'ssoLogoutUrl': '<?php  echo $logoutUrl ?>',
	 <?php }?>	 
	 'comment_count_id': '<?php echo get_the_ID(); ?>', 
	 'comment_count_url': "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=kommento_count_update",
	 'comment_count_ref': "<?php echo $currentPost->comment_count; ?>",
  }; 

 (function() { 
     var d = document, s = d.createElement('script'); 
     s.src = 'https://script.webospace.com/kommento.js'; 
     (d.head || d.body).appendChild(s); 
  })(); 

</script>
<?php }?>
