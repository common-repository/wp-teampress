<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressAjaxResult' ) ) :


class TPressAjaxResult {

	public function __construct() {
	}
	
	public function setSuccess( $data ) {
		$this->success = true;
		$this->data = $data;
		return $this;
	}
	
	public function setFailure( $message ) {
		$this->success = false;
		$this->error = $message;
		return $this;
	}
	
	public function getAsJson() {
		return json_encode( $this );
	}
	
	public static function getFailureResult( $message ) {
		$res = new TPressAjaxResult();
		return $res->setFailure( $message )->getAsJson();
	}
	
	public static function getSuccessResult( $data ) {
		$res = new TPressAjaxResult();
		return $res->setSuccess( $data )->getAsJson();
	}

	/** Instance variables *******************************************************************************************/
	
	public $success;
	public $error;
	public $data;
}

endif; // class_exists