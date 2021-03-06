<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core variables extension file
 * Last Updated: $Date: 2010-04-23 19:39:24 -0400 (Fri, 23 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 6174 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$_LOAD = array();

# PERSONAL MESSAGES
if( ( isset( $_GET['module'] ) && $_GET['module'] == 'messaging' ) )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['profilefields']		= 1;
	$_LOAD['badwords']			= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['moderators']		= 1;
}

# MEMBER LIST
if( ( isset( $_GET['section'] ) && $_GET['section'] == 'view' ) && ( isset( $_GET['module'] ) && $_GET['module'] == 'list' ) )
{
	$_LOAD['ranks']			= 1;
	$_LOAD['profilefields']	= 1;
	$_LOAD['reputation_levels']	= 1;
}

# ONLINE LIST
if( isset( $_GET['module'] ) && $_GET['module'] == 'online' )
{
	$_LOAD['ranks']			= 1;
	$_LOAD['profilefields']	= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['moderators']	= 1; // This is need for forums application - as there are usually people browsing topics,
								 // we should load this on the assumption it'll be needed anyways
}

# Status updates
if( isset( $_GET['section'] ) && $_GET['section'] == 'status' )
{
	$_LOAD['ranks']			= 1;
	$_LOAD['profilefields']	= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['moderators']	= 1; // This is need for forums application - as there are usually people browsing topics,
								 // we should load this on the assumption it'll be needed anyways
}

# PROFILE
if ( ( isset( $_GET['showuser'] ) && $_GET['showuser'] ) OR $_GET['module'] == 'profile' AND $_GET['section'] == 'view' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['profilefields']		= 1;
	$_LOAD['badwords']			= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['moderators']		= 1;
}

/* Never, ever remove or re-order these options!!
 * Feel free to add, though. :) */

$_BITWISE = array();