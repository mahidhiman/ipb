<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Template plugin: Perform row striping
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		6/24/2008
 * @version		$Revision: 5713 $
 */

/**
* Main loader class
*/
class tp_striping extends output implements interfaceTemplatePlugins
{
	/**
	 * Prevent our main destructor being called by this class
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
	}
	
	/**
	 * Run the plug-in
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @param	string	The initial data from the tag
	 * @param	array	Array of options
	 * @return	string	Processed HTML
	 */
	public function runPlugin( $data, $options )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$phpCode = '';
		
		if ( ! isset( $options['classes'] ) )
		{
			return '" .  IPSLib::next( $this->registry->templateStriping["'.$data.'"] ) . "';
		}
		else
		{
			$_classes = explode( ",", trim( $options['classes'] ) );
		
			//$phpCode .= "\n " . '$this->registry->templateStriping[\'' . $data . '\'] = array( FALSE, "' . implode( '","', $_classes ) . "\");\n";
			$phpCode .= "\n if ( ! isset( " . '$this->registry->templateStriping[\'' . $data . "'] ) ) {\n" . '$this->registry->templateStriping[\'' . $data . '\'] = array( FALSE, "' . implode( '","', $_classes ) . "\");\n}\n";
		}
		
		//-----------------------------------------
		// Process the tag and return the data
		//-----------------------------------------

		return ( $phpCode ) ? "<php>" . $phpCode . "</php>" : '';
	}
	
	/**
	 * Return information about this modifier.
	 *
	 * It MUST contain an array  of available options in 'options'. If there are no allowed options, then use an empty array.
	 * Failure to keep this up to date will most likely break your template tag.
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @return	array
	 */
	public function getPluginInfo()
	{
		return array( 'name'    => 'striping',
					  'author'  => 'IPS, Inc.',
					  'usage'   => '{parse striping="someKey" classes="row1, row2"}',
					  'options' => array( 'classes' ) );
	}
}