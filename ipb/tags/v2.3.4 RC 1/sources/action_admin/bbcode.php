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
|   > $Date: 2007-04-24 17:35:04 -0400 (Tue, 24 Apr 2007) $
|   > $Revision: 952 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Administration Module
|   > Module written by Matt Mecham
|   > Date started: 27th January 2004
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Mon 24th May 2004
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_bbcode {

	var $functions = "";
	var $ipsclass;
	var $html;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "content";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "bbcode";
	
	function auto_run()
	{
		$this->html = $this->ipsclass->acp_load_template('cp_skin_bbcode_badword');
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code, 'BBCode Manager' );

		//-----------------------------------------
		// Require and RUN !! THERES A BOMB
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "您可以在这里创建和管理您自己的 BBCodes.";
		$this->ipsclass->admin->page_title  = "自定义 BBCode 管理";

		//-----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
			case 'bbcode':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->bbcode_start();
			    break;
			    
			case 'bbcode_add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':add' );
				$this->bbcode_form('add');
				break;
				
			case 'bbcode_doadd':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':add' );
				$this->bbcode_save('add');
				break;
				
			case 'bbcode_edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->bbcode_form('edit');
				break;
				
			case 'bbcode_doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->bbcode_save('edit');
				break;
				
			case 'bbcode_test':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->bbcode_test();
				break;
				
			case 'bbcode_delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':remove' );
				$this->bbcode_delete();
				break;
				
			case 'bbcode_export':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':export' );
				$this->bbcode_export();
				break;
				
			case 'bbcode_import':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':import' );
				$this->bbcode_import();
				break;
				
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->bbcode_start();
				break;
		}
	}
	
 	//-----------------------------------------
	// BBCODE: Import
	//-----------------------------------------
	
	function bbcode_import()
	{
		$content = $this->ipsclass->admin->import_xml( 'bbcode.xml' );
		
		//-----------------------------------------
		// Got anything?
		//-----------------------------------------
		
		if ( ! $content )
		{
			$this->ipsclass->main_msg = "上传失败, ipb_bbcode.xml 文件不存在或者文件为空";
			$this->bbcode_start();
		}
		
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Unpack the datafile
		//-----------------------------------------
		
		$xml->xml_parse_document( $content );
		
		//-----------------------------------------
		// Get current custom bbcodes
		//-----------------------------------------
		
		$tags = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'custom_bbcode' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$tags[ $r['bbcode_tag'] ] = 1;
		}
		
		//-----------------------------------------
		// pArse
		//-----------------------------------------
		
		if ( ! is_array( $xml->xml_array['bbcodeexport']['bbcodegroup']['bbcode'][0]  ) )
		{
			$xml->xml_array['bbcodeexport']['bbcodegroup']['bbcode'][0] = $xml->xml_array['bbcodeexport']['bbcodegroup']['bbcode'];
		}
		
		foreach( $xml->xml_array['bbcodeexport']['bbcodegroup']['bbcode'] as $entry )
		{
			$bbcode_title             = $entry['bbcode_title']['VALUE'];
			$bbcode_desc              = $entry['bbcode_desc']['VALUE'];
			$bbcode_tag               = $entry['bbcode_tag']['VALUE'];
			$bbcode_replace           = $entry['bbcode_replace']['VALUE'];
			$bbcode_useoption         = $entry['bbcode_useoption']['VALUE'];
			$bbcode_example           = $entry['bbcode_example']['VALUE'];
			$bbcode_switch_option     = $entry['bbcode_switch_option']['VALUE'];
			$bbcode_add_into_menu     = $entry['bbcode_add_into_menu']['VALUE'];
			$bbcode_menu_option_text  = $entry['bbcode_menu_option_text']['VALUE'];
			$bbcode_menu_content_text = $entry['bbcode_menu_content_text']['VALUE'];
			
			if ( $tags[ $bbcode_tag ] )
			{
				$bbarray = array(
								 'bbcode_title'             => $bbcode_title,
								 'bbcode_desc'              => $bbcode_desc,
								 'bbcode_tag'               => $bbcode_tag,
								 'bbcode_replace'           => $this->ipsclass->txt_safeslashes($bbcode_replace),
								 'bbcode_useoption'         => $bbcode_useoption,
								 'bbcode_example'           => $bbcode_example,
								 'bbcode_switch_option'     => $bbcode_switch_option,
								 'bbcode_add_into_menu'     => $bbcode_add_into_menu,
								 'bbcode_menu_option_text'  => $bbcode_menu_option_text,
								 'bbcode_menu_content_text' => $bbcode_menu_content_text,
								);
								
				$this->ipsclass->DB->do_update( 'custom_bbcode', $bbarray, "bbcode_tag='".$bbcode_tag."'" );
				
				continue;
			}
			
			if ( $bbcode_tag )
			{
				$bbarray = array(
								 'bbcode_title'             => $bbcode_title,
								 'bbcode_desc'              => $bbcode_desc,
								 'bbcode_tag'               => $bbcode_tag,
								 'bbcode_replace'           => $this->ipsclass->txt_safeslashes($bbcode_replace),
								 'bbcode_useoption'         => $bbcode_useoption,
								 'bbcode_example'           => $bbcode_example,
								 'bbcode_switch_option'     => $bbcode_switch_option,
								 'bbcode_add_into_menu'     => $bbcode_add_into_menu,
								 'bbcode_menu_option_text'  => $bbcode_menu_option_text,
								 'bbcode_menu_content_text' => $bbcode_menu_content_text,
								);
								
				$this->ipsclass->DB->do_insert( 'custom_bbcode', $bbarray );
			}
		}
		
		$this->bbcode_rebuildcache();
                    
		$this->ipsclass->main_msg = "BBCode XML 文件导入完成";
		
		$this->bbcode_start();
	
	}
		
	//-----------------------------------------
	// BBCODE: Export
	//-----------------------------------------
	
	function bbcode_export()
	{
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Start...
		//-----------------------------------------
		
		$xml->xml_set_root( 'bbcodeexport', array( 'exported' => time() ) );
		
		//-----------------------------------------
		// Get emo group
		//-----------------------------------------
		
		$xml->xml_add_group( 'bbcodegroup' );
		
		$select = array( 'select' => '*', 'from' => 'custom_bbcode' );
		
		if( $this->ipsclass->input['id'] )
		{
			$select['where'] = 'bbcode_id=' . intval($this->ipsclass->input['id']);
		}
		
		$this->ipsclass->DB->simple_construct( $select );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$content = array();
			
			foreach ( $r as $k => $v )
			{
				$content[] = $xml->xml_build_simple_tag( $k, $v );
			}
			
			$entry[] = $xml->xml_build_entry( 'bbcode', $content );
		}
		
		$xml->xml_add_entry_to_group( 'bbcodegroup', $entry );
		
		$xml->xml_format_document();
		
		//-----------------------------------------
		// Send to browser.
		//-----------------------------------------
		
		$this->ipsclass->admin->show_download( $xml->xml_document, 'bbcode.xml', '', 0 );
	}
	
	//-----------------------------------------
	// BBCODE Remove
	//-----------------------------------------
	
	function bbcode_delete()
	{
		if ( ! $this->ipsclass->input['id'] )
		{
			$this->ipsclass->main_msg = "No ID was passed, please try again.";
			$this->bbcode_start();
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'custom_bbcode', 'where' => 'bbcode_id='.intval($this->ipsclass->input['id']) ) );
		
		$this->bbcode_rebuildcache();
		
		$this->bbcode_start();
	}
	
	//-----------------------------------------
	// BBCODE Rebuild Cache
	//-----------------------------------------
	
	function bbcode_rebuildcache()
	{
		$this->ipsclass->cache['bbcode'] = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'custom_bbcode' ) );
		$bbcode = $this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row($bbcode) )
		{
			$this->ipsclass->cache['bbcode'][] = $r;
		}

		$this->ipsclass->update_cache( array( 'name' => 'bbcode', 'array' => 1, 'deletefirst' => 1 ) );
	}
	
	//-----------------------------------------
	// BBCODE Test
	//-----------------------------------------
	
	function bbcode_test()
	{
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'custom_bbcode', 'order' => 'bbcode_title' ) );
		$this->ipsclass->DB->simple_exec();
		
		$t = $this->ipsclass->txt_stripslashes($_POST['bbtest']);
		
		while ( $row = $this->ipsclass->DB->fetch_row() )
		{
			$preg_tag = preg_quote($row['bbcode_tag'], '#' );
			
			if ( substr_count( $row['bbcode_replace'], '{content}' ) >= 1 )
			{
				//-----------------------------------------
				// Slightly slower
				//-----------------------------------------
				
				if ( $row['bbcode_useoption'] )
				{
					preg_match_all( "#(\[".preg_quote($row['bbcode_tag'], '#' )."=(?:&quot;|&\#39;)?(.+?)(?:&quot;|&\#39;)?\])(.+?)(\[/".preg_quote($row['bbcode_tag'], '#' )."\])#si", $t, $match );
					
					for ($i=0; $i < count($match[0]); $i++)
					{
						# XSS Check: Bug ID: 980
						if ( $row['bbcode_tag'] == 'post' OR $row['bbcode_tag'] == 'topic' )
						{
							$match[2][$i] = intval( $match[2][$i] );
						}
						
						//-----------------------------------------
						// Does the option tag come first?
						//-----------------------------------------
						
						$_option  = 2;
						$_content = 3;
						
						if ( $row['bbcode_switch_option'] )
						{
							$_option  = 3;
							$_content = 2;
						}
						
						$tmp = $row['bbcode_replace'];
						$tmp = str_replace( '{option}' , $match[ $_option  ][$i], $tmp );
						$tmp = str_replace( '{content}', $match[ $_content ][$i], $tmp );
						$t   = str_replace( $match[0][$i], $tmp, $t );
					}
				}
				else
				{
					# Tricky.. match anything that's not a closing tag, or nothing
					preg_match_all( "#(\[$preg_tag\])((?!\[/$preg_tag\]).+?)?(\[/$preg_tag\])#si", $t, $match );
					
					for ($i=0; $i < count($match[0]); $i++)
					{
						$tmp = $row['bbcode_replace'];
						$tmp = str_replace( '{content}', $match[2][$i], $tmp );
						$t   = str_replace( $match[0][$i], $tmp, $t );
					}
				}
			}
			else
			{
				$replace = explode( '{content}', $row['bbcode_replace'] );
				
				if ( $row['bbcode_useoption'] )
				{
					$t = preg_replace( "#\[".$row['bbcode_tag']."=(?:&quot;|&\#39;)?(.+?)(?:&quot;|&\#39;)?\]#si", str_replace( '{option}', "\\1", $replace[0] ), $t );
				}
				else
				{
					$t = preg_replace( '#\['.$row['bbcode_tag'].'\]#i' , $replace[0], $t );
				}
				
				$t = preg_replace( '#\[/'.$row['bbcode_tag'].'\]#i', $replace[1], $t );
			}
		}
		
		$this->ipsclass->main_msg = "<b>BBCode 测试:</b><br /><br />".$t;
		
		$this->bbcode_start();
	}
	
	//-----------------------------------------
	// BBCODE Save Form
	//-----------------------------------------
	
	function bbcode_save($type='add')
	{
		if ( $type == 'edit' )
		{
			if ( ! $this->ipsclass->input['id'] )
			{
				$this->ipsclass->main_msg = "No ID was passed, please try again";
				$this->bbcode_form($type);
			}
		}
		
		//-----------------------------------------
		// check...
		//-----------------------------------------
		
		if ( ! $this->ipsclass->input['bbcode_title'] or ! $this->ipsclass->input['bbcode_tag'] or ! $this->ipsclass->input['bbcode_replace'] )
		{
			$this->ipsclass->main_msg = "您必须完整填写表单.";
			$this->bbcode_form($type);
		}
		
		if ( ! strstr( $this->ipsclass->input['bbcode_replace'], '{content}' ) )
		{
			$this->ipsclass->main_msg = "您必须在 BBCode 置换中使用.";
			$this->bbcode_form($type);
		}
		
		if ( ! strstr( $this->ipsclass->input['bbcode_replace'], '{option}' ) AND $this->ipsclass->input['bbcode_useoption'] )
		{
			$this->ipsclass->main_msg = "您必须在 BBCode 置换中使用 {option}, 或关闭“标签中有可选参数";
			$this->bbcode_form($type);
		}
		
		$array = array( 'bbcode_title'             => $this->ipsclass->input['bbcode_title'],
						'bbcode_desc'              => $this->ipsclass->txt_safeslashes( $_POST['bbcode_desc'] ),
						'bbcode_tag'               => $this->ipsclass->input['bbcode_tag'],
						'bbcode_replace'           => $this->ipsclass->txt_safeslashes( $_POST['bbcode_replace'] ),
						'bbcode_example'           => $this->ipsclass->txt_safeslashes( $_POST['bbcode_example'] ),
						'bbcode_useoption'         => $this->ipsclass->input['bbcode_useoption'],
						'bbcode_switch_option'     => intval( $this->ipsclass->input['bbcode_switch_option'] ),
						'bbcode_add_into_menu'     => intval( $this->ipsclass->input['bbcode_add_into_menu'] ),
						'bbcode_menu_option_text'  => trim( $this->ipsclass->input['bbcode_menu_option_text'] ),
						'bbcode_menu_content_text' => trim( $this->ipsclass->input['bbcode_menu_content_text'] )
						 );
						
		if ( $type == 'add' )
		{
			$this->ipsclass->DB->do_insert( 'custom_bbcode', $array );
			$this->ipsclass->main_msg = 'BBCode 已添加';
		}
		else
		{
			$this->ipsclass->DB->do_update( 'custom_bbcode', $array, 'bbcode_id='.intval($this->ipsclass->input['id']) );
			$this->ipsclass->main_msg = 'BBCode 已编辑';
		}
		
		$this->bbcode_rebuildcache();
		
		$this->bbcode_start();
	
	}
	
	
	//-----------------------------------------
	// BBCODE Start Form
	//-----------------------------------------
	
	function bbcode_form($type='add')
	{
		$this->ipsclass->admin->page_detail = "您可以在这里添加自定义 BBCode.";
		$this->ipsclass->admin->page_title  = "自定义 BBCode 管理";
		$this->ipsclass->admin->nav[] 		= array( '', '添加/编辑 BBCode' );
		
		if ( $type == 'edit' )
		{
			if ( ! $this->ipsclass->input['id'] )
			{
				$this->ipsclass->main_msg = "No ID was passed, please try again";
				$this->bbcode_start();
			}
			
			$bbcode = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'custom_bbcode', 'where' => 'bbcode_id='.intval($this->ipsclass->input['id']) ) );
			
			$button = "Edit BBCode";
			$code   = 'bbcode_doedit';
			$title  = "Editing BBCode: ".$bbcode['bbcode_title'];
		}
		else
		{
			$bbcode = array( 'bbcode_title' 	=> '',
							 'bbcode_desc'		=> '',
							 'bbcode_example'	=> '',
							 'bbcode_tag'		=> '',
							 'bbcode_useoption'	=> '',
							 'bbcode_replace'	=> '' );
			$code   = 'bbcode_doadd';
			$title  = "添加自定义 BBCode";
			$button = "添加 BBCode";
		}
		
		//-----------------------------------------
		// Show the codes mahn!
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'bbcode'    ),
															   2 => array( 'code' , $code      ),
															   3 => array( 'id'   , $this->ipsclass->input['id'] ),
															   4 => array( 'section', $this->ipsclass->section_code ),
													  )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $title );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>BBCode 标题</b><div style='color:gray'>用于 BBCode 弹出提示</div>",
															   $this->ipsclass->adskin->form_input( 'bbcode_title', ( isset($this->ipsclass->input['bbcode_title']) AND $this->ipsclass->input['bbcode_title'] ) ? $this->ipsclass->input['bbcode_title'] : $bbcode['bbcode_title'] )
													 )      );
													 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>BBCode 说明</b><div style='color:gray'>用于 BBCode 弹出提示</div>",
															   $this->ipsclass->adskin->form_textarea( 'bbcode_desc', ( isset($this->ipsclass->input['bbcode_desc']) AND $this->ipsclass->input['bbcode_desc'] ) ? $this->ipsclass->input['bbcode_desc'] : $bbcode['bbcode_desc'] )
													 )      );
													 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>BBCode 示例</b><div style='color:gray'>用于 BBCode 弹出提示<br />在示例中使用标签: [tag]这里是示例![/tag]</div>",
															   $this->ipsclass->adskin->form_textarea( 'bbcode_example', ( isset($this->ipsclass->input['bbcode_example']) AND $this->ipsclass->input['bbcode_example'] ) ? $this->ipsclass->input['bbcode_example'] : $bbcode['bbcode_example'] )
													 )      );
													 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>BBCode 标记</b><div style='color:gray'>例如: 如果标记是 [tag], 那么输入 <b>tag</b></div>",
															   '[ '.$this->ipsclass->adskin->form_simple_input( 'bbcode_tag', ( isset($this->ipsclass->input['bbcode_tag']) AND $this->ipsclass->input['bbcode_tag'] ) ? $this->ipsclass->input['bbcode_tag'] : $bbcode['bbcode_tag'], 10).' ]'
													 )      );
													 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>标记中有可选参数</b><div style='color:gray'>用于创建 [tag=option] 这样的标记</div>",
															   $this->ipsclass->adskin->form_yes_no( 'bbcode_useoption', ( isset($this->ipsclass->input['bbcode_useoption']) AND $this->ipsclass->input['bbcode_useoption'] ) ? $this->ipsclass->input['bbcode_useoption'] : $bbcode['bbcode_useoption'] )
													 )      );
													
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>转换参数</b><div style='color:gray'>如果您想将 {content} 和 {option} 交换, 就打开本开关（例如创建[tag={content}]{option}[/tag]这样的标记）</div>",
															   				 $this->ipsclass->adskin->form_yes_no( 'bbcode_switch_option', ( isset($this->ipsclass->input['bbcode_switch_option']) AND $this->ipsclass->input['bbcode_switch_option'] ) ? $this->ipsclass->input['bbcode_switch_option'] : $bbcode['bbcode_switch_option'] )
													 				)      );
													 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>BBCode 置换</b><div style='color:gray'>将 BBCode 标记转换为一段 HTML 代码<br />&lt;tag&gt;{content}&lt;/tag&gt;<br />&lt;tag thing='{option}'&gt;{content}&lt;/tag&gt;</div>",
															   				 $this->ipsclass->adskin->form_textarea( 'bbcode_replace', ( isset($this->ipsclass->input['bbcode_replace']) AND $this->ipsclass->input['bbcode_replace'] ) ? $this->ipsclass->input['bbcode_replace'] : $bbcode['bbcode_replace'] )
													 				)      );
		//-----------------------------------------
		// Insert Special Options
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>插入到编辑器的 “插入特殊” 菜单</b>",
															   				 $this->ipsclass->adskin->form_yes_no( 'bbcode_add_into_menu', ( isset($this->ipsclass->input['bbcode_add_into_menu']) AND $this->ipsclass->input['bbcode_add_into_menu'] ) ? $this->ipsclass->input['bbcode_add_into_menu'] : $bbcode['bbcode_add_into_menu'] )
													 				)      );
													
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>输入 '选项' 对话框文字</b><div style='color:gray'>显示在 '选项' 文本框上方的帮助提示. 该内容将在您点击了 '特殊输入' 菜单的相应条目时出现.</div>",
															   			 	 $this->ipsclass->adskin->form_simple_input( 'bbcode_menu_option_text', ( isset($this->ipsclass->input['bbcode_menu_option_text']) AND $this->ipsclass->input['bbcode_menu_option_text'] ) ? $this->ipsclass->input['bbcode_menu_option_text'] : $bbcode['bbcode_menu_option_text'], 50)
													 				)      );
													
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>输入 '内容' 对话框文字 </b><div style='color:gray'>显示在 '内容' 文本框上方的帮助提示. 该内容将在您点击了 '特殊输入' 菜单的相应条目时出现.</div>",
															   			 	 $this->ipsclass->adskin->form_simple_input( 'bbcode_menu_content_text', ( isset($this->ipsclass->input['bbcode_menu_content_text']) AND $this->ipsclass->input['bbcode_menu_content_text'] ) ? $this->ipsclass->input['bbcode_menu_content_text'] : $bbcode['bbcode_menu_content_text'], 50)
													 				)      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( $button );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= "<br /><div class='tableborder'><div class='tablerow1' style='padding:6px'><b>更多信息</b><br />添加 BBCode 置换时, 不要忘记在解析标记内容的位置输入 {content}. <br />
						    如果您要创建一个 <b>[tag=option][/tag]</b> 这样的标签, 不要忘记在 BBCode 置换中可选参数的位置输入 {option} . </div></div>";
		
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	
	}
	
	//-----------------------------------------
	// BBCODE Splash
	//-----------------------------------------
	
	function bbcode_start()
	{
		$this->ipsclass->admin->page_detail = "您可以在这里添加自定义 BBCode.";
		$this->ipsclass->admin->page_title  = "自定义 BBCode 管理";
		
		//-----------------------------------------
		// Show the codes mahn!
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'custom_bbcode', 'order' => 'bbcode_title' ) );
		$this->ipsclass->DB->simple_exec();
		
		$bbcode_rows = "";
		
		while ( $row = $this->ipsclass->DB->fetch_row() )
		{
			if ( $row['bbcode_useoption'] )
			{
				$option = '={option}';
			}
			else
			{
				$option = '';
			}
			
			$row['bbcode_fulltag'] = '['.$row['bbcode_tag'].$option.']{content}[/'.$row['bbcode_tag'].']';
			
			$bbcode_rows .= $this->html->bbcode_row( $row );
		}
		
		$this->ipsclass->html .= $this->html->bbcode_wrapper( $bbcode_rows );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'bbcode'       ),
															     2 => array( 'code' , 'bbcode_test' ),
															     4 => array( 'section', $this->ipsclass->section_code ),
													  )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "测试您的自定义 BBCode" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "测试您的自定义 BBCode",
																$this->ipsclass->adskin->form_textarea( 'bbtest', isset($_POST['bbtest']) ? $_POST['bbtest'] : '' ),
														 )      );
														 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("测试");
														 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// IMPORT: Start table
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		
		//-----------------------------------------
		// IMPORT: Start output
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'bbcode_import' ),
															   2 => array( 'act'   , 'bbcode'      ),
															   3 => array( 'MAX_FILE_SIZE', '10000000000' ),
															   4 => array( 'section', $this->ipsclass->section_code ),
													  ) , "uploadform", " enctype='multipart/form-data'"     );
													
													  			
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "导入 BBCode 列表" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
													 		 "<b>上传 XML BBCode 列表</b><div style='color:gray'>文件名必须为“bbcode.xml”或“bbcode.xml.gz”. 不会导入重复的 [tag] 项目. </div>",
													  		 $this->ipsclass->adskin->form_upload(  )
													   )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("导入");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	
	}
	

	
}


?>