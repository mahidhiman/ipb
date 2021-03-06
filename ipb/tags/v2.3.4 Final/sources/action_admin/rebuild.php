<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2007-12-17 18:06:57 -0500 (Mon, 17 Dec 2007) $
|   > $Revision: 1149 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Admin Rebuild Counter Functions
|   > Module written by Matt Mecham
|   > Date started: 9th March 2004
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_rebuild {

	var $base_url;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "tools";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "rebuild";


	function auto_run() 
	{
		switch($this->ipsclass->input['code'])
		{
			case 'docount':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recount' );
				$this->docount();
				break;
			case 'doresyncforums':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recount' );
				$this->resync_forums();
				break;
			case 'doresynctopics':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recount' );
				$this->resync_topics();
				break;
			case 'doposts':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_posts();
				break;
			case 'dopostnames':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_post_names();
				break;
			case 'dopostcounts':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_post_counts();
				break;
			case 'dothumbnails':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_thumbnails();
				break;
			case 'dophotos':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_photos();
				break;				
			case 'doattachdata':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->rebuild_attachdata();
				break;
			case 'cleanattachments':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->clean_attachments();
				break;
			case 'cleanavatars':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->clean_avatars();
				break;
			case 'cleanphotos':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->clean_photos();
				break;
			//-----------------------------------------
			// Tools 
			//-----------------------------------------
			
			case '220tool_photos':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_220_photos();
				break;
				
			case '220tool_contacts':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_220_contacts();
				break;
			
			case '220tool_templatebits':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_220_template_bits();
				break;
			case '210polls':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_210_polls();
				break;
				
			case '210calevents':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_210_calevents();
				break;
			case '210tool_settings':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_210_dupe_settings();
				break;
				
			case 'tool_settings':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_dupe_settings();
				break;
				
			case 'tool_converge':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tools_converge();
				break;
			
			case 'tool_bansettings':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':rebuild' );
				$this->tool_bansettings();
				break;
				
			case 'tools':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->tools_splash();
				break;
			
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->rebuild_start();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// 220: Photos
	/*-------------------------------------------------------------------------*/
	
	function tools_220_photos()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$start           = intval($_GET['st']);
		$lend            = 50;
		$end             = $start + $lend;
		$done            = 0;
		$updated         = 0;
		
		//-----------------------------------------
		// Get lib
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/lib/func_usercp.php' );
		$func_70s_style           =  new func_usercp;
		$func_70s_style->ipsclass =& $this->ipsclass;
		
		require_once( KERNEL_PATH.'class_image.php' );
		$image_lib = new class_image();		
		
		//-----------------------------------------
		// OK..
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'me.*',
											 	 'from'   	=> array( 'member_extra' => 'me' ),
											 	 'where'  	=> "me.photo_type='upload'",
											     'limit'  	=> array( $start, $lend ),
											     'add_join'	=> array(
											     					array(
											     							'type'		=> 'left',
											     							'select'	=> 'm.mgroup',
											     							'from'		=> array( 'members' => 'm' ),
											     							'where'		=> 'm.id=me.id'
											     						),
											     					array(
											     							'type'		=> 'left',
											     							'select'	=> 'g.g_photo_max_vars',
											     							'from'		=> array( 'groups' => 'g' ),
											     							'where'		=> 'g.g_id=m.mgroup'
											     						),
											     					)											     					
										) 		);
											
		$o = $this->ipsclass->DB->exec_query();
		
		//-----------------------------------------
		// Do it...
		//-----------------------------------------

		if ( $this->ipsclass->DB->get_num_rows($o) )
		{
			//-----------------------------------------
			// Got some to convert!
			//-----------------------------------------
			
			while( $row = $this->ipsclass->DB->fetch_row( $o ) )
			{
				//-----------------------------------------
				// INIT
				//-----------------------------------------
				
				$member_id = intval( $row['id'] );
				$photo     = array();
				
				//-----------------------------------------
				// Not got a photo?
				//-----------------------------------------
				
				if ( $member_id AND $row['photo_location'] )
				{
					//-----------------------------------------
					// Get member
					//-----------------------------------------
					
					$member = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																  				'from'   => 'profile_portal',
																  				'where'  => "pp_member_id=".$member_id ) );
					
					if ( ! $member['pp_main_photo'] )
					{
						//-----------------------------------------
						// Resize image... 150 / 150 / 75 / 75
						//-----------------------------------------
					
						list( $p_max, $p_width, $p_height ) = explode( ":", $row['g_photo_max_vars'] );
						
						$_dims = explode( ',', $row['photo_dimensions'] );
						
						$_main = $this->ipsclass->scale_image( array( 'max_height' => 150,
																	  'max_width'  => 150,
																	  'cur_width'  => $_dims[0],
																	  'cur_height' => $_dims[1] ) );
																	
						$_thumb = $this->ipsclass->scale_image( array( 'max_height' => 50,
																	   'max_width'  => 50,
																	   'cur_width'  => $_dims[0],
																	   'cur_height' => $_dims[1] ) );
																	   
						if ( ! $this->ipsclass->vars['disable_ipbsize'] )
						{
							//-----------------------------------------
							// Main photo
							//-----------------------------------------
							
							$image_lib->in_type        = 'file';
							$image_lib->out_type       = 'file';
							$image_lib->in_file_dir    = $this->ipsclass->vars['upload_dir'];
							$image_lib->in_file_name   = $row['photo_location'];
							$image_lib->out_file_name  = 'photo-'.$row['id'];
							$image_lib->desired_width  = $p_width;
							$image_lib->desired_height = $p_height;
							
							$return = $image_lib->generate_thumbnail();

							$im['img_width']  = $return['thumb_width'];
							$im['img_height'] = $return['thumb_height'];
							
							# Do we have an attachment?
							if ( isset( $return['thumb_location'] ) AND strstr( $return['thumb_location'], 'photos-' ) )
							{
								//-----------------------------------------
								// Kill old and rename new...
								//-----------------------------------------
								
								$real_name = 'photo-'.$row['id'].'.'.$image_lib->file_extension;
								
								if( $real_name != $row['photo_location'] )
								{
									@unlink( $this->ipsclass->vars['upload_dir'] . "/" . $row['photo_location'] );
								}

								@rename( $this->ipsclass->vars['upload_dir'] . "/" . $return['thumb_location'], $this->ipsclass->vars['upload_dir'] . "/" . $real_name );
								@chmod(  $this->ipsclass->vars['upload_dir'] . "/" . $real_name, 0777 );
							}
							
							//-----------------------------------------
							// MINI photo
							//-----------------------------------------
							
							$image_lib->in_type        = 'file';
							$image_lib->out_type       = 'file';
							$image_lib->in_file_dir    = $this->ipsclass->vars['upload_dir'];
							$image_lib->in_file_name   = $row['photo_location'];
							$image_lib->out_file_name  = 'photo-thumb-'.$row['id'];
							$image_lib->desired_width  = 50;
							$image_lib->desired_height = 50;
							
							$return = $image_lib->generate_thumbnail();

							$t_im['img_width']    = $return['thumb_width'];
							$t_im['img_height']   = $return['thumb_height'];
							$t_im['img_location'] = $return['thumb_location'];
						}
						
						$photo = array( 'final_location'   => $real_name ? $real_name : $row['photo_location'],
										'final_width'      => $im['img_width'] ? $im['img_width'] : $_main['img_width'],
										'final_height'     => $im['img_height'] ? $im['img_height'] : $_main['img_height'],
										't_final_location' => $t_im['img_location'] ? $t_im['img_location'] : $row['photo_location'],
										't_final_width'    => $t_im['img_width'] ? $t_im['img_width'] : $_thumb['img_width'],
										't_final_height'   => $t_im['img_height'] ? $t_im['img_height'] : $_thumb['img_height'] );
										
						//-----------------------------------------
						// Save...
						//-----------------------------------------

						if ( $member['pp_member_id'] )
						{
							# Update...
							$this->ipsclass->DB->do_update( 'profile_portal', array( 'pp_main_photo'                => $photo['final_location'],
																  				   	 'pp_main_width'                => $photo['final_width'],
																				   	 'pp_main_height'               => $photo['final_height'],
																					 'pp_thumb_photo'               => $photo['t_final_location'],
																					 'pp_thumb_width'               => $photo['t_final_width'],
																					 'pp_thumb_height'              => $photo['t_final_height'],
																				 ), 'pp_member_id='.$member_id );
						}
						else
						{
							# Insert
							$this->ipsclass->DB->do_insert( 'profile_portal', array( 'pp_main_photo'                => $photo['final_location'],
																  				   	 'pp_main_width'                => $photo['final_width'],
																				   	 'pp_main_height'               => $photo['final_height'],
																					 'pp_thumb_photo'               => $photo['t_final_location'],
																					 'pp_thumb_width'               => $photo['t_final_width'],
																					 'pp_thumb_height'              => $photo['t_final_height'],
																					 'pp_member_id'                 => $member_id,
																				 ) );
						}
					
						$updated++;
					}
				}
			}
		}
		else
		{
			$done = 1;
		}
		
		//-----------------------------------------
		// Done?
		//-----------------------------------------
		
		if ( ! $done )
		{
			$this->ipsclass->main_msg = "<b>会员照片: $start 到 $end 已完成. 本次更新 $updated 项...</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&st='.$end;
		}
		else
		{
			$this->ipsclass->main_msg = "<b>会员照片已更新</b>";

			$url  = "{$this->ipsclass->form_code}&code=tools";
		}

		//-----------------------------------------
		// Bye....
		//-----------------------------------------

		$this->ipsclass->admin->redirect( $url, $this->ipsclass->main_msg, 0, 1 );
	}
	
	
	/*-------------------------------------------------------------------------*/
	// REBUILD THUMBNAILS
	/*-------------------------------------------------------------------------*/
	
	function rebuild_photos()
	{
		require_once( KERNEL_PATH.'class_image.php' );
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'pp_member_id', 'from' => 'profile_portal', 
																'where' => "pp_main_photo != ''", 'order' => 'pp_member_id ASC', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['pp_member_id'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'profile_portal', 'order' => 'pp_member_id ASC', 'where' => "pp_main_photo != ''", 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			if ( $r['pp_thumb_photo'] and ( $r['pp_thumb_photo'] != $r['pp_main_photo'] ) )
			{
				if ( file_exists( $this->ipsclass->vars['upload_dir'].'/'.$r['pp_thumb_photo'] ) )
				{
					if ( ! @unlink( $this->ipsclass->vars['upload_dir'].'/'.$r['pp_thumb_photo'] ) )
					{
						$output[] = "无法删除: ".$r['pp_thumb_photo'];
						continue;
					}
				}
			}
			
			$photo_data           = array();
			$thumb_data            = array();
			
			$image = new class_image();
			
			$image->in_type        = 'file';
			$image->out_type       = 'file';
			$image->in_file_dir    = $this->ipsclass->vars['upload_dir'];
			$image->in_file_name   = $r['pp_main_photo'];
			$image->desired_width  = 50;
			$image->desired_height = 50;
			$image->gd_version     = $this->ipsclass->vars['gd_version'];
	
			$thumb_data = $image->generate_thumbnail();
			
			$photo_data['pp_thumb_width']   = $thumb_data['thumb_width'];
			$photo_data['pp_thumb_height']  = $thumb_data['thumb_height'];
			$photo_data['pp_thumb_photo'] 	= $thumb_data['thumb_location'];
			
			if ( count( $photo_data ) )
			{
				$this->ipsclass->DB->do_update( 'profile_portal', $photo_data, 'pp_member_id='.$r['pp_member_id'] );
				
				$output[] = "调整大小: ".$r['pp_main_photo'];
			}
			
			unset($image);
			
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}	
	
	/*-------------------------------------------------------------------------*/
	// 220: Contacts
	/*-------------------------------------------------------------------------*/
	
	function tools_220_contacts()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$start           = intval($_GET['st']);
		$lend            = 50;
		$end             = $start + $lend;
		$done            = 0;
		$updated         = 0;
		
		//-----------------------------------------
		// Get lib
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/action_public/profile.php' );
		$profile           =  new profile();
		$profile->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------
		// OK..
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
											 	 'from'   => 'contacts',
											 	 'where'  => 'allow_msg=1',
											     'limit'  => array( $start, $lend ) ) );
											
		$o = $this->ipsclass->DB->exec_query();
		
		//-----------------------------------------
		// Do it...
		//-----------------------------------------

		if ( $this->ipsclass->DB->get_num_rows($o) )
		{
			//-----------------------------------------
			// Got some to convert!
			//-----------------------------------------
			
			while( $row = $this->ipsclass->DB->fetch_row( $o ) )
			{
				//-----------------------------------------
				// Already a friend
				//-----------------------------------------
				
				$friend = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																			'from'   => 'profile_friends',
																			'where'  => 'friends_member_id=' . intval( $row['member_id'] ) . ' AND friends_friend_id=' . intval( $row['contact_id'] ) ) );
																			
				if ( ! $friend['friends_id'] )
				{
					//-----------------------------------------
					// Add to DB
					//-----------------------------------------

					$this->ipsclass->DB->do_insert( 'profile_friends', array( 'friends_member_id' => $row['member_id'],
																			  'friends_friend_id' => $row['contact_id'],
																			  'friends_approved'  => 1,
																			  'friends_added'     => time() ) );
																			
					//-----------------------------------------
					// Rebuild...
					//-----------------------------------------
					
					$profile->personal_function_recache_members_friends( array( 'id' => $row['member_id'] ) );
					
					$updated++;
				}
				
				$this->ipsclass->DB->do_delete( "contacts", "id={$row['id']}" );
			}
		}
		else
		{
			$done = 1;
		}
		
		//-----------------------------------------
		// Done?
		//-----------------------------------------
		
		if ( ! $done )
		{
			$this->ipsclass->main_msg = "<b>联系人 $start 到 $end 已完成, 本次更新了 $updated 项...</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&st='.$end;
		}
		else
		{
			$this->ipsclass->main_msg = "<b>联系人已更新</b>";

			$url  = "{$this->ipsclass->form_code}&code=tools";
		}

		//-----------------------------------------
		// Bye....
		//-----------------------------------------

		$this->ipsclass->admin->redirect( $url, $this->ipsclass->main_msg, 0, 1 );
	}
	
	/*-------------------------------------------------------------------------*/
	// 220: Template Bits
	/*-------------------------------------------------------------------------*/
	
	function tools_220_template_bits()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$updated         = 0;
		$set_skin_set_id = intval( $this->ipsclass->input['set_skin_set_id'] );
		$_bits           = array();
		$start           = intval($_GET['st']);
		$lend            = 25;
		$end             = $start + $lend;
		$done            = 0;
		
		//-----------------------------------------
		// Get API class
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/api/api_skins.php' );
		$api           =  new api_skins;
		$api->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------
		// OK..
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
											 	 'from'   => 'skin_templates',
											 	 'where'  => 'set_id='.$set_skin_set_id,
											     'limit'  => array( $start, $lend ) ) );
											
		$o = $this->ipsclass->DB->exec_query();
		
		//-----------------------------------------
		// Do it...
		//-----------------------------------------

		if ( $this->ipsclass->DB->get_num_rows($o) )
		{
			//-----------------------------------------
			// Got some to convert!
			//-----------------------------------------
			
			while( $row = $this->ipsclass->DB->fetch_row( $o ) )
			{
				if ( preg_match( "#ipb\.|<if|<else#", $row['section_content'] ) )
				{
					$section_content = $api->skin_update_template_bit( $row['section_content'] );
				
					if ( $section_content AND $section_content != $row['section_content'] )
					{
						$updated++;
						$this->ipsclass->DB->do_update( 'skin_templates', array( 'section_content' => $section_content ), 'suid=' . $row['suid'] );
					}
				}
			}
		}
		else
		{
			$done = 1;
		}
		
		//-----------------------------------------
		// Done?
		//-----------------------------------------
		
		if ( ! $done )
		{
			$this->ipsclass->main_msg = "<b>模版元素 $start 到 $end 已完成, 本次更新 $updated 项...</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&st='.$end.'&set_skin_set_id='.$set_skin_set_id;
		}
		else
		{
			$this->ipsclass->main_msg = "<b>模版元素已更新</b>";

			$url  = "{$this->ipsclass->form_code}&code=tools";
		}

		//-----------------------------------------
		// Bye....
		//-----------------------------------------

		$this->ipsclass->admin->redirect( $url, $this->ipsclass->main_msg, 0, 1 );
	}
	
	/*-------------------------------------------------------------------------*/
	// 210: TOOLS DUPLICATE SETTINGS
	/*-------------------------------------------------------------------------*/
	
	function tools_210_dupe_settings()
	{
		//-----------------------------------------
		// Remove dupe categories
		//-----------------------------------------
		
		$title_id_to_keep    = array();
		$title_id_to_delete  = array();
		$title_deleted_count = 0;
		$msg                 = '';
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'conf_settings_titles', 'order' => 'conf_title_id DESC' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			if ( $title_id_to_keep[ $r['conf_title_title'] ] )
			{
				$title_id_to_delete[ $r['conf_title_id'] ] = $r['conf_title_id'];
				
				$msg .= "Deleting: {$r['conf_title_title']} ID:{$r['conf_title_id']}<br />";
			}
			else
			{
				$title_id_to_keep[ $r['conf_title_title'] ] = $r['conf_title_id'];
				$msg .= "KEEPING: {$r['conf_title_title']} ID:{$r['conf_title_id']}<br />";
			}
		}
		
		if ( count( $title_id_to_delete ) )
		{
			$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'conf_settings_titles', 'where' => 'conf_title_id IN ('.implode( ',', $title_id_to_delete ).')' ) );
		}
		
		$title_deleted_count = intval( count($title_id_to_delete) );
		
		//-----------------------------------------
		// Time to move on dude
		//-----------------------------------------
		
		$this->ipsclass->main_msg = "$title_deleted_count 项重复设置项目已删除<br />$msg";
		$this->tools_splash();
	}
	
	/*-------------------------------------------------------------------------*/
	// CALENDAR EVENTS
	/*-------------------------------------------------------------------------*/
	
	function tools_210_calevents()
	{
		$start = intval($_GET['st']);
		$lend  = 50;
		$end   = $start + $lend;
		$max   = intval($_GET['max']);
		
		//-----------------------------------------
		// Check to make sure table exists
		//-----------------------------------------
		
		if ( ! $this->ipsclass->DB->table_exists( 'calendar_events' ) )
		{
			$this->ipsclass->main_msg = "由于老的 calendar_events 数据表已删除, 您无法运行本工具";
			$this->tools_splash();
		}
		
		//-----------------------------------------
		// Do we need to run this tool?
		//-----------------------------------------
		
		if ( ! $max )
		{
			$original = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as max', 'from' => 'calendar_events' ) );
			$new      = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as max', 'from' => 'cal_events' ) );
			
			if ( $new['max'] >= $original['max'] OR ! $original['max'] )
			{
				$this->ipsclass->main_msg = "日历事件已转换";
				$this->tools_splash();
			}
		}
		
		$max = intval( $original['max'] );
		
		//-----------------------------------------
		// In steps...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*',
													  'from'   => 'calendar_events',
													  'limit'  => array( $start, $lend ) ) );
		$o = $this->ipsclass->DB->simple_exec();
	
		//-----------------------------------------
		// Do it...
		//-----------------------------------------
		
		if ( $this->ipsclass->DB->get_num_rows($o) )
		{
			//-----------------------------------------
			// Got some to convert!
			//-----------------------------------------
			
			while ( $r = $this->ipsclass->DB->fetch_row($o) )
			{
				$recur_remap = array( 'w' => 1,
									  'm' => 2,
									  'y' => 3 );
				
				$begin_date        = $this->ipsclass->date_getgmdate( $r['unix_stamp']     );
				$end_date          = $this->ipsclass->date_getgmdate( $r['end_unix_stamp'] );
				
				if ( ! $begin_date OR ! $end_date )
				{
					continue;
				}
				
				$day               = $begin_date['mday'];
				$month             = $begin_date['mon'];
				$year              = $begin_date['year'];
				
				$end_day           = $end_date['mday'];
				$end_month         = $end_date['mon'];
				$end_year          = $end_date['year'];
		
				$_final_unix_from  = gmmktime(0, 0, 0, $month, $day, $year );
				
				//-----------------------------------------
				// Recur or ranged...
				//-----------------------------------------
				
				if ( $r['event_repeat'] OR $r['event_ranged'] )
				{
					$_final_unix_to = gmmktime(23, 59, 59, $end_month, $end_day, $end_year);
				}
				else
				{
					$_final_unix_to = 0;
				}
				
				$new_event = array( 'event_calendar_id' => 1,
									'event_member_id'   => $r['userid'],
									'event_content'     => $r['event_text'],
									'event_title'       => $r['title'],
									'event_smilies'     => $r['show_emoticons'],
									'event_perms'       => $r['read_perms'],
									'event_private'     => $r['priv_event'],
									'event_approved'    => 1,
									'event_unixstamp'   => $r['unix_stamp'],
									'event_recurring'   => ( $r['event_repeat'] && $recur_remap[ $r['repeat_unit'] ] ) ? $recur_remap[ $r['repeat_unit'] ] : 0,
									'event_tz'          => 0,
									'event_unix_from'   => $_final_unix_from,
									'event_unix_to'     => $_final_unix_to );
				
				//-----------------------------------------
				// INSERT
				//-----------------------------------------
				
				$this->ipsclass->DB->do_insert( 'cal_events', $new_event );
			}
			
			$this->ipsclass->main_msg = "<b>日历事件 $start 到 $end 已完成....</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&max='.$max.'&st='.$end;
		}
		else
		{
			$this->ipsclass->main_msg = "<b>日历事件已转换</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=tools";
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $this->ipsclass->main_msg, 0, $time );
		
		
	}
	
	/*-------------------------------------------------------------------------*/
	// POLLS
	/*-------------------------------------------------------------------------*/
	
	function tools_210_polls()
	{
		$start     = intval($_GET['st']);
		$lend      = 50;
		$end       = $start + $lend;
		$max       = intval($_GET['max']);
		$done      = 0;
		$converted = intval( $_GET['conv'] );
		
		//-----------------------------------------
		// First off.. grab number of polls to convert
		//-----------------------------------------
		
		if ( ! $max )
		{
			$total = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as max',
																	   'from'   => 'topics',
																	   'where'  => "poll_state IN ('open', 'close', 'closed')" ) );
																	   
			$max   = $total['max'];
		}
		
		if ( $max < 1 )
		{
			$done = 1;
		}
		
		//-----------------------------------------
		// In steps...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*',
													  'from'   => 'topics',
													  'where'  => "poll_state IN ('open', 'close', 'closed' )",
													  'limit'  => array( $start, $lend ) ) );
		$o = $this->ipsclass->DB->simple_exec();
	
		//-----------------------------------------
		// Do it...
		//-----------------------------------------
		
		if ( $this->ipsclass->DB->get_num_rows($o) )
		{
			//-----------------------------------------
			// Got some to convert!
			//-----------------------------------------
			
			while ( $r = $this->ipsclass->DB->fetch_row($o) )
			{
				$converted++;
				
				$new_poll  = array( 1 => array() );
				
				$poll_data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																			   'from'   => 'polls',
																			   'where'  => "tid=".$r['tid']
																	  )      );
				if ( ! $poll_data['pid'] )
				{
					continue;
				}
				
				if ( ! $poll_data['poll_question'] )
				{
					$poll_data['poll_question'] = $r['title'];
				}
				
				//-----------------------------------------
				// Kick start new poll
				//-----------------------------------------
				
				$new_poll[1]['question'] = $poll_data['poll_question'];
        
				//-----------------------------------------
				// Get OLD polls
				//-----------------------------------------
				
				$poll_answers = unserialize( stripslashes( $poll_data['choices'] ) );
        	
				reset($poll_answers);
				
				foreach ( $poll_answers as $entry )
				{
					$id     = $entry[0];
					$choice = $entry[1];
					$votes  = $entry[2];
					
					$total_votes += $votes;
					
					if ( strlen($choice) < 1 )
					{
						continue;
					}
					
					$new_poll[ 1 ]['choice'][ $id ] = $choice;
					$new_poll[ 1 ]['votes'][ $id  ] = $votes;
				}
				
				//-----------------------------------------
				// Got something?
				//-----------------------------------------
				
				if ( count( $new_poll[1]['choice'] ) )
				{
					$this->ipsclass->DB->do_update( 'polls' , array( 'choices'    => serialize( $new_poll ) ), 'tid='.$r['tid'] );
					$this->ipsclass->DB->do_update( 'topics', array( 'poll_state' => 1 ), 'tid='.$r['tid'] );
				}
				
				//-----------------------------------------
				// All done?
				//-----------------------------------------
				
				if ( $converted >= $max )
				{
					$done = 1;
					continue;
				}
			}
		}
		else
		{
			$done = 1;
		}
		
		
		if ( ! $done )
		{
			$this->ipsclass->main_msg = "<b>投票 $start 到 $end 已完成, 共 $max 项....</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&max='.$max.'&st='.$end.'&conv='.$converted;
		}
		else
		{
			$this->ipsclass->main_msg = "<b>投票已转换</b>";
			
			$url  = "{$this->ipsclass->form_code}&code=tools";
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $this->ipsclass->main_msg, 0, 1 );
	}
	
	/*-------------------------------------------------------------------------*/
	// TOOLS BAN SETTINGS
	/*-------------------------------------------------------------------------*/
	
	function tool_bansettings()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$bomb        = array();
		$ban         = array();
		$ip_count    = 0;
		$email_count = 0;
		$name_count  = 0;
		
		//-----------------------------------------
		// Get current entries
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'banfilters', 'order' => 'ban_date desc' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$ban[ $r['ban_type'] ][ $r['ban_content'] ] = $r;
		}
		
		//-----------------------------------------
		// Get $INFO (again) ip email name
		//-----------------------------------------
		
		require( ROOT_PATH."conf_global.php" );
		
		//-----------------------------------------
		// IP
		//-----------------------------------------
		
		if ( $INFO['ban_ip'] )
		{
			$bomb = explode( '|', $INFO['ban_ip'] );
			
			if ( is_array( $bomb ) and count( $bomb ) )
			{
				foreach( $bomb as $bang )
				{
					if ( ! is_array($ban['ip'][ $bang ]) )
					{
						$this->ipsclass->DB->do_insert( 'banfilters', array( 'ban_type' => 'ip', 'ban_content' => $bang, 'ban_date' => time() ) );
						
						$ip_count++;
					}
				}
			}
		}
		
		//-----------------------------------------
		// EMAIL
		//-----------------------------------------
		
		if ( $INFO['ban_email'] )
		{
			$bomb = explode( '|', $INFO['ban_email'] );
			
			if ( is_array( $bomb ) and count( $bomb ) )
			{
				foreach( $bomb as $bang )
				{
					if ( ! is_array($ban['email'][ $bang ]) )
					{
						$this->ipsclass->DB->do_insert( 'banfilters', array( 'ban_type' => 'email', 'ban_content' => $bang, 'ban_date' => time() ) );
						
						$email_count++;
					}
				}
			}
		}
		
		//-----------------------------------------
		// EMAIL
		//-----------------------------------------
		
		if ( $INFO['ban_names'] )
		{
			$bomb = explode( '|', $INFO['ban_names'] );
			
			if ( is_array( $bomb ) and count( $bomb ) )
			{
				foreach( $bomb as $bang )
				{
					if ( ! is_array($ban['name'][ $bang ]) )
					{
						$this->ipsclass->DB->do_insert( 'banfilters', array( 'ban_type' => 'name', 'ban_content' => $bang, 'ban_date' => time() ) );
						
						$name_count++;
					}
				}
			}
		}
		
		$this->ipsclass->main_msg = "导入 $ip_count 个 IP 地址, 导入 $email_count 个邮件地址, 导入 $name_count 个名称.";
		
		require_once( ROOT_PATH."sources/action_admin/banandbadword.php");
		$thing           =  new ad_banandbadword();
		$thing->ipsclass =& $this->ipsclass;
		
		$thing->ban_rebuildcache();
		
		$this->tools_splash();
	}
	
	/*-------------------------------------------------------------------------*/
	// TOOLS (UN)CONVERGE
	/*-------------------------------------------------------------------------*/
	
	function tools_converge()
	{
		//-----------------------------------------
		// Get all validating members...
		//-----------------------------------------
		
		$to_unconverge    = array();
		$unconverge_count = 0;
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, email, mgroup', 'from' => 'members', 'where' => 'mgroup='.$this->ipsclass->vars['auth_group'] ) );
		$this->ipsclass->DB->simple_exec();
		
		while( $m = $this->ipsclass->DB->fetch_row() )
		{
			if ( preg_match( "#^{$m['id']}\-#", $m['email'] ) )
			{
				$to_unconverge[] = $m['id'];
			}
		}
		
		$unconverge_count = intval( count($to_unconverge) );
		
		if ( $unconverge_count )
		{
			foreach( $to_unconverge as $mid )
			{
				$this->ipsclass->DB->do_update( 'members'     , array( 'mgroup' => $this->ipsclass->vars['member_group'] ), 'id='.$mid );
				$this->ipsclass->DB->do_update( 'member_extra', array( 'bio'   => 'dupemail'                       ), 'id='.$mid );
			}
		}
		
		//-----------------------------------------
		// Time to move on dude
		//-----------------------------------------
		
		$this->ipsclass->main_msg = "找到并还原 $unconverge_count 个会员";
		$this->tools_splash();
		
	}
	
	/*-------------------------------------------------------------------------*/
	// TOOLS DUPLICATE SETTINGS
	/*-------------------------------------------------------------------------*/
	
	function tools_dupe_settings()
	{
		//-----------------------------------------
		// Remove dupe categories
		//-----------------------------------------
		
		$title_id_to_keep    = array();
		$title_id_to_delete  = array();
		$title_deleted_count = 0;
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'conf_settings_titles', 'order' => 'conf_title_id' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			if ( $title_id_to_keep[ $r['conf_title_title'] ] )
			{
				$title_id_to_delete[ $r['conf_title_id'] ] = $r['conf_title_id'];
			}
			else
			{
				$title_id_to_keep[ $r['conf_title_title'] ] = $r['conf_title_id'];
			}
		}
		
		if ( count( $title_id_to_delete ) )
		{
			$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'conf_settings_titles', 'where' => 'conf_title_id IN ('.implode( ',', $title_id_to_delete ).')' ) );
		}
		
		$title_deleted_count = intval( count($title_id_to_delete) );
		
		//-----------------------------------------
		// Remove dupe settings
		//-----------------------------------------
		
		$setting_id_to_keep       = array();
		$setting_id_to_delete     = array();
		$setting_id_deleted_count = 0;
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'conf_settings', 'order' => 'conf_id' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			if ( $setting_id_to_keep[ $r['conf_title'].','.$r['conf_key'] ] )
			{
				$setting_id_to_delete[ $r['conf_id'] ] = $r['conf_id'];
			}
			else
			{
				$setting_id_to_keep[ $r['conf_title'].','.$r['conf_key'] ] = $r['conf_id'];
			}
		}
	
		if ( count( $setting_id_to_delete ) )
		{
			$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'conf_settings', 'where' => 'conf_id IN ('.implode( ',', $setting_id_to_delete ).')' ) );
		}
		
		$setting_deleted_count = intval( count($setting_id_to_delete) );
		
		//-----------------------------------------
		// Time to move on dude
		//-----------------------------------------
		
		$this->ipsclass->main_msg = "$title_deleted_count 个重复的设置标题已删除, $setting_deleted_count 个重复的设置已删除";
		$this->tools_splash();
	}
	
	/*-------------------------------------------------------------------------*/
	// TOOLS SPLASH
	/*-------------------------------------------------------------------------*/
	
	function tools_splash()
	{
		$this->ipsclass->admin->nav[]	= array( $this->ipsclass->form_code.'&code=tools', '维护工具' );
		
		//-----------------------------------------
		// Get skin list...
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/action_admin/skintools.php' );
		$ad_skintools           =  new ad_skintools;
		$ad_skintools->ipsclass =& $this->ipsclass;
		
		$skin_list = $ad_skintools->_get_skinlist();
		//$skin_list = str_replace( '<!--DD.OPTIONS-->', '<option value="1">Master IPB Skin</option>', $skin_list );
		
		//-----------------------------------------
		// 2.2.0: START
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( 'IPB 2.1.x -> 2.2.0 升级工具', '这些工具将清理并重建您升级自 IPB 2.1.x 系列的数据库' ) . "<br />";
		
		//-----------------------------------------
		// 220: Personal Photos
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '220tool_photos' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "转换 IPB 2.1.x 的“照片” 到 IPB 2.2.x 的'会员照片'" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "IPB 2.2.0 引入了更强的包含会员照片的会员信息系统.<br />本工具将升级现有的上传照片到新格式."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( '运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 220: Contacts
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '220tool_contacts' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "转换 IPB 2.1.x 的'联系人'到 IPB 2.2.x 的'好友'" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "IPB 2.2.0 引入了'好友'功能, 取代了短消息'联系人'.<br />本工具将现有的联系人转换到好友."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( '运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 220: Template Bits
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '220tool_templatebits' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "转换 IPB 2.1.x 模版 HTML 到 IPB 2.2.x 模版 HTML 格式" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "的模版格式有一些变化, 本工具将更新您存储在数据库中的主主题. 运行本工具后您需要重建缓存.
																			  <br />针对主题: $skin_list 运行本工具"
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( '运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 2.1.0: START
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( 'IPB 2.0.x -> 2.1.0 升级工具', '这些工具将清理并重建您升级自 IPB 2.0.x 系列的数据库' ) . "<br />";
		
		//-----------------------------------------
		// 210: DUPE SETTINGS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '210tool_settings' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除升级到 IPB 2.1.x 后重复的系统设置组" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "从 IPB 2.0.x 升级或从另一个论坛到如数据后, 您会发现由于运行了2次升级工具或者超时错误. 系统设置中将会有一些重复的组."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 210: CALEVENTS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '210calevents' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "将 IPB 2.0.x 的日历事件转换为 2.1.x 格式" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "本工具将 IPB 2.0.x 的日历事件转换为新的格式. 如果您的论坛是手工升级的或者某些日历事件没有转换, 请使用本工具."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 210: POLLS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , '210polls' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "将 IPB 2.0.x 的投票转换到 2.1.x 格式" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "本工具将 IPB 2.0.x 的投票转换为新的格式. 如果您的论坛是手工升级的或者某些投票没有转换, 请使用本工具."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// 2.0.0: START
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( 'IPB 1.x.x -> 2.0.0 升级工具', '这些工具将清理并重建您升级自 IPB 1.x.x 系列的数据库' ) . "<br />";
		
		
		//-----------------------------------------
		// DUPE SETTINGS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'tool_settings' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除升级到 IPB 2.0.x 后重复的系统设置组" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "经过升级或由其他论坛转换而来的论坛, 您会发现, 由于运行了2次升级工具或者超时错误, 系统设置中将会有一些重复的设置.
																			  <br />本工具将找出这些重复的设置项, 并且保留 ID 较大的."
																	)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// STATISTICS (also vali mem)
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'tool_converge' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "查找并恢复“疑似重复”的会员" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "过升级或由其他论坛转换而来的论坛, 您会发现, 由于几个会员使用了重复的邮件地址, 他们的帐户被移动到等待验证用户组.
																  <br />本工具将找出这些会员, 并恢复他们到默认的用户组, 同时提醒他们更改邮件地址."
														)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Import old bandana settings
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'tool_bansettings' ),
												                 2 => array( 'act'   , 'rebuild' ),
												                 4 => array( 'section', $this->ipsclass->section_code ),
									                    )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "{none}"    , "100%" );
	
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "查找并恢复 旧的 IPB 屏蔽设置" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "经过升级或由其他论坛转换而来的论坛, 您会发现, 您的屏蔽设置不见了.<br />R本工具将试图导入那些旧设置.旧的项目不会覆盖新项目."
														)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('运行工具');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Print
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
		
	}
	
	/*-------------------------------------------------------------------------*/
	// Clean out photos
	/*-------------------------------------------------------------------------*/
	
	function clean_photos()
	{
		require_once( KERNEL_PATH.'class_upload.php' );
		
		$upload = new class_upload();
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$display = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Pop open the directory and
		// peek inside...
		//-----------------------------------------
		
		$i = 0;
		
		$dh = opendir( $this->ipsclass->vars['upload_dir'] );
 		
 		while ( false !== ( $file = readdir( $dh ) ) )
 		{
 			if ( strstr( $file, 'photo-' ) )
 			{
 				$fullfile = $this->ipsclass->vars['upload_dir'].'/'.$file;
 			
 				$i++;
 				
 				//-----------------------------------------
 				// Already started?
 				//-----------------------------------------
 				
 				if ( $start > $i )
 				{
 					continue;
 				}
 				
 				//-----------------------------------------
 				// Done for this iteration?
 				//-----------------------------------------
 				
 				if ( $i > $display )
 				{
 					break;
 				}
 				
 				//-----------------------------------------
 				// Try and get attach row
 				//-----------------------------------------
 				
 				$found = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'pp_member_id', 'from' => 'profile_portal', 'where' => "pp_main_photo='{$file}' OR pp_thumb_photo='{$file}'" ) );
 				
 				if ( ! $found['pp_member_id'] )
 				{
 					@unlink( $fullfile );
 					$output[] = "<span style='color:red'>删除了 $file 个孤立文件: $file</span>";
 				}
 				else
 				{
 					$output[] = "<span style='color:gray'>$file 个附件没有问题</span>";
 				}
			}
 		}
 		
 		closedir( $dh );
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( $i < $display)
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 {$display} 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$display;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// Clean out avatars
	/*-------------------------------------------------------------------------*/
	
	function clean_avatars()
	{
		require_once( KERNEL_PATH.'class_upload.php' );
		
		$upload = new class_upload();
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Pop open the directory and
		// peek inside...
		//-----------------------------------------
		
		$i = 0;
		
		$dh = opendir( $this->ipsclass->vars['upload_dir'] );
 		
 		while ( false !== ( $file = readdir( $dh ) ) )
 		{
 			if ( strstr( $file, 'av-' ) )
 			{
 				$fullfile = $this->ipsclass->vars['upload_dir'].'/'.$file;
 			
 				$i++;
 				
 				//-----------------------------------------
 				// Already started?
 				//-----------------------------------------
 				
 				if ( $start > $i )
 				{
 					continue;
 				}
 				
 				//-----------------------------------------
 				// Done for this iteration?
 				//-----------------------------------------
 				
 				if ( $i > $dis )
 				{
 					break;
 				}
 				
 				//-----------------------------------------
 				// Try and get attach row
 				//-----------------------------------------
 				
 				$found = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'id', 'from' => 'member_extra', 'where' => "avatar_location='$file' or avatar_location='upload:$file'" ) );
 				
 				if ( ! $found['id'] )
 				{
 					@unlink( $fullfile );
 					$output[] = "<span style='color:red'>删除 $file 个孤立文件</span>";
 				}
 				else
 				{
 					$output[] = "<span style='color:gray'>$file 个附件没有问题</span>";
 				}
			}
 		}
 		
 		closedir( $dh );
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( $i < $dis)
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>Rebuild completed</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>Up to $dis processed so far, continuing...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// Clean out attachments
	/*-------------------------------------------------------------------------*/
	
	function clean_attachments()
	{
		require_once( KERNEL_PATH.'class_upload.php' );
		
		$upload = new class_upload();
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Pop open the directory and
		// peek inside...
		//-----------------------------------------
		
		$i = 0;
		
		$dh = opendir( $this->ipsclass->vars['upload_dir'] );
 		
 		while ( false !== ( $file = readdir( $dh ) ) )
 		{
			if ( $file == '.' OR $file == '..' )
			{
				continue;
			}
			
	 		$fullfile = $this->ipsclass->vars['upload_dir'].'/'.$file;
	 		
	 		if( is_dir( $fullfile ) )
	 		{
		 		$ndh = opendir( $fullfile );
		 		
		 		while( false !== ( $nfile = readdir( $ndh ) ) )
		 		{
					if ( $nfile == '.' OR $nfile == '..' )
					{
						continue;
					}
					
		 			if ( strstr( $nfile, 'post-' ) )
		 			{
		 				$i++;
		 				
		 				//-----------------------------------------
		 				// Already started?
		 				//-----------------------------------------
		 				
		 				if ( $start > $i )
		 				{
		 					continue;
		 				}
		 				
		 				//-----------------------------------------
		 				// Done for this iteration?
		 				//-----------------------------------------
		 				
		 				if ( $i > $dis )
		 				{
		 					break;
		 				}
		 				
		 				//-----------------------------------------
		 				// Try and get attach row
		 				//-----------------------------------------
		 				
		 				$found = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'attach_id', 'from' => 'attachments', 'where' => "attach_location='{$file}/{$nfile}' OR attach_thumb_location='{$file}/{$nfile}'" ) );
		 				
		 				if ( ! $found['attach_id'] )
		 				{
		 					@unlink( $fullfile . '/' . $nfile );
		 					$output[] = "<span style='color:red'>Removed orphan: $nfile</span>";
		 				}
		 				else
		 				{
		 					$output[] = "<span style='color:gray'>Attached File OK: $nfile</span>";
		 				}
					}
				}
				
				closedir( $ndh );
			}
 			else if ( strstr( $file, 'post-' ) )
 			{
 				$i++;
 				
 				//-----------------------------------------
 				// Already started?
 				//-----------------------------------------
 				
 				if ( $start > $i )
 				{
 					continue;
 				}
 				
 				//-----------------------------------------
 				// Done for this iteration?
 				//-----------------------------------------
 				
 				if ( $i > $dis )
 				{
 					break;
 				}
 				
 				//-----------------------------------------
 				// Try and get attach row
 				//-----------------------------------------
 				
 				$found = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'attach_id', 'from' => 'attachments', 'where' => "attach_location='$file' OR attach_thumb_location='$file'" ) );
 				
 				if ( ! $found['attach_id'] )
 				{
 					@unlink( $fullfile );
 					$output[] = "<span style='color:red'>Removed orphan: $file</span>";
 				}
 				else
 				{
 					$output[] = "<span style='color:gray'>Attached File OK: $file</span>";
 				}
			}
 		}
 		
 		closedir( $dh );
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( $i < $dis)
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>Rebuild completed</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// REBUILD ATTACH DATA
	/*-------------------------------------------------------------------------*/
	
	function rebuild_attachdata()
	{
		require_once( KERNEL_PATH.'class_upload.php' );
		
		$upload = new class_upload();
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'attach_id', 'from' => 'attachments', 'limit' => array($dis,1) ) );
		$max = intval( $tmp['attach_id'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'attachments', 'order' => 'attach_id ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			//-----------------------------------------
			// Get ext
			//-----------------------------------------
			
			$update = array();
			
			$update['attach_ext'] = $upload->_get_file_extension( $r['attach_file'] );
			
			if ( $r['attach_location'] )
			{
				if ( file_exists( $this->ipsclass->vars['upload_dir'].'/'.$r['attach_location'] ) )
				{
					$update['attach_filesize'] = @filesize( $this->ipsclass->vars['upload_dir'].'/'.$r['attach_location'] );
					
					if( $r['attach_is_image'] )
					{
						$dims = @getimagesize( $this->ipsclass->vars['upload_dir'].'/'.$r['attach_location'] );
						
						if( $dims[0] AND $dims[1] )
						{
							$update['attach_img_width'] = $dims[0];
							$update['attach_img_height'] = $dims[1];
						}
					}
				}
			}
			
			if ( count( $update ) )
			{
				$this->ipsclass->DB->do_update( 'attachments', $update, 'attach_id='.$r['attach_id'] );
			}
			
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// REBUILD THUMBNAILS
	/*-------------------------------------------------------------------------*/
	
	function rebuild_thumbnails()
	{
		require_once( KERNEL_PATH.'class_image.php' );
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'attach_id', 'from' => 'attachments', 'where' => "attach_rel_module IN('post','msg')", 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['attach_id'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'attachments', 'where' => "attach_rel_module IN('post','msg')", 'order' => 'attach_id ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			if ( $r['attach_is_image'] )
			{
				if ( $r['attach_thumb_location'] and ( $r['attach_thumb_location'] != $r['attach_location'] ) )
				{
					if ( file_exists( $this->ipsclass->vars['upload_dir'].'/'.$r['attach_thumb_location'] ) )
					{
						if ( ! @unlink( $this->ipsclass->vars['upload_dir'].'/'.$r['attach_thumb_location'] ) )
						{
							$output[] = "Could not remove: ".$r['attach_thumb_location'];
							continue;
						}
					}
				}
				
				$attach_data           = array();
				$thumb_data            = array();
				
				$image = new class_image();
				
				$image->in_type        = 'file';
				$image->out_type       = 'file';
				$image->in_file_dir    = $this->ipsclass->vars['upload_dir'];
				$image->in_file_name   = $r['attach_location'];
				$image->desired_width  = $this->ipsclass->vars['siu_width'];
				$image->desired_height = $this->ipsclass->vars['siu_height'];
				$image->gd_version     = $this->ipsclass->vars['gd_version'];
		
				$thumb_data = $image->generate_thumbnail();
				
				$attach_data['attach_thumb_width']    = $thumb_data['thumb_width'];
				$attach_data['attach_thumb_height']   = $thumb_data['thumb_height'];
				$attach_data['attach_thumb_location'] = $thumb_data['thumb_location'];
				
				if ( count( $attach_data ) )
				{
					$this->ipsclass->DB->do_update( 'attachments', $attach_data, 'attach_id='.$r['attach_id'] );
					
					$output[] = "调整大小: ".$r['attach_location'];
				}
				
				unset($image);
			}
			
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// REBUILD POST COUNTS
	/*-------------------------------------------------------------------------*/
	
	function rebuild_post_counts()
	{
		//-----------------------------------------
		// Forums not to count?
		//-----------------------------------------
		
		$forums = array();
		
		foreach( $this->ipsclass->cache['forum_cache'] as $data )
		{
			if ( ! $data['inc_postcount'] )
			{
				$forums[] = $data['id'];
			}
		}
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis   = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'id', 'from' => 'members', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['id'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, name', 'from' => 'members', 'order' => 'id ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			if ( ! count( $forums ) )
			{
				$count = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as count', 'from' => 'posts', 'where' => 'queued != 1 AND author_id='.$r['id'] ) );
			}
			else
			{
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'count(p.pid) as count',
														 'from'		=> array( 'posts' => 'p' ),
														 'where'	=> 'p.queued <> 1 AND p.author_id='.$r['id'].' AND t.forum_id NOT IN ('.implode(",",$forums).')',
														 'add_join'	=> array( 1 => array( 'type'	=> 'left',
														 								  'from'	=> array( 'topics' => 't' ),
														 								  'where'	=> 't.tid=p.topic_id'
														 					)			)
												)		);
				$this->ipsclass->DB->exec_query();
								
				$count = $this->ipsclass->DB->fetch_row();
			}
			
			$new_post_count = intval( $count['count'] );
			
			$this->ipsclass->DB->do_update( 'members', array( 'posts' => $new_post_count ), 'id='.$r['id'] );
			
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// REBUILD POSTS
	/*-------------------------------------------------------------------------*/
	
	function rebuild_post_names()
	{
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'id', 'from' => 'members', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['id'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, members_display_name', 'from' => 'members', 'order' => 'id ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			$this->ipsclass->DB->do_update( 'contacts'      , array( 'contact_name' => $r['members_display_name'] ), "contact_id="    .$r['id'] );
			$this->ipsclass->DB->do_update( 'topics'        , array( 'starter_name' => $r['members_display_name'] ), "starter_id="    .$r['id'] );
		
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// REBUILD POSTS
	/*-------------------------------------------------------------------------*/
	
	function rebuild_posts()
	{
		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
        $parser                      =  new parse_bbcode();
        $parser->ipsclass            =& $this->ipsclass;
        $parser->allow_update_caches = 1;
      
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$last	= 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = intval($this->ipsclass->input['dis']) >=0 ? intval($this->ipsclass->input['dis']) : 0;
		$output = array();
		
		$types	= array( 'posts', 'pms', 'cal', 'announce', 'sigs' );
		
		$type	= in_array( $this->ipsclass->input['type'], $types ) ? $this->ipsclass->input['type'] : 'posts';
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		switch( $type )
		{
			case 'cal':
				$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'event_id', 'from' => 'cal_events', 'limit' => array($dis,1)  ) );
				$max = intval( $tmp['event_id'] );
			break;

			case 'announce':
				$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'announce_id', 'from' => 'announcements', 'limit' => array($dis,1)  ) );
				$max = intval( $tmp['announce_id'] );
			break;

			case 'pms':
				$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'msg_id', 'from' => 'message_text', 'limit' => array($dis,1)  ) );
				$max = intval( $tmp['msg_id'] );
			break;
			
			case 'sigs':
				$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'id', 'from' => 'member_extra', 'where' => "signature != ''", 'limit' => array($dis,1)  ) );
				$max = intval( $tmp['id'] );
			break;
			
			case 'posts':
			default:
				$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'pid', 'from' => 'posts', 'limit' => array($dis,1)  ) );
				$max = intval( $tmp['pid'] );
			break;
		}
		//print $dis . ' ' . $start . ' ' . $end . ' ' . $max;exit;
		$this->ipsclass->load_skin();
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		switch( $type )
		{
			case 'cal':
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'e.*', 
														 'from' 	=> array( 'cal_events' => 'e' ),
														 'order' 	=> 'e.event_id ASC',
														 'where'	=> 'e.event_id > ' . $start,
														 'limit' 	=> array($end),
														 'add_join'	=> array( 	1 => array( 'type'		=> 'left',
														  									'select'	=> 'm.mgroup',
														  								  	'from'		=> array( 'members' => 'm' ), 
														  								  	'where' 	=> "m.id=e.event_member_id"
														  						)	)  
												) 		);
			break;

			case 'announce':
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'a.*', 
														 'from' 	=> array( 'announcements' => 'a' ),
														 'order' 	=> 'a.announce_id ASC',
														 'where'	=> 'a.announce_id > ' . $start,
														 'limit' 	=> array($end),
														 'add_join'	=> array( 	1 => array( 'type'		=> 'left',
														  									'select'	=> 'm.mgroup',
														  								  	'from'		=> array( 'members' => 'm' ), 
														  								  	'where' 	=> "m.id=a.announce_member_id"
														  						)	)  
												) 		);
			break;

			case 'pms':
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'p.*', 
														 'from' 	=> array( 'message_text' => 'p' ),
														 'order' 	=> 'p.msg_id ASC',
														 'where'	=> 'p.msg_id > ' . $start,
														 'limit' 	=> array($end),
														 'add_join'	=> array( 	1 => array( 'type'		=> 'left',
														  									'select'	=> 'm.mgroup',
														  								  	'from'		=> array( 'members' => 'm' ), 
														  								  	'where' 	=> "m.id=p.msg_author_id"
														  						)	)  
												) 		);
			break;
			
			case 'sigs':
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'me.signature, me.id', 
														 'from' 	=> array( 'member_extra' => 'me' ),
														 'order' 	=> 'me.id ASC',
														 'where'	=> "me.signature != '' AND me.id > " . $start,
														 'limit' 	=> array($end),
														 'add_join'	=> array( 	1 => array( 'type'		=> 'left',
														  									'select'	=> 'm.mgroup',
														  								  	'from'		=> array( 'members' => 'm' ), 
														  								  	'where' 	=> "m.id=me.id"
														  						)	)  
												) 		);
			break;
			
			case 'posts':
			default:
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'p.*', 
														 'from' 	=> array( 'posts' => 'p' ),
														 'order' 	=> 'p.pid ASC',
														 'where'	=> 'p.pid > ' . $start,
														 'limit' 	=> array($end),
														 'add_join'	=> array( 	1 => array( 'type'		=> 'left',
														 									'select'	=> 't.forum_id',
														  								  	'from'		=> array( 'topics' => 't' ), 
														  								  	'where' 	=> "t.tid=p.topic_id"
														  						),
														  						2 => array( 'type'		=> 'left',
														  									'select'	=> 'm.mgroup',
														  								  	'from'		=> array( 'members' => 'm' ), 
														  								  	'where' 	=> "m.id=p.author_id"
														  						)	)  
												) 		);
			break;
		}

		$outer = $this->ipsclass->DB->exec_query();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			$parser->quote_open   = 0;
			$parser->quote_closed = 0;
			$parser->quote_error  = 0;
			$parser->error        = '';
			$parser->image_count  = 0;
			
			$this->ipsclass->member['g_bypass_badwords'] = $this->ipsclass->cache['group_cache'][ $r['mgroup'] ]['g_bypass_badwords'];
			
			switch( $type )
			{
				case 'cal':
					$parser->parse_smilies = $r['event_smilies'];
					$parser->parse_html    = 0;
					$parser->parse_bbcode  = 1;
					
					$rawpost = $parser->pre_edit_parse( $r['event_content'] );
				break;
	
				case 'announce':
					$parser->parse_smilies = 1;
					$parser->parse_html    = $r['announce_html_enabled'];
					$parser->parse_nl2br   = $r['announce_nlbr_enabled'];
					$parser->parse_bbcode  = 1;
					
					$rawpost = $parser->pre_edit_parse( $r['announce_post'] );
				break;
	
				case 'pms':
					$parser->parse_smilies = 1;
					$parser->parse_nl2br   = 1;
					$parser->parse_html    = $this->ipsclass->vars['msg_allow_html'];
					$parser->parse_bbcode  = $this->ipsclass->vars['msg_allow_code'];
					
					$rawpost = $parser->pre_edit_parse( $r['msg_post'] );
				break;
				
				case 'sigs':
					$parser->parse_smilies 		= 0;
					$parser->parsing_signature 	= 1;
					$parser->parse_html    		= $this->ipsclass->vars['sig_allow_html'];
					$parser->parse_bbcode  		= $this->ipsclass->vars['sig_allow_ibc'];
					
					$rawpost = $parser->pre_edit_parse( $r['signature'] );
				break;
				
				case 'posts':
				default:
					$parser->parse_smilies = $r['use_emo'];
					$parser->parse_html    = ( $this->ipsclass->cache['forum_cache'][ $r['forum_id'] ]['use_html'] AND
												$this->ipsclass->cache['group_cache'][ $r['mgroup'] ]['g_dohtml'] AND
												$r['post_htmlstate'] > 0 ) ? 1 : 0;
					$parser->parse_nl2br   = ( $r['post_htmlstate'] != 1 ) ? 1 : 0;
					$parser->parse_bbcode  = $this->ipsclass->cache['forum_cache'][ $r['forum_id'] ]['use_ibc'];
					
					$rawpost = $parser->pre_edit_parse( $r['post'] );
				break;
			}
			
			$newpost = $parser->pre_db_parse( $rawpost );
			
			//-----------------------------------------
			// Remove old \' escaping
			//-----------------------------------------
			
			$newpost = str_replace( "\\'", "'", $newpost );
			
			//-----------------------------------------
			// Convert old dohtml?
			//-----------------------------------------
			
			$htmlstate = 0;
			
			if ( strstr( strtolower($newpost), '[dohtml]' ) )
			{
				//-----------------------------------------
				// Can we use HTML?
				//-----------------------------------------
				
				if ( $type == 'posts' AND $this->ipsclass->cache['forum_cache'][ $r['forum_id'] ]['use_html'] )
				{
					$htmlstate = 2;
				}
				
				$newpost = preg_replace( "#\[dohtml\]#i" , "", $newpost );
				$newpost = preg_replace( "#\[/dohtml\]#i", "", $newpost );
			}
			else
			{
				$htmlstate = intval( $r['post_htmlstate'] );
			}
			
			//-----------------------------------------
			// Convert old attachment tags
			//-----------------------------------------
			
			$newpost = preg_replace( "#\[attachmentid=(\d+?)\]#is", "[attachment=\\1:attachment]", $newpost );
			
			$newpost = $parser->pre_display_parse( $newpost );
			
			if ( $newpost OR $type == 'sigs' )
			{
				switch( $type )
				{
					case 'posts':
						$this->ipsclass->DB->do_update( 'posts', array( 'post' => $newpost, 'post_htmlstate' => $htmlstate ), 'pid='.$r['pid'] );
						$last = $r['pid'];
					break;
					
					case 'pms':
						$this->ipsclass->DB->do_update( 'message_text', array( 'msg_post' => $newpost ), 'msg_id='.$r['msg_id'] );
						$last = $r['msg_id'];
					break;
					
					case 'sigs':
						$this->ipsclass->DB->do_update( 'member_extra', array( 'signature' => $newpost ), 'id='.$r['id'] );
						$last = $r['id'];
					break;
					
					case 'cal':
						$this->ipsclass->DB->do_update( 'cal_events', array( 'event_content' => $newpost ), 'event_id='.$r['event_id'] );
						$last = $r['event_id'];
					break;
					
					case 'announce':
						$this->ipsclass->DB->do_update( 'announcements', array( 'announce_post' => $newpost ), 'announce_id='.$r['announce_id'] );
						$last = $r['announce_id'];
					break;
				}					
			}
			
			$done++;
		}

		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$dis  = $dis + $done;
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&type='.$type.'&pergo='.$this->ipsclass->input['pergo'].'&st='.$last.'&dis='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// RESYNCHRONIZE TOPICS
	/*-------------------------------------------------------------------------*/
	
	function resync_topics()
	{
		require_once( ROOT_PATH.'sources/lib/func_mod.php' );
		$modfunc = new func_mod();
		$modfunc->ipsclass =& $this->ipsclass;
		
		$this->ipsclass->load_language( 'lang_global' );
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as count', 'from' => 'topics', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['count'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'topics', 'order' => 'tid ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			$modfunc->rebuild_topic($r['tid'], 0);
			
			if ( $this->ipsclass->input['pergo'] <= 200 )
			{
				$output[] = "处理论坛版块 ".$r['title'];
			}
			
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// RESYNCHRONIZE FORUMS
	/*-------------------------------------------------------------------------*/
	
	function resync_forums()
	{
		require_once( ROOT_PATH.'sources/lib/func_mod.php' );
		$modfunc = new func_mod();
		$modfunc->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$done   = 0;
		$start  = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		$end    = intval( $this->ipsclass->input['pergo'] ) ? intval( $this->ipsclass->input['pergo'] ) : 100;
		$dis    = $end + $start;
		$output = array();
		
		//-----------------------------------------
		// Got any more?
		//-----------------------------------------
		
		$tmp = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as count', 'from' => 'forums', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['count'] );
		
		//-----------------------------------------
		// Avoid limit...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'forums', 'order' => 'id ASC', 'limit' => array($start,$end) ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		//-----------------------------------------
		// Process...
		//-----------------------------------------
		
		while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			$modfunc->forum_recount( $r['id'] );
			$output[] = "处理论坛版块 ".$r['name'];
			$done++;
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		
		if ( ! $done and ! $max )
		{
		 	//-----------------------------------------
			// Done..
			//-----------------------------------------
			
			$text = "<b>重建完成</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			
			$text = "<b>目前处理完 $dis 项数据, 继续处理...</b><br />".implode( "<br />", $output );
			$url  = "{$this->ipsclass->form_code}&code=".$this->ipsclass->input['code'].'&pergo='.$this->ipsclass->input['pergo'].'&st='.$dis;
			$time = 0;
		}
		
		//-----------------------------------------
		// Bye....
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( $url, $text, 0, $time );
	}
	
	/*-------------------------------------------------------------------------*/
	// DO COUNT - Count the stats
	/*-------------------------------------------------------------------------*/
	
	function docount()
	{
		if ( (! $this->ipsclass->input['posts']) and (! $this->ipsclass->input['online']) and (! $this->ipsclass->input['members'] ) and (! $this->ipsclass->input['lastreg'] ) )
		{
			$this->ipsclass->admin->error("没有需要重新统计的数据!");
		}
		
		$stats = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'cache_store', 'where' => "cs_key='stats'" ) );
		
		$stats = unserialize($this->ipsclass->txt_stripslashes($stats['cs_value']));
		
		if ($this->ipsclass->input['posts'])
		{
			$topics = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'COUNT(*) as tcount',
																 	 'from'   => 'topics',
												 				 	 'where'  => 'approved=1' ) );
		
			$posts  = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'SUM(posts) as replies',
																	 'from'   => 'topics',
																	 'where'  => 'approved=1' ) );
																	 
			$stats['total_topics']  = $topics['tcount'];
			$stats['total_replies'] = $posts['replies'];
		}
		
		if ($this->ipsclass->input['members'])
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'count(id) as members', 'from' => 'members', 'where' => "mgroup <> '".$this->ipsclass->vars['auth_group']."'" ) );
			$this->ipsclass->DB->simple_exec();
			
			$r = $this->ipsclass->DB->fetch_row();
			$stats['mem_count'] = intval($r['members']);
		}
		
		if ($this->ipsclass->input['lastreg'])
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'id, name, members_display_name',
										  'from'   => 'members',
										  'where'  => "mgroup <> '".$this->ipsclass->vars['auth_group']."'",
										  'order'  => "id DESC",
										  'limit'  => array(0,1) ) );
			$this->ipsclass->DB->simple_exec();
			
			$r = $this->ipsclass->DB->fetch_row();
			$stats['last_mem_name'] = $r['members_display_name'] ? $r['members_display_name'] : $r['name'];
			$stats['last_mem_id']   = $r['id'];
		}
		
		if ($this->ipsclass->input['online'])
		{
			$stats['most_date'] = time();
			$stats['most_count'] = 1;
		}
		
		if ( count($stats) > 0 )
		{
			$this->ipsclass->cache['stats'] =& $stats;
			$this->ipsclass->update_cache( array( 'name' => 'stats', 'array' => 1, 'deletefirst' => 1 ) );
		}
		else
		{
			$this->ipsclass->admin->error("没有需要重新统计的数据!");
		}
		
		$this->ipsclass->main_msg = '重新统计已完成';
		
		$this->ipsclass->admin->done_screen("重新统计已完成", "数据重新统计", "{$this->ipsclass->form_code}", 'redirect' );
		
	}
	
	/*-------------------------------------------------------------------------*/
	// MAIN PAGE
	/*-------------------------------------------------------------------------*/
	
	function rebuild_start()
	{
		$this->ipsclass->admin->page_detail = "请选择要重新统计的数据.";
		$this->ipsclass->admin->page_title  = "数据重新统计";
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code, '数据重新统计' );
		
		//-----------------------------------------
		// STATISTICS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'docount' ),
												                 			 2 => array( 'act'   , 'rebuild' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "统计"    , "70%" );
		$this->ipsclass->adskin->td_header[] = array( "选项"       , "30%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重新统计数据" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "重新统计主题数和帖子数",
																 $this->ipsclass->adskin->form_dropdown( 'posts', array( 0 => array( 1, '是'  ), 1 => array( 0, '否' ) ) )
														)      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "重新统计会员数",
												  $this->ipsclass->adskin->form_dropdown( 'members', array( 0 => array( 1, '是'  ), 1 => array( 0, '否' ) ) )
										 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "重设最后注册的会员",
												  $this->ipsclass->adskin->form_dropdown( 'lastreg', array( 0 => array( 1, '是'  ), 1 => array( 0, '否' ) ) )
										 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "重设“在线峰值”统计?",
												  $this->ipsclass->adskin->form_dropdown( 'online', array( 0 => array( 0, '否'  ), 1 => array( 1, '是' ) ) )
										 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重新统计上述数据');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Resynchronise Forums
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'doresyncforums' ),
																 			 2 => array( 'act'   , 'rebuild' ),
																 			 4 => array( 'section', $this->ipsclass->section_code ),
																	 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "同步版块" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>同步版块</b><div style='color:gray'>本操作将重新统计所有论坛板块的主题, 帖子, 和版块最后发表人.</div>",
												  		        "每次循环同步&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '50', 5 ). "&nbsp;个版块"
										 			  )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('同步版块');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Resynchronise Forums
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'doresynctopics' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "同步主题" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>同步主题</b><div style='color:gray'>本操作将重新统计所有主题的回复, 附件, 主题作者和最后回复人.</div>",
												  		         "每次循环同步&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '500', 5 ). "&nbsp;个主题"
										 			  )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('同步主题');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Resynchronise Posts
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'doposts' ),
												                			 2 => array( 'act'   , 'rebuild' ),
												                			 4 => array( 'section', $this->ipsclass->section_code ),
									                  			  )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建帖子内容" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建帖子内容</b><div style='color:gray'>本操作将重建所有帖子内容, 包括 BBCode 和表情. 如果您更改了大量的表情或者表情路径, 本操作很有效.</div>",
												  		       $this->ipsclass->adskin->form_dropdown( 'type', array( 
												  		       														array( 'posts'		, '帖子内容' ),
												  		       														array( 'pms'		, '个人短消息' ),
												  		       														array( 'cal'		, '日历事件' ),
												  		       														array( 'announce'	, '论坛通知' ),
												  		       														array( 'sigs'		, '个人签名' ),
												  		       										) 				) . 
												  		       	"&nbsp;&nbsp;" . 
												  		       	"每次循环重建&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '500', 5 ). "&nbsp;个帖子"
										 			  )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重建帖子内容');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Resynchronise User Names
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'dopostnames' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建会员名" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建会员名</b><div style='color:gray'>本操作将重置保存在帖子, 主题, 操作记录内的会员名. 如果您刚刚手工修改会员的会员名, 本操作很有效.</div>",
												  		         $this->ipsclass->adskin->form_simple_input( 'pergo', '500', 5 ). "&nbsp;每次循环"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重建会员名');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Resynchronise User Post Counts
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'dopostcounts' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                   			  )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重计会员帖子数" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重计会员帖子数</b><div style='color:gray'>本操作将根据当前数据库内的数据重新统计会员的帖子数. 由于不会统计被删除和清理的帖子, 本操作将会减少他们的帖子数. 如果您希望保留会员目前的帖子数, 那么请不要使用本工具.</div>本操作无法恢复!",
												  		         $this->ipsclass->adskin->form_simple_input( 'pergo', '500', 5 ). "&nbsp;每次循环"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重计会员帖子数');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		//-----------------------------------------
		// Rebuild user photo thumbnails
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'dophotos' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建个人照片缩略图" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建个人照片缩略图</b><div style='color:gray'>本操作将重建您会员照片的缩略图的到当前设置的大小. 如果您刚刚调整了缩略图大小, 需要更新的话, 请执行本操作</div>本操作较为占用资源.",
												  		         $this->ipsclass->adskin->form_simple_input( 'pergo', '20', 5 ). "&nbsp;每次循环"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重建个人照片缩略图');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();		
		
		
		//-----------------------------------------
		// Rebuild thumbnails
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'dothumbnails' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建附件缩略图" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建附件缩略图</b><div style='color:gray'>本操作将根据当前设置重建所有图像附件的缩略图. 如果您刚刚修改了缩略图大小, 需要更新的话, 请执行本操作</div>本操作较为占用资源.",
												  		         $this->ipsclass->adskin->form_simple_input( 'pergo', '20', 5 ). "&nbsp;每次循环"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重建附件缩略图');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Rebuild attachment data
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'doattachdata' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建附件数据" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建附件数据</b><div style='color:gray'>本操作将重建所有附件数据, 比如文件大小, 位置和文件扩展. </div>本操作较为占用资源.",
												  		         "每次循环检查&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '50', 5 ). "&nbsp;个附件"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('重建附件数据');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Clean up attachments
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array(  1 => array( 'code'  , 'cleanattachments' ),
												             				  2 => array( 'act'   , 'rebuild' ),
												             				  4 => array( 'section', $this->ipsclass->section_code ),
									                   			  )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除孤立的附件" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>删除孤立的附件</b><div style='color:gray'>本操作将将找出所有以“post-”开头, 并且没有帖子使用的头像文件, 然后删除它们. </div>本操作较为占用资源.",
												  		      			    $this->ipsclass->adskin->form_simple_input( 'pergo', '50', 5 ). "&nbsp;每次循环"
										 		   	    			 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('删除孤立的附件');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Clean up uploaded avatars
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'cleanavatars' ),
																			 2 => array( 'act'   , 'rebuild' ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除孤立头像" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>删除孤立头像</b><div style='color:gray'>操作将将找出所有以 'av-' 开头, 并且没有会员使用的头像文件, 然后删除它们.</div>本操作较为占用资源.",
												  		         "每次循环检查&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '50', 5 ). "&nbsp;个文件"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('删除孤立头像');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Clean up uploaded photos
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'cleanphotos' ),
												             	 			 2 => array( 'act'   , 'rebuild' ),
												             	 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除孤立照片" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>删除孤立照片</b><div style='color:gray'>本操作将将找出所有以 'photo-' 开头, 并且没有会员使用的照片文件, 然后删除它们.</div>本操作较为占用资源.",
												  		         "每次循环检查&nbsp;".$this->ipsclass->adskin->form_simple_input( 'pergo', '50', 5 ). "&nbsp;个文件"
										 		   	    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('删除孤立照片');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-------------------------------//
		
		$this->ipsclass->admin->output();
	
	}
	
	
	
	
}


?>