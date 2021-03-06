//------------------------------------------
// Invision Power Board v2.1
// User CP JS File
// (c) 2005 Invision Power Services, Inc.
//
// http://www.invisionboard.com
//------------------------------------------

var ucp_dname_name;
var ucp_dname_pass;
var ucp_dname_name_img;
var ucp_dname_pass_img;
var ucp_dname_form;
var ucp_dname_warn;
var ucp_dname_warn_content;
var dname_max_length = 26;

var ucp_dname_ok_to_go      = 0;
var ucp_dname_illegal_chars = new Array( '[', ']', '|', ',', ';', '$' );
var ucp_dname_illegal_regex = '';

for ( var i in ucp_dname_illegal_chars )
{
	ucp_dname_illegal_regex += '\\' + ucp_dname_illegal_chars[i];
}

var error_found = '';

RegExp.escape = function(text)
{
	if (!arguments.callee.sRE)
	{
	   	var specials = [ '/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\', '$' ];
	    	
	   	arguments.callee.sRE = new RegExp( '(\\' + specials.join('|\\') + ')', 'g' );
	}
	  
	return text.replace(arguments.callee.sRE, '\\$1');
};

/*-------------------------------------------------------------------------*/
// INIT Display name change stuff
/*-------------------------------------------------------------------------*/

function ucp_dname_init( name, pass, form, wbox )
{
	ucp_dname_name = document.getElementById( name );
	ucp_dname_pass = document.getElementById( pass );
	ucp_dname_form = document.getElementById( form );
	ucp_dname_warn = document.getElementById( wbox );
	
	ucp_dname_warn_content = document.getElementById( wbox+'-content' );
	ucp_dname_name_img     = document.getElementById( name+'-img' );
    ucp_dname_pass_img     = document.getElementById( pass+'-img' );

	ucp_dname_name.onblur   = ucp_dname_check;
	ucp_dname_form.onsubmit = ucp_dname_form_check;
	
	if ( ucp_dname_warn_content.innerHTML )
	{
		ucp_dname_warn.style.display = 'block';
	}
}

/*-------------------------------------------------------------------------*/
// EVENT: Check display name
/*-------------------------------------------------------------------------*/

function ucp_dname_check( event )
{
	//----------------------------------
	// INIT
	//----------------------------------

	//----------------------------------
	// Make sure we have sommat
	//----------------------------------
	
	if ( ! ucp_dname_name.value || ucp_dname_name.value.length < 3 || ucp_dname_name.value.length > dname_max_length )
	{
		error_found += dname_error_no_name + "<br />";
	}
	
	//----------------------------------
	// Check for illegal chars
	//----------------------------------
	
	if ( ucp_dname_name.value.match( new RegExp( "[" + ucp_dname_illegal_regex + "]" ) ) )
	{
		error_found += dname_error_chars + "<br />";
	}
	
	if ( allowed_chars != "" )
	{
		var test_regex = new RegExp();

		test_regex.compile( "^[" + RegExp.escape(allowed_chars) + "]+$" );
		
		if ( !test_regex.test( ucp_dname_name.value ) )
		{
			error_found += allowed_error + "<br />";
		}
	}	
	
	//----------------------------------
	// Ajax: check for existing member name
	//----------------------------------
	
	if ( use_enhanced_js && ucp_dname_name.value )
	{
		var url = ipb_var_base_url+'act=xmlout&do=check-display-name&name='+escape( ucp_dname_name.value );
	
		/*--------------------------------------------*/
		// Main function to do on request
		// Must be defined first!!
		/*--------------------------------------------*/
		
		do_request_function = function()
		{
			//----------------------------------
			// Ignore unless we're ready to go
			//----------------------------------
			
			if ( ! xmlobj.readystate_ready_and_ok() )
			{
				// Could do a little loading graphic here?
				return;
			}
			
			//----------------------------------
			// INIT
			//----------------------------------
			
			var html = xmlobj.xmlhandler.responseText;
			
			if ( html == 'found' )
			{
				error_found += dname_error_taken + "<br />";
			}
			
			//----------------------------------
			// Show errors
			//----------------------------------
			
			if ( error_found )
			{
				ucp_dname_name.className         = input_red;
				ucp_dname_name_img.src           = ipb_var_image_url + '/' + img_cross;
				ucp_dname_warn_content.innerHTML = error_found;
				ucp_dname_warn.style.display     = 'block';
			}
			else
			{
				ucp_dname_name.className         = input_green;
				ucp_dname_name_img.src           = ipb_var_image_url + '/' + img_tick;
				ucp_dname_warn.style.display     = 'none';
				ucp_dname_warn_content.innerHTML = '';
			}
			
			error_found = '';
		};
		
		//----------------------------------
		// LOAD XML
		//----------------------------------
		
		xmlobj = new ajax_request();
		xmlobj.onreadystatechange( do_request_function );
		xmlobj.process( url );
	}
	else
	{
		//----------------------------------
		// Show errors
		//----------------------------------
		
		if ( error_found )
		{
			ucp_dname_name.className         = input_red;
			ucp_dname_name_img.src           = ipb_var_image_url + '/' + img_cross;
			ucp_dname_warn_content.innerHTML = error_found;
			ucp_dname_warn.style.display     = 'block';
		}
		else
		{
			ucp_dname_name.className         = input_green;
			ucp_dname_name_img.src           = ipb_var_image_url + '/' + img_tick;
			ucp_dname_warn.style.display     = 'none';
			ucp_dname_warn_content.innerHTML = '';
		}
		
		error_found = '';
	}
}

/*-------------------------------------------------------------------------*/
// EVENT: Check display name
/*-------------------------------------------------------------------------*/

function ucp_dname_form_check( event )
{
	//----------------------------------
	// DO pass fields
	//----------------------------------
	
	if ( ! ucp_dname_pass.value )
	{
		error_found += dname_error_no_pass + "<br />";
	}
	
	//----------------------------------
	// Check name
	//----------------------------------
	
	ucp_dname_check( event );
	
	if ( error_found )
	{
		ucp_dname_ok_to_go = 0;
	}
	else
	{
		ucp_dname_ok_to_go = 1;
	}
		
	//----------------------------------
	// Return
	//----------------------------------
	
	if ( ucp_dname_ok_to_go )
	{
		return true;
	}
	else
	{
		return false;
	}
}