<?php defined('SYSPATH') or die('No direct script access.');
/***********************************************************
* Menuedit.php - Controller
* This software is copy righted by Kobo 2013
* Writen by Dylan Gillespie, Etherton Technologies <http://ethertontech.com>
* Started on 2013-03-22
* Creating custom menu items
*************************************************************/

class Controller_Menuedit extends Controller_Loggedin {

	
	public function before()
	{
		parent::before();	
		
		$auth = Auth::instance();
		//is the user logged in?
		if($auth->logged_in('admin'))
		{
			$this->session = Session::instance();
			//if auto rendered set this up
			if ($this->auto_render)
			{
				$data = array(
						'text' => '',
						'image_url' => '',
						'item_url' => '',
						'title' => '',
				);
				
				$custompage = ORM::factory('Custompage')->
				where('user_id', '=', $this->user->id)->
				find_all();
				
				$adminpages = ORM::factory('Custompage')->
				where('user_id', '=' , 1)->
				find_all();
				
				$pageSelector = array();
				$pageSelector[0] = __('None');
				$help = array(
					'custompagehelp' => 'custompagehelp',
					'maphelp' => 'maphelp',
					'templatehelp' => 'templatehelp',
					'submenuhelp' => 'submenuhelp',
					'__HELP__' => '__HELP__'
				);
				
				foreach($adminpages as $admin){
					if(!in_array($admin->slug, $help)){
						$pageSelector[$admin->id] = $admin->slug;
						if($admin->my_menu != 0){
							$m = ORM::factory('Menus')->
							where('id', '=', $admin->my_menu)->
							find();
							$data[$m->title.'pages'] = $admin->id;
						}
					}
				}
				foreach($custompage as $custom){
					$pageSelector[$custom->id] = $custom->slug;
					if($custom->my_menu != 0){
						$m = ORM::factory('Menus')->
						where('id', '=', $custom->my_menu)->
						find();
						$data[$m->title.'pages'] = $custom->id;
					}
				}

				$submenus = array();
				$menus = array();
				//these should not be able to be changed by the admins, pages yes, menu no
				
				$m = ORM::factory('Menus')
				->find_all();
				
				foreach($m as $main){
					$sub = ORM::factory('Menuitem')->
					where('menu', '=', $main->id)->
					find_all();
					//guarantees all menus are put through
					if($main->title != 'help'){
						$submenus[$main->title] = array();
						$menus[$main->id] = $main->title;
					}
					foreach($sub as $s){
						if($main->title != 'help'){
							$submenus[$main->title][$s->id] = $s;
							$data[$s->id.'admin_only'] = $s->admin_only;
						}
					}
				}
				
			
			}
		}
	}
	/**
	* where users go to edit custom menus
	*/
	public function action_index()
	{
		$auth = Auth::instance();
		//only admins should be allowed to see the page in the first place, and if not, are redirected to mymaps
		if(!$auth->logged_in('admin'))
		{
			HTTP::redirect('mymaps');
		}
		
		$this->template->content = new View('menuedit/main');
		$this->template->content->errors = array();
		$this->template->content->messages = array();
		

		if(!empty($_POST)){
			
			$action = $_POST['action'];
			switch($action){
				case 'delete_sub_menu':
					$submenu_id = $_POST['submenu_id'];
					$submenu = ORM::factory('Menus', $submenu_id);
					$submenu->delete();
					break;
					
				case 'edit_submenu':
					$submenu_id = $_POST['submenu_id'];
					$submenu = ORM::factory('Menus', $submenu_id);
					$submenu->update_menu($_POST);
					break;
							
			}
			
			
			
			
			
			//save a new menu
			if($_POST['action'] == 'saveMenu'){
								
				//check for titles being the same in the database
				$other = ORM::factory('Menus')->
				where('title', '=', $_POST['title'])->
				find();
				
				if(!$other->loaded()){
					$menu = ORM::factory('Menus');
					$menu->title = $_POST['title'];
					$menu->save();
					
					//update the message
					$this->template->content->messages[] = __('Saved menu ').$menu->title;
				}
				else{
					$this->template->content->errors[] = $_POST['title'].' '.__('already exists.');
				}
			}
			//new submenu
			if($_POST['action'] == 'saveSub'){
				$menu = ORM::factory('Menus')->
				where('id', '=', $_POST['submenu_menu'])->
				find();
				
				$sub = ORM::factory('Menuitem')->
				where('text', '=', $_POST['text'])->
				find();
				
				if(!$sub->loaded()){
					//load the image
					$sub->text = $_POST['text'];
					$sub->item_url = $_POST['item_url'];
					$sub->menu = $menu->id;
					$sub->admin_only = isset($_POST['admin_only']) ? 1 : 0;
					//save the item so that the database gives it an ID number
					$sub->save();
	
					$_POST['image_url'] = $_FILES['file']['name'];
					if($_FILES['file']['name'] != '')
					{
						$filename = $this->_save_file($_FILES['file'], $menu->title, $sub->id);
					}
						
					
					if($filename !== false){
						$sub->image_url = $filename;
					}
					$sub->save();
					
					$this->template->content->data[$sub->id.'admin_only'] = $sub->admin_only;
					$this->template->content->submenus[$menu->title][$sub->id] = $sub;
					$this->template->content->messages[] = __('Saved submenu').' '.$sub->text;
					
				}
			}
			//editing the menus
			if($_POST['action'] == 'saveAll'){
				$menus = ORM::factory('Menus')->find_all();
				$subs = ORM::factory('Menuitem')->find_all();
				$my_menu_array = array();
				
				foreach($menus as $m){
					if(array_key_exists($m->title.'delete', $_POST)){
						unset($this->template->content->submenus[$m->title]);
						unset($this->template->content->menus[$m->id]);
						$this->template->content->messages[] = __('Deleted menu').' '.$m->title;
						$m->delete();
					}
					else if(array_key_exists($m->title.'pages', $_POST)){
						$page = $_POST[$m->title.'pages'];
						$custompage = ORM::factory('Custompage')->
						where('id', '=', $page)->
						find();
						
						if($page != 0 AND in_array($page, $my_menu_array) != false){
							
							$this->template->content->errors[] = __('A page cannot have more than one menu. 
										Page used more than once:').$custompage->slug;
							return;
						}
						else if($page != 0){
							$my_menu_array[] = $page;
							$custompage->my_menu = $m->id;
							$custompage->save();
							$this->template->content->data[$m->title.'pages'] = $custompage->id;
						}
						/*else if($page == 0){
							unset($custompage->my_menu);
							print_r($custompage);
							
							$custompage->save();
							unset($this->template->content->data[$m->title.'pages']);
						}*/
					}
				}
				
				foreach($subs as $s){
					if(array_key_exists($s->id.'delete', $_POST)){
						$menu = ORM::factory('Menus', $s->menu);
						$this->template->content->messages[] = __('Deleted menuitem').' '.$s->text;
						unset($this->template->content->submenus[$menu->title][$s->id]);
						$s->delete();
					}
					else if(array_key_exists($s->id.'admin_only', $_POST)){
						$value = ($_POST[$s->id.'admin_only'] == 'on') ? 1 : 0;
						
						$this->template->content->data[$s->id.'admin_only'] = $value;
						$s->admin_only = $value;
						$s->save();
					}
					else{
						$s->admin_only = 0;
						$s->save();
					}
				}
			}
			
		}
		$submenus = ORM::factory('Menus')->find_all();
		$this->template->content->submenus = $submenus;
		$this->template->html_head->script_views[] = view::factory('js/messages');
		$this->template->html_head->script_files[] = 'media/js/jquery.tools.min.js';
		$this->template->header->menu_page = "custompage";
		//make messages roll up when done
		$this->template->html_head->messages_roll_up = true;		
		$this->template->html_head->title = __("Menus Page");
		$this->template->html_head->script_views[] = new View('menuedit/main_js');

		
	}//end action_index
	
		 
	
	public function action_edit_item()
	{
		
	}



	//call the generic slug checker function
	public function action_checkslug(){
		$this->auto_render = false;
		$this->response->headers('Content-Type','application/json');
		
		if(!isset($_POST['slug'])){
			echo '{}';
			exit;
		}
		if($_POST['id'] == 0){
			$db_obj = ORM::factory('Menuitem');
		}
		else {
			$db_obj = ORM::factory('Menuitem')->where('id', '=', $_POST['id'])->find();
		}
		Helper_Slugs::check_slug($_POST['slug'], $db_obj);
	}
	
	/**
	 * Grabs the file extention of a file
	 * @param string $file_name name of the file
	 * @return the exention of the file. So for 'about.txt' this function would return 'txt'
	 */
	protected function get_file_extension($file_name) {
		return substr(strrchr($file_name,'.'),1);
	}
	
	/**
	 * Saves a file from the temp upload area to the hard disk
	 * @param array $upload_file the $_FILES['<name>'] array for the given file
	 * @param String $title Title of the menu that the file is for
	 * @param int $id id of the menuitem associated with it
	 * @return boolean or filename
	 */
	protected function _save_file($upload_file, $title, $id)
	{

		if (
				! Upload::valid($upload_file) OR
				! Upload::not_empty($upload_file) OR
				! Upload::type($upload_file, array('png', 'jpeg', 'bmp', 'jpg')))
		{
			return FALSE;
		}
	
		$directory = DOCROOT.'uploads/images/';
	
		$extention = $this->get_file_extension($upload_file['name']);
		$filename = $title.'-'.$id.'.'.$extention;
		 
		if ($file = Upload::save($upload_file, $filename, $directory))
		{
			return URL::base(TRUE,TRUE).'uploads/images/'.$filename;
		}
	
		return FALSE;
	}
	
}//end of class