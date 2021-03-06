<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Facebook Connect Library
 * Created by Matt M
 * Last Updated: $Date: 2010-03-30 17:04:39 +0100 (Tue, 30 Mar 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6039 $
 *
 */

class facebook_connect
{
	/**#@+
	* Registry Object Shortcuts
	*
	* @access	protected
	* @var		object
	*/
	protected $DB;
	protected $settings;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/
	
	/**
	 * IPBs log in handler
	 *
	 * @access	private
	 * @var		object
	 */
	private $_login;
	
	/**
	 * Facebooks class wrapper
	 *
	 * @access	private
	 * @var		object
	 */
	private $_fb;
	
	/**
	 * Facebooks REST API wrapper
	 *
	 * @access	private
	 * @var		object
	 */
	private $_api;
	
	/**
	 * Construct.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct( $registry )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		/* Test */
		if ( IPSLib::fbc_enabled() !== TRUE )
		{
			throw new Exception( 'FACEBOOK_DISABLED_OR_NOT_SET_UP' );
		}
		
		/* Load and set up the facebook stuff */
		require_once( IPS_KERNEL_PATH . 'facebook-client/facebook.php' );
		$this->_fb  = new Facebook( $this->settings['fbc_api_id'], $this->settings['fbc_secret'], true );
		$this->_api = $this->_fb->api_client;
	}
	
	/**
	 * Accessor for the facebook functions
	 *
	 * @access	public
	 * @return	object
	 */
	public function FB()
	{
		return $this->_fb;
	}
	
	/**
	 * Accessor for the facebook REST API functions
	 *
	 * @access	public
	 * @return	object
	 */
	public function API()
	{
		return $this->_api;
	}
	
	/**
	 * Set user
	 * If no params are passed, will assume data from $this->memberData
	 *
	 * @access	public
	 * @param	int		[FB user ID]
	 * @param	string	[FB permanent session ID]
	 */
	public function setUser( $fb_uid, $fb_session )
	{
		$fb_uid     = ( $fb_uid )     ? $fb_uid     : $this->memberData['fb_uid'];
		$fb_session = ( $fb_session ) ? $fb_session : $this->memberData['fb_session'];
		
		if ( $fb_uid AND $fb_session )
		{
			$this->FB()->set_user( $fb_uid, $fb_session );
		}
		else if ( $fb_uid )
		{
			try
			{
				$fb_uid = $this->API()->users_getLoggedInUser();
				
				if ( $fb_uid )
				{
					$fb_session = $this->API()->session_key;
					
					if ( $fb_session )
					{
						$this->FB()->set_user( $fb_uid, $fb_session );
					}
				}
			}
			catch( Exception $error )
			{
				$this->unsetCookies();
			}
		}
	}
	
	/**
	 * Post a link to the user's FB wall
	 *
	 * @access	public
	 * @param	string		URL
	 * @param	string		Comment (can be NUFING)
	 */
	public function postLinkToWall( $url, $comment='' )
	{
		$memberData = $this->memberData;
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] OR ! $memberData['fb_session'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
				
		/* Grab the data */
		try
		{
			$this->setUser( $memberData['fb_uid'], $memberData['fb_session'] );
			$r = $this->API()->links_post( $url, $comment, $memberData['fb_uid'] );
		}
		catch( Exception $e )
		{
			//IPSDebug::addLogMessage( 'facebook', 'fbc', $e, TRUE );
			throw new Exception( $e->getMessage() );
		}
	}

	
	/**
	 * Post a status update to Facebook based on native content
	 * Which may be longer and such and so on and so forth, etc
	 *
	 * @access	public
	 * @param	string		Content
	 * @param	string		URL to add
	 * @param	bool		Always add the URL regardless of content length
	 */
	public function updateStatusWithUrl( $content, $url, $alwaysAdd=false )
	{
		$memberData = $this->memberData;
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
		
		/* Ensure content is correctly de-html-ized */
		$content = IPSText::UNhtmlspecialchars( $content );
		
		/* Is the text longer than 140 chars? */
		if ( $alwaysAdd === TRUE or IPSText::mbstrlen( $content ) > 500 )
		{
			/* Leave 26 chars for URL shortener */
			$content = IPSText::mbsubstr( $content, 0, 474 ) . '...';
			
			/* Generate short URL */
			require_once( IPS_ROOT_PATH . 'sources/classes/url/shorten.php' );
 			$shorten = new urlShorten();
 			
 			$data      = $shorten->shorten( $url, 'bitly' );
 			
 			$content .= ' ' . $data['url'];
		}
		
		/* Grab the data */
		try
		{
			$this->setUser( $memberData['fb_uid'], $memberData['fb_session'] );
			$r = $this->API()->users_setStatus( $content, $memberData['fb_uid'] );
			
		}
		catch( Exception $e )
		{
			//IPSDebug::addLogMessage( 'facebook', 'fbc', $e, TRUE );
		}
	}
	
	
	/**
	 * Function to resync a member's FB data
	 *
	 * @access	public
	 * @param	mixed		Member Data in an array form (result of IPSMember::load( $id, 'all' ) ) or a member ID
	 * @return	array 		Updated member data	
	 *
	 * EXCEPTION CODES:
	 * NO_MEMBER		Member ID does not exist
	 * NOT_LINKED		Member ID or data specified is not linked to a FB profile
	 */
	public function syncMember( $memberData )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$exProfile = array();
		
		/* Do we need to load a member? */
		if ( ! is_array( $memberData ) )
		{
			$memberData = IPSMember::load( intval( $memberData ), 'all' );
		}
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
		
		/* Thaw Options */
		$bwOptions = IPSBWOptions::thaw( $memberData['fb_bwoptions'], 'facebook' );
		
		/* Grab the data */
		try
		{
			$this->setUser( $memberData['fb_uid'], $memberData['fb_session'] );
			$_fbData = $this->API()->users_getInfo( $memberData['fb_uid'], array( 'first_name', 'last_name', 'name', 'status', 'pic', 'pic_square', 'pic_square_with_logo', 'about_me', 'email_hashes' ) );
			$fbData  = $_fbData[0];
		
			/* Format data */
			$emailHash = ( is_array( $fbData['email_hashes'] ) AND $fbData['email_hashes'][0] ) ? $fbData['email_hashes'][0] : $memberData['fb_emailhash'];
		
			/* Update.. */
			$exProfile['fb_photo']       = ( $bwOptions['fbc_s_pic'] ) ? $fbData['pic']        : '';
			$exProfile['fb_photo_thumb'] = ( $bwOptions['fbc_s_pic'] ) ? ( strstr( $memberData['email'], '@proxymail.facebook.com' ) ? $fbData['pic_square_with_logo'] : $fbData['pic_square'] ) : '';
		
			if ( $bwOptions['fbc_s_avatar'] )
			{
				$exProfile['avatar_location'] = $fbData['pic_square'];
				$exProfile['avatar_type']     = 'facebook';
			}
		
			if ( $bwOptions['fbc_s_aboutme'] )
			{
				$exProfile['pp_about_me'] = IPSText::getTextClass( 'bbcode' )->stripBadWords( IPSText::convertCharsets( $fbData['about_me'], 'utf-8', IPS_DOC_CHAR_SET ) );
			}
		
			if ( $bwOptions['fbc_si_status'] AND is_array($fbData['status']) AND $fbData['status']['message'] AND ! $memberData['gbw_no_status_import'] )
			{
				/* Load status class */
				if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
				{
					require_once( IPS_ROOT_PATH . 'sources/classes/member/status.php' );
					$this->registry->setClass( 'memberStatus', new memberStatus( ipsRegistry::instance() ) );
				}
				
				/* Set Author */
				$this->registry->getClass('memberStatus')->setAuthor( $memberData );
				
				/* Set as imported */
				$this->registry->getClass('memberStatus')->setIsImport( 1 );
				
				/* Set creator */
				$this->registry->getClass('memberStatus')->setCreator( 'facebook' );
				
				/* Convert if need be */
				if ( IPS_DOC_CHAR_SET != 'UTF-8' )
				{
					$fbData['status']['message'] = IPSText::utf8ToEntities( $fbData['status']['message'] );
				}
							
				/* Set Content */
				$this->registry->getClass('memberStatus')->setContent( trim( IPSText::getTextClass( 'bbcode' )->stripBadWords( $fbData['status']['message'] ) ) );
				
				/* Can we reply? */
				if ( $this->registry->getClass('memberStatus')->canCreate() )
		 		{
					$this->registry->getClass('memberStatus')->create();
				}
			}
									
			/* Update member */
			IPSMember::save( $memberData['member_id'], array( 'core' 			=> array( 'fb_emailhash' => $emailHash, 'fb_lastsync' => time() ),
															  'extendedProfile' => $exProfile ) );
		
			/* merge and return */
			$memberData['fb_lastsync'] = time();
			$memberData = array_merge( $memberData, $exProfile );
		}
		catch( Exception $e )
		{
		}
		
		return $memberData;
	}
	
	/**
	 * Link to IPB account 
	 *
	 * @access	public
	 * @param	int			Member ID
	 * @return	boolean
	 *
	 * EXCEPTION CODES:
	 * NO_FACEBOOK_USER_LOGGED_IN		System cannot detect a logged in facebook user
	 * ALREADY_LINKED					Auth member is already linked to a different FB account
	 */
	public function linkMember( $memberID )
	{
		$loggedInUser = $this->FB()->get_loggedin_user();
		
		if ( ! $loggedInUser )
		{
			throw new Exception( 'NO_FACEBOOK_USER_LOGGED_IN' );
		}
	
		$memberData = IPSMember::load( $memberID, 'all' );
		
		/* Already FBd? */
		if ( $memberData['fb_uid'] AND ( $memberData['fb_uid'] != $loggedInUser ) )
		{
			throw new Exception( 'ALREADY_LINKED' );
		}
		
		/* Ensure that there is not already a linked account */
		$_member = IPSMember::load( $loggedInUser, 'all', 'fb_uid' );
		
		if ( $_member['fb_uid'] AND ( $_member['member_id'] != $memberID ) )
		{
			throw new Exception( 'ALREADY_LINKED' );
		}
	
		/* Associate this account with FBC */
		$hash = $this->generateEmailhash( $memberData['email'] );
		
		/* Got a correct key? */
				if ( ! preg_match( "#^[a-z0-9]{24}-(\d+?)$#", $_sKey ) )
				{
					$_sKey = '';
				}
				
				/* Update... */
				IPSMember::save( $memberData['member_id'], array( 'core' => array( 'fb_uid' => $loggedInUser, 'fb_emailhash' => $hash, 'fb_session' => $_sKey ) ) );
				
				
		/* Update... */
		IPSMember::save( $memberData['member_id'], array( 'core' => array( 'fb_uid' => $loggedInUser, 'fb_emailhash' => $hash ) ) );
		
		/* Register with Facebook */
		try
		{
			$reg = $this->API()->connect_registerUsers( json_encode( array( array( 'email_hash' => $hash, 'account_id' => $memberData['member_id'] ) ) ) );
		}
		catch( Exception $error )
		{
			//print $error->getMessage(); exit();
		}
		
		return $result;
	}
	
	/**
	 * Log in and create a brand new forum account
	 *
	 * @access	public
	 * @return	mixed		On success, an array containing a message and redirect URL
	 *
	 * EXCEPTION CODES:
	 * NO_FACEBOOK_USER_LOGGED_IN		System cannot detect a logged in facebook user
	 * NO_FB_EMAIL						Could not locate a facebook proxy email
	 * CREATION_FAIL					Account creation failed
	 * ALREADY_LINKED_MEMBER			The facebook UID is already linked to another IPB account
	 */
	public function loginWithNewAccount()
	{
		$loggedInUser = $this->FB()->get_loggedin_user();
	
		if ( ! $loggedInUser )
		{
			throw new Exception( 'NO_FACEBOOK_USER_LOGGED_IN' );
		}
		
		/* Ensure that there is not already a linked account */
		/* Now get the linked user */
		$_member = IPSMember::load( $loggedInUser, 'all', 'fb_uid' );
	
		if ( $_member['member_id'] )
		{
			throw new Exception( 'ALREADY_LINKED_MEMBER' );
		}
		
		/* Now fetch more data */
		$_fbData = $this->API()->users_getInfo( $loggedInUser, array( 'name', 'email', 'timezone', 'pic', 'pic_square', 'pic_square_with_logo', 'about_me' ) );
		$_sKey   = $this->API()->session_key;
		$fbData  = $_fbData[0];
	
		/* Got a correct key? */
		if ( ! preg_match( "#^[a-z0-9]{24}-(\d+?)$#", $_sKey ) )
		{
			$_sKey = '';
		}
		
		/* Generate BW options */
		foreach( array( 'fbc_s_pic', 'fbc_s_avatar', 'fbc_s_status', 'fbc_s_aboutme' ) as $field )
		{
			$toSave[ $field ] = 1;
		}
		
		$fb_bwoptions = IPSBWOptions::freeze( $toSave, 'facebook' );
		
		/* Generate FB hash */
		$hash = $this->generateEmailHash( $fbData['email'] );
		
		/* Data to save */
		$toSave = array( 'core' 		=> array( 'name' 				   => IPSText::convertCharsets( $fbData['name'], 'utf-8', IPS_DOC_CHAR_SET ),
										 		  'members_display_name'   => IPSText::convertCharsets( $fbData['name'], 'utf-8', IPS_DOC_CHAR_SET ),
										 		  'members_created_remote' => 1,
										 		  'member_group_id'		   => ( $this->settings['fbc_mgid'] ) ? $this->settings['fbc_mgid'] : $this->settings['member_group'],
												  'email'                  => $fbData['email'],
												  'time_offset'            => $fbData['timezone'],
												  'fb_uid'                 => $loggedInUser,
												  'fb_session'			   => $_sKey,
												  'fb_emailhash'		   => $hash ),
						'extendedProfile' => array( 'pp_about_me'     => IPSText::getTextClass( 'bbcode' )->stripBadWords( IPSText::convertCharsets( $fbData['about_me'], 'utf-8', IPS_DOC_CHAR_SET ) ),
													'fb_photo'        => $fbData['pic'],
													'fb_photo_thumb'  => $fbData['pic_square_with_logo'],
													'fb_bwoptions'    => $fb_bwoptions,
													'avatar_location' => $fbData['pic_square'],
													'avatar_type'     => 'facebook' ) );
		
		/* Ensure we don't already have a partially created FB member */
		$testMember = IPSMember::load( $toSave['core']['email'], 'core' );
		
		if ( $testMember['member_id'] )
		{
			IPSMember::save( $testMember['member_id'], $toSave );
			$memberData = array_merge( $testMember, $toSave );
		}
		else
		{
			$memberData = IPSMember::create( $toSave, TRUE, FALSE, FALSE );
		}
		
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'CREATION_FAIL' );
		}
	
		/* Register with Facebook */
		try
		{
			$reg = $this->API()->connect_registerUsers( json_encode( array( array( 'email_hash' => $hash, 'account_id' => $memberData['member_id'] ) ) ) );
		}
		catch( Exception $error )
		{
			//print $error->getMessage(); exit();
		}
		
		//-----------------------------------------
		// Update Stats
		//-----------------------------------------

		$cache	= $this->cache->getCache('stats');
		
		if( $memberData['members_display_name'] AND $memberData['member_id'] )
		{
			$cache['last_mem_name']	= $memberData['members_display_name'];
			$cache['last_mem_id']	= $memberData['member_id'];
		}
		
		$cache['mem_count']		+= 1;
		
		$this->cache->setCache( 'stats', $cache, array( 'array' => 1 ) );
		
		//-----------------------------------------
		// New registration emails
		//-----------------------------------------
		
		if( $this->settings['new_reg_notify'] )
		{
			$this->lang->loadLanguageFile( array( 'public_register' ), 'core' );

			$date = $this->registry->class_localization->getDate( time(), 'LONG', 1 );
			
			IPSText::getTextClass('email')->getTemplate( 'admin_newuser' );
			IPSText::getTextClass('email')->buildMessage( array( 'DATE'			=> $date,
																 'LOG_IN_NAME'  => $memberData['name'],
																 'EMAIL'		=> $memberData['email'],
																 'IP'			=> $memberData['ip_address'],
																 'DISPLAY_NAME'	=> $memberData['members_display_name'] ) );
										
			IPSText::getTextClass('email')->subject = $this->lang->words['new_registration_email1'] . $this->settings['board_name'];
			IPSText::getTextClass('email')->to      = $this->settings['email_in'];
			IPSText::getTextClass('email')->sendMail();
		}
	
		/* Here, so log us in!! */
		return $this->_login()->loginWithoutCheckingCredentials( $memberData['member_id'], TRUE );
	}
	
	/**
	 * Log in with an existing FB->IPB link
	 *
	 * @access	public
	 * @return	mixed		On success, an array containing a message and redirect URL
	 *
	 * EXCEPTION CODES:
	 * NO_FACEBOOK_USER_LOGGED_IN		System cannot detect a logged in facebook user
	 * NO_LINKED_MEMBER					Could not locate a linked member
	 */
	public function loginWithExistingLink()
	{
		$loggedInUser = $this->FB()->get_loggedin_user();
	
		if ( ! $loggedInUser )
		{
			throw new Exception( 'NO_FACEBOOK_USER_LOGGED_IN' );
		}
	
		/* Now get the linked user */
		$memberData = IPSMember::load( $loggedInUser, 'all', 'fb_uid' );
	
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_LINKED_MEMBER' );
		}
	
		/* Here, so log us in!! */
		return $this->_login()->loginWithoutCheckingCredentials( $memberData['member_id'], TRUE );
	}
	
	/**
	 * Link to IPB account and log in
	 * Allows the user to link to an existing facebook account by passing a username and password
	 *
	 * @access	public
	 * @param	string		Email address
	 * @param	string		Plain text password
	 * @return	mixed		On success, an array containing a message and redirect URL
	 *
	 * EXCEPTION CODES:
	 * NO_FACEBOOK_USER_LOGGED_IN		System cannot detect a logged in facebook user
	 * AUTH_FAIL						Email address or password incorrect
	 * ALREADY_LINKED					Auth member is already linked to a different FB account
	 */
	public function loginWithCreateLink( $email, $password )
	{
		$loggedInUser = $this->FB()->get_loggedin_user();
		
		if ( ! $loggedInUser )
		{
			throw new Exception( 'NO_FACEBOOK_USER_LOGGED_IN' );
		}
		
		/* Force email check */
		$this->_login()->setForceEmailCheck( TRUE );
		
		/* Now attempt to authorize member */
    	$return = $this->_login()->loginPasswordCheck( '', $email, $password );

    	if ( $return !== TRUE )
		{
			throw new Exception( 'AUTH_FAIL' );
		}
		else
		{
			$memberData = $this->_login()->member_data;
			
			/* Already FBd? */
			if ( $memberData['fb_uid'] AND ( $memberData['fb_uid'] != $loggedInUser ) )
			{
				throw new Exception( 'AUTH_FAIL' );
			}
			
			/* Un-force email check */
			$this->_login()->setForceEmailCheck( FALSE );
			
			/* Ok, now the easiest way to get them logged in is to do this.. .*/
			$this->request['username']   = $memberData['name'];
			$this->request['rememberMe'] = 1;
			
			$result = $this->_login()->verifyLogin();
		
			if ( $result[2] )
			{
				throw new Exception( 'AUTH_FAIL' );
			}
			else
			{
				/* Associate this account with FBC */
				$hash = $this->generateEmailhash( $memberData['email'] );
				
				$_sKey   = $this->API()->session_key;
		
				/* Got a correct key? */
				if ( ! preg_match( "#^[a-z0-9]{24}-(\d+?)$#", $_sKey ) )
				{
					$_sKey = '';
				}
				
				/* Update... */
				IPSMember::save( $memberData['member_id'], array( 'core' => array( 'fb_uid' => $loggedInUser, 'fb_emailhash' => $hash, 'fb_session' => $_sKey ) ) );
				
				/* Register with Facebook */
				try
				{
					$reg = $this->API()->connect_registerUsers( json_encode( array( array( 'email_hash' => $hash, 'account_id' => $memberData['member_id'] ) ) ) );
				}
				catch( Exception $error )
				{
					//print $error->getMessage(); exit();
				}
				
				return $result;
			}
		}
	}
	
	/**
	 * Facebook Connect Bug Fix: Test the API and delete cookies
	 * if we get a session error: http://bugs.developers.facebook.com/show_bug.cgi?id=3237
	 * 
	 * @access	public
	 * @return	void
	 */
	public function testConnectSession()
	{
		try
		{
			$loggedInUser = $this->API()->users_getLoggedInUser();
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
	
			$this->unsetCookies();
		}
	}
	
	/**
	 * Unset cookies
	 *
	 * @access	public
	 * @return	void
	 */
	public function unsetCookies()
	{
		foreach( array( '_user', '_session_key', '_expires', '_ss' ) as $key )
		{
			IPSCookie::set( $this->settings['fbc_api_id'] . $key, -1, 0, -1 );
			unset( $_COOKIE[ $this->settings['fbc_api_id'] . $key ] );
		}
		
		IPSCookie::set( $this->settings['fbc_api_id'], -1, 0, -1 );
		IPSCookie::set( 'fbsetting_' . $this->settings['fbc_api_id'], -1, 0, -1 );
	}
	
	/**
	 * Facebook: Generate Email Hash
	 * 
	 * @access	public
	 * @param	string 		Email Address
	 * @return  string 		Facebook Hash (crc32 _ md5 )
	 */
	static public function generateEmailhash( $email )
	{
		if ( $email != NULL )
		{
	    	$email = trim( strtolower( $email ) );
	
	    	return sprintf( "%u", crc32( $email ) ) . '_' . md5( $email );
	  	}
		else
		{
	    	return '';
	 	}
	}

	/**
	 * Returns 1 if the user has the specified permission, 0 otherwise.
	 * http://wiki.developers.facebook.com/index.php/Users.hasAppPermission
	 *
	 * @return integer  1 or 0
	 */
	public function users_hasAppPermission($ext_perm, $uid=null)
	{
    	return $this->_api->call_method('facebook.users.hasAppPermission', array('ext_perm' => $ext_perm, 'uid' => $uid) );
 	}
	
	/**
	 * Accessor for the log in functions
	 *
	 * @access	public
	 * @return	object
	 */
	public function _login()
	{
		if ( ! is_object( $this->_login ) )
		{
			require_once( IPS_ROOT_PATH . 'sources/handlers/han_login.php' );
	    	$this->_login =  new han_login( $this->registry );
	    	$this->_login->init();
		}
		
		return $this->_login;
	}
}