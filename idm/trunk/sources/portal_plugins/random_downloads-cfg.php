<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   =============================================
+---------------------------------------------------------------------------
|   > $Date: 2006-09-22 05:28:54 -0500 (Fri, 22 Sep 2006) $
|   > $Revision: 567 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > PORTAL PLUG IN MODULE: Random downloads - CONFIG FILE
|   > Module written by Brandon Farber
|   > Date started: Wednesday 4th October 2006 (13:37)
+--------------------------------------------------------------------------
*/

/**
* This file must be named {file_name_minus_php}-cfg.php
*
* Please see each variable for more information
* $PORTAL_CONFIG is OK for each file, do not change
* this array name.
*/

$PORTAL_CONFIG = array();

/**
* Main plug in title
*
*/
$PORTAL_CONFIG['pc_title'] = 'IP.Downloads Manager Random Downloads';

/**
* Plug in mini description
*
*/
$PORTAL_CONFIG['pc_desc']  = "Shows random downloads with names and date uploaded";

/**
* Keyword for settings. This is the keyword
* entered into ibf_conf_settings_titles -> conf_title_keyword
* Can be left blank.
* PLEASE stick to the naming convention when entering a setting
* keyword: portal_{file_name_minus_php} This will prevent
* other keyword clashes. Likewise, when creating settings, choose
* NOT to cache them (they will be loaded at run time) and always
* prefix with {file_name_minus_php}_setting_key - for example
* If you had a setting called "export_forums" then please name it
* "recent_topics_export_forums". This will be available in
* $this->ipsclass->vars['recent_topics_export_forums'] in the
* main module.
*/
$PORTAL_CONFIG['pc_settings_keyword'] = "";

/**
* Exportable tags key must be in the naming format of:
* {file_name_minus_php}-tag. The value *MUST* be the function
* which it corresponds to. For example:
* 'recent_topics_last_x' => 'recent_topics_last_x'
* The portal will look for function 'recent_topics_last_x' in
* module "sources/portal_plugins/recent_topics.php" when it parses
* the tag <!--::recent_topics_last_x::-->
*
* @param array[ TAG ] = array( FUNCTION NAME, DESCRIPTION );
*/
$PORTAL_CONFIG['pc_exportable_tags']['random_downloads']             = array( 'random_downloads'            , 'Shows random downloads with names and date uploaded' );

?>