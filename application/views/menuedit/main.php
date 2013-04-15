<?php defined('SYSPATH') or die('No direct script access.');
/***********************************************************
* main.php - View
* This software is copy righted by Etherton Technologies Ltd. 2013
* Writen by Dylan Gillespie
* Started on 2013-03-22
* Show menus to edit
*************************************************************/
?>


<h3><?php echo __('Create your own custom submenus')?></h3>


<?php if(count($errors) > 0 )
{
?>
	<div class="errors">
	<?php echo __("error"); ?>
		<ul>
<?php 
	foreach($errors as $error)
	{
?>
		<li> <?php echo $error; ?></li>
<?php
	} 
	?>
		</ul>
	</div>
<?php 
}
?>

<?php if(count($messages) > 0 )
{
?>
	<div class="messages">
		<ul>
<?php 
	foreach($messages as $message)
	{
?>
		<li> <?php echo $message; ?></li>
<?php
	} 
	?>
		</ul>
	</div>
<?php 
}
?>

<?php 
	echo Form::open(NULL, array('id'=>'edit_menu_form', 'enctype'=>'multipart/form-data'));
	echo Form::hidden('action','edit_submenu', array('id'=>'action'));
	echo Form::hidden('submenu_id','0', array('id'=>'submenu_id'));
	echo Form::hidden('submenu_item_id','0', array('id'=>'submenu_item_id'));
	
	
  
  echo '<div id="menuEdit" style="width:830px; height 500px;">';
  ?>
  <div class="scroll_table">
	  <table class="list_table" style="width:824px; height:400px">
	  <thead>
	  <tr class="header">
	  			<th class="menuName" style="width:80px">
	  				<?php echo __('Submenu');?>
	  			</th>
	  			<th class="menuItems" style="width:641px">
	  				<?php echo __('Items');?>
	  			</th>
	  			<th class="menuDelete" style="width:58px">
	  				<?php echo __('Actions')?>
	  			</th>
	  			
	  		</tr>
	  	</thead>
	  	<tbody style="height: 360px">
	  	<?php
	  		if(count($submenus) == 0)
	  		{
	  			echo '<tr><td colspan="4" style="width:880px;text-align:center;">'.__('You have no menus').'</td></tr>';
	  		}
	  		$i = 0;
	  		foreach($submenus as $submenu){
				$i++;
	  			$odd_row = ($i % 2) == 0 ? 'class="odd_row"' : '';
	  		?>
	  
	  	<tr <?php echo $odd_row; ?>>
	  		<td class="menuName" style="width: 80px">
	  			<?php echo $submenu->title;
	  			?>
	  		</td>
	  		<td class="menuItems" style="width:641px">
	  			<ul>
	  			<?php 
	  				$submenu_items = $submenu->menu_items->find_all();
	  				foreach($submenu_items as $submenu_item){
					?>
						<li>
							<a href="/kobomaps/<?php echo $submenu_item->item_url?>">
								<div>
	            					<img class="customMenus" src="<?php echo $submenu_item->image_url?>" width="50" height="50"/><br/><?php echo $submenu_item->text;?>
	            					</br></a>
	            					<?php 
	            						echo __('Admin only'). ' '. Form::checkbox('admin_only_'.$submenu_item->id, null, 1==$submenu_item->admin_only, array('title' => __('Admin only?'), 'class' => 'admin_box'));
	            						echo '<br/>';
	            						echo __('Delete'). ' '. Form::checkbox('delete_'.$submenu_item->id, null, 0, array('title' => __('Delete'), 'class' => 'delete_box'));
	            					?>
	          					</div>
      					</li>
					<?php }	
	  			?>
	  			</ul>
	  		</td>	 
	  		<td class="menuDelete" style="width:59px; text-align:center;">
	  			<a href="#" onclick="deleteSubMenu(<?php echo $submenu->id; ?>);"><?php echo __('Delete');?></a>
	  			<br/>
	  			<a href="#" onclick="editSubMenu(<?php echo $submenu->id; ?>, '<?php echo str_replace('\'', '\\\'', $submenu->title); ?>'); return false;"><?php echo __('Edit');?></a>
	  			<br/>
	  			<a rel="#overlay" href="<?php echo url::base().'menuedit/edit_item?m_id='.$submenu->id; ?>"><?php echo __('Add Item');?></a>
	  		</td> 		
	  	</tr>
	  	<?php }?>
	  	</tbody>
	  </table>
  </div>
  </br>
  <?php 
  
  
  echo '</br></br>';
  
  /*********** Create menu ***************/
  echo '<div id="createMenu" style="float:left; width: 300px">';
  echo '<table style="width:330px"><tr><td><strong>';
  echo Form::label('menuCreate', __('Create a new menu.'));
  echo '</strong</td><td></td></tr><tr><td>';
  echo Form::label('title', __('Name of Menu'.':'));
  echo '</td><td>';
  echo Form::input('title', '', array('id'=>'title', 'style'=>'width:150px'));
  echo '</td></tr><tr><td>';
  echo Form::submit('submit', __('Submit'));
  echo '</td></tr></table>';
  echo '</div>';
  
  
  echo Form::close();
  ?>


<div style="clear:both"></div>

<div class="apple_overlay" id="overlay">
		<div class="contentWrap">
			<img class="contentWrapWaiter" src="<?php echo URL::base();?>media/img/waiter_barber.gif"/>
		</div>
</div>