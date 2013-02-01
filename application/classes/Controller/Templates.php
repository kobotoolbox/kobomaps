<?php defined('SYSPATH') or die('No direct script access.');
/***********************************************************
* Templates.php - Controller
* This software is copy righted by Kobo 2012
* Writen by John Etherton <john@ethertontech.com>, Etherton Technologies <http://ethertontech.com>
* Started on 2012-11-08
*************************************************************/

class Controller_Templates extends Controller_Loggedin {

	
	/**
	 Set stuff up, mainly just check if the user is an admin or not
	 */
	public function before()
	{
		parent::before();
	
	
		$this->is_admin = false;
		
		//see if the given user is an admin, if so they can do super cool stuff
		$admin_role = ORM::factory('Role')->where("name", "=", "admin")->find();
		if($this->user->has('roles', $admin_role))
		{
			$this->is_admin = true;
		}
	}
	

  	
	/**
	where users go to change their profiel
	*/
	public function action_index()
	{
		
	
		/***** initialize stuff****/
		//The title to show on the browser
		$this->template->html_head->title = __("Templates");
		//make messages roll up when done
		$this->template->html_head->messages_roll_up = true;
		//the name in the menu
		$this->template->header->menu_page = "templates";
		$this->template->content = view::factory("templates/templates");
		$this->template->content->errors = array();
		$this->template->content->messages = array();
		//set the JS
		$js = view::factory('templates/templates_js');
		$this->template->html_head->script_views[] = $js;
		$this->template->html_head->script_views[] = view::factory('js/messages');
		$this->template->content->is_admin = $this->is_admin;
		
		/********Check if we're supposed to do something ******/
		if(!empty($_POST)) // They've submitted the form to update his/her wish
		{
			try
			{	
				if($_POST['action'] == 'delete')
				{
					//make make sure this user has the rights to do this
					$template = ORM::factory('Template',$_POST['template_id']);
					if($this->is_admin OR $template->user_id = $this->user->id)
					{
						Model_Template::delete_template($_POST['template_id']);
						$this->template->content->messages[] = __('Template Deleted') . ' - ' . $template->title;
					}
				}
			}
			catch (ORM_Validation_Exception $e)
			{
				$errors_temp = $e->errors('register');
				if(isset($errors_temp["_external"]))
				{
					$this->template->content->errors = array_merge($errors_temp["_external"], $this->template->content->errors);
				}				
				else
				{
					foreach($errors_temp as $error)
					{
						if(is_string($error))
						{
							$this->template->content->errors[] = $error;							
						}
					}
				}
			}	
		}
		
		/*****Render the forms****/
		
		//if you're an admin and you can do whatever you want then you see all templates
		$maps = ORM::factory("Template")
			->select('users.username')
			->join('users')
			->on('users.id','=','template.user_id');
		if($this->is_admin)
		{							
		}
		else //you're a regular user and can only see your own templates
		{
			$maps = $maps->where('user_id','=',$this->user->id);			
		}
		$maps = $maps->order_by('title', 'ASC')
			->find_all();
		
		$this->template->content->maps = $maps;
		
		
	}//end action_index
	
	
	
