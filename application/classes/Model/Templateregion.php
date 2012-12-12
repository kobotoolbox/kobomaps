<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Templateregion extends ORM {

	
	
	/**
	 * Set the name of the table
	 */
	protected  $_table_name = 'template_regions';
	
	/**
	 * A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many =  array(
	
	);
	
	protected $_has_one = array(
		'template' => array('model' => 'template'),
	);

	
	/**
	 * Rules function
	 * @see Kohana_ORM::rules()
	 */
	public function rules()
	{
		return array(
				'title' => array(
						array('not_empty'),
						array('max_length', array(':value', 254)),
						array('min_length', array(':value', 1))
				),
			
				'template_id' => array(array('not_empty'),),				
		);
	}//end function
	
	
	/**
	 * Update an existing template
	 *
	 * Example usage:
	 * ~~~
	 * $form = ORM::factory('Template', $id)->update_template($_POST);
	 * ~~~
	 *
	 * @param array $values
	 * @throws ORM_Validation_Exception
	 */
	public function update_template_region($values)
	{
	
		$expected = array('title', 'template_id');
	
		$this->values($values, $expected);
		$this->check();
		$this->save();
	}//end function

	
	

	
} // End User Model