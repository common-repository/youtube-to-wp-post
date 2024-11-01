<?php
$title = __('Youtube to WP Post'); 
$i=0;
$videoDetails = array();
if(isset($_REQUEST['submitYouTubeURLS']) && $_REQUEST['submitYouTubeURLS']  ){
if(trim(empty($_REQUEST['videoURLs'])))
 echo '<div id="message" class="updated fade"><p><strong>'.__('Please enter video URL\'s.',"mu").'</strong></p></div>';
 else {
	$youTubeURLS = array_filter(explode("\n",$_REQUEST['videoURLs'])); 
		
	foreach($youTubeURLS as $yt){
	 $vedioID = getVideoID($yt);
	 if(checkYoutubeId($vedioID) == 1){
	 $xml = getVideoDetails($vedioID); 
	 if($xml->title != 'YouTube Videos') {
      // load up the array
	  $videoDetails[$i]['videoURL'] = $yt;
      $videoDetails[$i]['title'] = $xml->title;
      $videoDetails[$i]['description'] = $xml->content;
      $videoDetails[$i++]['thumbnail'] = "http://i.ytimg.com/vi/".$vedioID."/0.jpg";
	   } 
	  }
	}
  }
  if(empty($videoDetails) && !empty($_REQUEST['videoURLs']))
	 echo '<div id="message" class="updated fade"><p><strong>'.__('Please enter valid video URL\'s.',"mu").'</strong></p></div>';	
}


