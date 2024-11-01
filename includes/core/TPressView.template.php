<div id="loading_indicator">
	<img src="<?php echo TeamPress::instance()->getAdminThemeUrl() ?>images/ajax-loader.gif" />
</div>

<script type="text/javascript">
<!--
jQuery(function($) {
	$('#loading_indicator')
	    .hide()  // hide it initially
	    .ajaxStart(function() {
	        $(this).show();
	    })
	    .ajaxStop(function() {
	        $(this).hide();
	    });
});
//-->
</script>