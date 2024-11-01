function handleAjaxJsonResponse( json, followRedirect ) {
	followRedirect = typeof followRedirect !== 'undefined' ? followRedirect : true;
	
	if (json==null) {
		alert( "Request time out" );
		return true;
	}
	
	if ( false==json.success ) {
		alert( json.error );
		return true;
	}
	
	if ( true==json.success && followRedirect && json.data!=null && json.data.redirect!=null ) {
		window.location.href = json.data.redirect;
		return true;
	}
	
	return false;
}