if(isset($_REQUEST['submitPostData']) && $_REQUEST['submitPostData']  ){

 $totalCount = count($_REQUEST['muTitle']);
 $postCount = 0;
 for($i=1;$i<=$totalCount;$i++){
    if(@in_array($i,$_REQUEST['muCheckBox'])){
 	$postTitle = trim($_REQUEST['muTitle'][$i]);
	$youTubeURL = trim($_REQUEST['muVideoURL'][$i]);
	$postDescription = trim($_REQUEST['description'][$i]);	
	$postContent = $postDescription;
	$postTags = $_REQUEST['muTags'][$i];
	$postCategories = @implode(',',$_REQUEST['mucategories'][$i]);
	$postThumbnail = $_REQUEST['muThumbnail'][$i];	
	
	
	$post = array(
                    'post_title'    => wp_strip_all_tags($postTitle),
                    'post_content'  => $postContent,
                    'post_category' =>  @explode(',',$postCategories),
                    'tags_input'    => $postTags,
                    'post_status'   => 'publish',
					'post_author'   => 1,
                    'post_type' => 'post'
                );
 
            $post_id = wp_insert_post($post);
			$postCount++;
	
			$imageurl = $postThumbnail;
			$imageurl = stripslashes($imageurl);
			$uploads = wp_upload_dir();
			
			$filename = wp_unique_filename( $uploads['path'], basename($imageurl), $unique_filename_callback = null );
			$wp_filetype = wp_check_filetype($filename, null );
			$fullpathfilename = $uploads['path'] . "/" . $filename;
			
			try {
				if ( !substr_count($wp_filetype['type'], "image") ) {
					throw new Exception( basename($imageurl) . ' is not a valid image. ' . $wp_filetype['type']  . '' );
				}
			
				$image_string = fetch_image($imageurl);
				$fileSaved = file_put_contents($uploads['path'] . "/" . $filename, $image_string);
				if ( !$fileSaved ) {
					throw new Exception("The file cannot be saved.");
				}
				
				$attachment = array(
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					 'post_content' => '',
					 'post_status' => 'inherit',
					 'guid' => $uploads['url'] . "/" . $filename
				);
				$attach_id = wp_insert_attachment( $attachment, $fullpathfilename, $post_id );
				if ( !$attach_id ) {
					throw new Exception("Failed to save record into database.");
				}
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attach_data = wp_generate_attachment_metadata( $attach_id, $fullpathfilename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta($post_id,'_thumbnail_id',$attach_id);		
			
			} catch (Exception $e) {
				$error = '<div id="message" class="error"><p>' . $e->getMessage() . '</p></div>';
			}
			
   }
 }
 if($postCount > 0)
	echo '<div id="message" class="updated fade"><p><strong>'.__('Post(s) Added Successfully.',"mu").'</strong></p></div>'; 
?>
<script type="text/javascript">
	location.href= "<?php echo admin_url('admin.php?page=yttowp'); ?>";
</script>
<?php
}

?>
<div class="wrap"> 
 <h2><?php echo esc_html( __($title) ); ?></h2>
 <?php if(empty($videoDetails)) { ?>
 <div style="padding:10px;color:green;font-size:16px;">Paste youtube url's here (Each url in different line)</div>
 <div class="clear"></div> 
<form id="muYoutubeURL" name="muYoutubeURL" action="<?php echo admin_url('admin.php?page=yttowp'); ?>" method="post">	
		<div style="float:left; margin-right: 20px;">  
		<div class="imga"></div>       
            <table class="niceblue small-table" cellpadding="0" cellspacing="0">			    
				<tr>                   
                    <td><textarea id="videoURLs" name="videoURLs" cols="83" rows="5"></textarea></td>
                </tr>               
            </table>			 
            <p id="submit" class="submit"><input type="submit" value="<?php _e('Submit',"mu"); ?>" name="submitYouTubeURLS"  /></p>
        </div>
        <div class="clear"></div>
    </form>
 <?php } else {  ?>
 	<form id="add-video" method="POST" action="<?php echo admin_url('admin.php?page=yttowp&action=add-video'); ?>" accept-charset="utf-8">
 <div class="tablenav">
     <div class="alignleft actions">
				<div style="float:left;">
					 
				</div>						
			</div>	
 
     </div>
		<table class="widefat" cellspacing="0">
			<thead>
			<tr>	                        
				<th scope="col" ><?php _e('S.No.'); ?></th>
				<th scope="col" ><?php _e('Title'); ?></th>
				<th scope="col" ><?php _e('Description'); ?></th>
                <th scope="col" ><?php _e('Tags'); ?></th>
                <th scope="col" ><?php _e('Categories'); ?></th>
                <th scope="col" ><?php _e('Thumbnail'); ?></th>               
			</tr>
			</thead>
			     
			<tbody>
				<?php                               
				if($videoDetails) {
							$sn = 1;
                            foreach ( (array) $videoDetails as $vd ) {   
							                                       
                            $class = ( !isset($class) || $class == 'class="alternate"' ) ? '' : 'class="alternate"';
                                          
				?>
						<tr id="gallery-<?php echo $sn ?>" <?php echo $class; ?> >							
							<td>
							 <?php echo $sn ;?>
							 <input type="checkbox" name="muCheckBox[<?php echo $sn; ?>]" value="<?php echo $sn; ?>" checked="checked" />
							</td>							 
							<td><input type="text" name="muTitle[<?php echo $sn; ?>]" value="<?php echo $vd['title'][0];?>" size="40"/></td>	
                            <td><textarea cols="25" rows="4" name="description[<?php echo $sn; ?>]"><?php echo $vd['description'][0];?></textarea></td>	
                            <td><input type="text" name="muTags[<?php echo $sn; ?>]" value="" size="30"/></td>	
                            <td align="left">
                            <?php 
							  $categories = get_categories('hide_empty=0');
							  if(!empty($categories)) { ?>	
                              <div  style="height:95px; overflow-y:scroll" >
                              <?php foreach($categories as $catData) { ?>
                              	<p><input class="checkbox" type="checkbox" id=""  value="<?php echo $catData->term_id; ?>" name="mucategories[<?php echo $sn; ?>][<?php echo $catData->term_id; ?>]" />
       							 <label for=""><?php echo $catData->name; ?> </label></p>
                                <?php } ?>   
                              </div>					
                            	 
                              <?php } ?>  
                            </td>	
                            <td><img src="<?php echo $vd['thumbnail'];?>"  width="100" height="100"/></td>	
                            <input type="hidden" name="muThumbnail[<?php echo $sn; ?>]" value="<?php echo $vd['thumbnail'];?>" />
                            <input type="hidden" name="muVideoURL[<?php echo $sn; ?>]" value="<?php echo $vd['videoURL'];?>" />
                                                       
						</tr>
						<?php					
                        $sn++; }
				}  
				?>			
			</tbody>
		</table>	
        <p id="submit" class="submit"><input type="submit" value="<?php _e('Submit',"mu"); ?>" name="submitPostData"  /></p>
      </form>
 <?php } ?>
	 
</div><!-- wrap -->