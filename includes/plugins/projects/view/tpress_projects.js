/**
 * Create a project and if successfull, redirect to that project's home page
 * 
 * @param $ jQuery instance
 * @param name Name of the project (required)
 * @param description Description for the project
 * @returns
 */
function createProject( $, name, description ) {
	var params = {
			action : 'tpress_create_project',
			project_name: name,
			project_description: description
		};
	
	$.ajax({
			url: ajaxurl,
			data: params, 
			dataType: 'JSON',
			success: function(response) {
				 alert(response);
			},
			error: function(errorThrown) {
				 alert('error');
				 console.log(errorThrown);
			}
	}); 
}

/** 
 * Show a confirmation dialog for an action
 * 
 * @param $ jQuery object
 * @param dialogTitle title
 * @param dialogText message
 * @param callback A function to execute if the user presses the OK button
 * @returns {Boolean}
 */
function showConfirmDialog($, dialogTitle, dialogText, callback, okButtonText, cancelButtonText) {
    $('body').append('<div id="confirm_dialog" style="display: none;">' + dialogText + '</div>');    
    $('#confirm_dialog').dialog({
        draggable: false,
        modal: true,
        resizable: false,
        width: 'auto',
        title: dialogTitle || 'Confirm',
        minHeight: 75,
        buttons:[
                  {
                	  text: okButtonText || 'OK',
                	  click: function() {
                		  $(this).dialog("close");
                		  if ($.isFunction(callback)) {   
                			  callback.apply();
                		  }
                	  }
                  }, {
                	  text: cancelButtonText || 'Cancel',
                	  click: function() { $(this).dialog("close");}
                  }
        ],
        close: function(event, ui) { 
        	$('#confirm_dialog').remove();
        }
    });
    return false;
}