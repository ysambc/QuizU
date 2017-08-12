<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

class multipq_front_ajax_controller_model extends multipq_main_model{
	/**
	* This class is the Ajax Controller for the frontend shortcode of multipq.
	*/

	public function __construct($mpq_id, $path = NULL, $current_question = NULL, $linked_question = NULL){/*Default value assignment. Requests Quiz ID for construction*/
		
		parent::__construct($mpq_id, $path);

		$this->current_question = $current_question;
		$this->next_question = $linked_question;
		$this->path = $path;

	}
}