	/**
	 * the function for editing a form
	 * super exciting
	 */
	 public function action_edit()
	 {
		//initialize data
		$data = array(
			'id'=>'0',
			'title'=>'',
			'description'=>'',
			'file'=>'',
			'admin_level'=>0,
			'decimals'=>-1,
			'lat'=>'',
			'lon'=>'',
			'zoom'=>4,
			'regions'=>array());
		
		$template = null;
		
		//check if there's a id
		if(isset($_GET['id']) AND intval($_GET['id']) != 0)
		{
			$template = ORM::factory('Template', $_GET['id']);		
		}
		
		//TODO write code to hanlde a user editing this once it's been set		
		 
		
		/***Now that we have the form, lets initialize the UI***/
		//The title to show on the browser
		
		$header =  $data['id'] == 0 ? __("Add a Template") : __("Edit Template") ;
		$this->template->html_head->title = $header;		
		//make messages roll up when done
		$this->template->html_head->messages_roll_up = true;
		//the name in the menu
		$this->template->header->menu_page = "templates";
		$this->template->content = view::factory("templates/template_add");
		
		$this->template->content->errors = array();
		$this->template->content->messages = array();
		$this->template->content->header = $header;		
		$this->template->html_head->script_views[] = view::factory('js/messages');
		$js = view::factory('templates/template_add_js');
				
		//get the status
		$status = isset($_GET['status']) ? $_GET['status'] : null;
		if($status == 'saved')
		{
				$this->template->content->messages[] = __('changes saved');
		}
		
		/******* Handle incoming data*****/
		if(!empty($_POST)) // They've submitted the form to update his/her wish
		{
		
			try
			{
				$first_time = false; //used to know if we should blow away everything if there's an error
				//Should we use the old file
				if($_FILES['file']['size'] == '0' AND $template != null)
				{
					$_POST['file'] = $template->file;
				}
				else
				{
					$_POST['file'] = $_FILES['file']['name'];
				}
				//should we make a new template?
				if($template == null)
				{				
					$template = ORM::factory('Template');
					$first_time = true;
				}
				else
				{
					//we shouldn't make a new template, but we should update regions
					foreach($_POST['regions'] as $r_id => $r_title)
					{
						$region = ORM::factory('Templateregion', $r_id);
						$region->title = $r_title;
						$region->save();
					}
				}
				$_POST['user_id'] = $this->user->id;
				//if they aren't an admin, then they can't set officialness
				if(!$this->is_admin)
				{
					unset($_POST['is_official']);
				}
				$template->update_template($_POST);
				
			
				if($_FILES['file']['size'] != '0')
				{
					
					//handle the kml file
					$filename = $this->_save_file($_FILES['file'], $template);
					if(is_array($filename))
					{					
						if($first_time)
						{
							Model_Template::delete_template($template->id);
						}
						throw new UTF_Character_Exception($filename['error']);
					}
					
					$template->file = $filename;					
				}
				else //we're editing an existing template and not changing the base file
				{
					$filename = $this->_save_file(null, $template);
				}
				$template->save();
				HTTP::redirect('/templates?status=saved');				
				
			}
			catch (ORM_Validation_Exception $e)
			{
				$errors_temp = $e->errors('register');
				if(isset($errors_temp["_external"]))
				{
					$this->template->content->errors = array_merge($errors_temp["_external"], $this->template->content->errors);
				}				
				else
				{
					foreach($errors_temp as $error)
					{
						if(is_string($error))
						{
							$this->template->content->errors[] = $error;							
						}
					}
				}
			}
			catch (UTF_Character_Exception $e)
			{
				$this->template->content->errors[] = $e->getMessage();
				$data['id'] =  $_POST['id'];
				$data['title'] =  $_POST['title'];
				$data['description'] =  $_POST['description'];
				$data['admin_level'] =  $_POST['admin_level'];
				$data['decimals'] =  $_POST['decimals'];
				$data['zoom'] =  $_POST['zoom'];
				$data['lat'] =  $_POST['lat'];
				$data['lon'] =  $_POST['lon'];
			}	
		}
		if(isset($_GET['id']) AND intval($_GET['id']) != 0)
		{
			$data['id'] =  $template->id;
			$data['title'] =  $template->title;
			$data['description'] =  $template->description;
			$data['file'] =  $template->file;
			$data['admin_level'] =  $template->admin_level;
			$data['decimals'] =  $template->decimals;
			$data['zoom'] =  $template->zoom;
			$data['lat'] =  $template->lat;
			$data['lon'] =  $template->lon;
			
			$regions = ORM::factory('Templateregion')
				->where('template_id', '=', $template->id)
				->find_all();
			foreach($regions as $r)
			{
				$data['regions'][$r->id] = $r->title;
			}
		}
		$this->template->content->data = $data;
		$js->template = $template;			
		$this->template->html_head->script_views[] = $js;
	 }//end action_add1
	 
	 

	 protected function _save_file($upload_file, $template)
	 {
	 	//if we're working with a file that's already been uploaded.
	 	//Happens when a user is editing an existing template
	 	if($upload_file == null AND $template->kml_file != null)
	 	{
	 		$filename = $template->kml_file;
	 		$json_file = Helper_Kml2json::convert($filename, $template);	 		
	 		return $json_file;
	 	}
	 	//Now deal with the case whe we're creating a new templae and just uploaded a file
	 	else
	 	{	 		
		 	if (! Upload::valid($upload_file) OR ! Upload::not_empty($upload_file))
		 	{
		 		return array('error'=>__('uploaded file is not valid'));
		 	}
		 	if(! Upload::type($upload_file, array('kml', 'kmz')))
		 	{
		 		return array('error'=>__('This is not a .kml or .kmz file'));
		 	}
		 
		 
		 	$directory = DOCROOT.'uploads/templates/';
		 
		 	$extention = $this->get_file_extension($upload_file['name']);
		 	$filename = $template->id.'.'.$extention;
		 	$template->kml_file = $filename;
		 	if ($file = Upload::save($upload_file, $filename, $directory))
		 	{	 			 
		 		$json_file = Helper_Kml2json::convert($filename, $template);
		 		return $json_file;
		 	}
		 
		 	return array('error'=>__('Something has gone wrong processing your template map file'));
	 	}
	 }
	 
	 function get_file_extension($file_name) {
	 	return substr(strrchr($file_name,'.'),1);
	 }
	
	
}//end of class



class UTF_Character_Exception extends Exception
{
	

}