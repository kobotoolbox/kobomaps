<?php defined('SYSPATH') or die('No direct script access.');
/***********************************************************
* main.php - View
* This software is copy righted by Etherton Technologies Ltd. 2013
* Writen by John Etherton <john@ethertontech.com> Tino also did some work on this back in the day
* Started on 2013-01-23
* Show user their stats
*************************************************************/
?>
	
<h1> Work in progress</h1>

<?php echo __('Select Maps')?> <br/>
<?php echo Form::select('maps',$maps,null, array('id'=>'maps', 'multiple'=>'multiple'));?>

<p>Start Date: <input type="text" id="startDate" value="<?php 
		echo date('m/d/Y', time() - (24 * 60 * 60 * 30));
	?>"/>
</p>
<p>End Date: <input type="text" id="endDate" value="<?php echo date('m/d/Y', time());?>" /></p>
<input type="button" value="Submit" onclick="updateGraph()"/>
<div id="statChart" width="300px" height="300px"></div>