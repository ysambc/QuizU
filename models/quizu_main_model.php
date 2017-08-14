<?php 

defined('ABSPATH') or die("Cannot access pages directly.");

/**
* quizu base class
*
* This class defines the main Quiz object for Ultimeatequizu
* quiz managing and editing.This object is a template for 
* both the admin ajax contrller object, and the frontend
* ajax controller object.
*
*/

class quizu_main_model
{
	/**
	* This base class sets the variables that will be needed for querying the quizes.
	* Quiz ID, title, content, questions, and parent path are all setup here.
	* Both the Quiz ID and the parent path are retrieved from POST queries.
	*/

	public function __construct($quizu_id, $path = NULL){/*Default value assignment. Requests Quiz ID for construction*/
		
		$this->quizu_id = $quizu_id;/*Get the Quiz ID from POST*/
		$this->quizu_title = get_the_title($quizu_id);/*Get the Title from POST*/
		$this->quiz_content = get_post_field('post_content', $this->quizu_id);/*Get the Content from POST*/
		$this->path = $path;/*Get the Quiz' parent Path from POST. If no path is set in POST, this will fallback to 'default'*/

		$this->field = '_quizu_questions';/*This is the name of the custom field where the questions are going to be stored inside the Quiz type post. Defined for ease*/
		$this->Rfield = '_quizu_results';/*This is the name of the custom field where the questions are going to be stored inside the Quiz type post. Defined for ease*/

		if (get_post_meta($this->quizu_id, $this->field, true) == '') {/*IF 'questions' custom field is empty, assign an empty array to this object's 'all_questions' property*/
			$this->all_questions = array();
		}else{/*If it is not empty, restrieve questions array from custom field*/
			$this->all_questions = get_post_meta($this->quizu_id, $this->field, true);
		}

		if (get_post_meta($this->quizu_id, $this->Rfield, true) == '') {/*Repeat the same logic of questions for the Quiz's results*/
			$this->all_results = array();
		}else{
			$this->all_results = get_post_meta($this->quizu_id, $this->Rfield, true);
		}
	}
}