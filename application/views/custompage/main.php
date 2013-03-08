<?php defined('SYSPATH') or die('No direct script access.');
/***********************************************************
* main.php - View
* This software is copy righted by Etherton Technologies Ltd. 2013
* Writen by Dylan Gillespie
* Started on 2013-01-23
* Show user their stats
*************************************************************/
?>


<h2><?php echo __('Create your own custom HTML page')?></h2>


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
	echo Form::open(NULL, array('id'=>'edit_custompage_form', 'enctype'=>'multipart/form-data'));
	echo Form::hidden('action','edit', array('id'=>'action'));
	
	echo '<div id="pageTable" style="float:left; width:200px; height:500px;">';
	echo Form::label('page_descr', __('This is the list of your current pages.'));
	echo '</br></br>';
	echo Form::select('pages', $pages, $data['id'], array('id'=>'pages', 'style' => 'width: 175px; height: 22px'));
	
	echo '</div>';
	
	echo '<div id="customWork" style="width: 700px; float:right">';
	
	echo __('Title of page: ');
	echo Form::input('slug', $data['slug'], array('id'=>'slug', 'style'=>'width:300px;', 'maxlength' => '128', 'onchange' => 'checkSlug()'));
		
	echo '</br></br>';
	echo __('Content of page: This is what will be displayed on the page. There are advanced options available.');
	echo '</br>'; 
	echo Form::textarea('content', $data['content'], array('id'=>'htmlContent', 'style' => 'height: 650px', 'class' => 'tinymce'));
	echo '</br>';
	echo Form::submit('edit', __('Save'), array('id'=>'edit_button'));
	echo '<div id ="delete_button" style ="float:right">'.__('Delete').'</div>';
	echo '</div>';

	echo Form::close();
	
?>

<div style="clear:both"></div>