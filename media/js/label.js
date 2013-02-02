// Define the overlay, derived from google.maps.OverlayView
function Label(opt_options) {
 // Initialization
 this.setValues(opt_options);
this.set('show_empty_name', true);
 // Label specific
 var labeldiv = this.labeldiv_ = document.createElement('div');
 labeldiv.setAttribute("class","countylabel");					  

 var div = this.figurediv = document.createElement('div');
 div.appendChild(labeldiv);
 div.style.cssText = 'position: absolute; display: none';
};
Label.prototype = new google.maps.OverlayView;

// Implement onAdd
Label.prototype.onAdd = function() {
 var pane = this.getPanes().overlayLayer;
 pane.appendChild(this.figurediv);

 // Ensures the label is redrawn if the text or position is changed.
 var me = this;
 this.listeners_ = [
   google.maps.event.addListener(this, 'position_changed',
       function() { me.draw(); }),
   google.maps.event.addListener(this, 'areaName_changed',
       function() { me.draw(); }),
   google.maps.event.addListener(this, 'areaValue_changed',
	   function() { me.draw(); })
	 ];
};

// Implement onRemove
Label.prototype.onRemove = function() {
 this.figurediv.parentNode.removeChild(this.figurediv);

 // Label is removed from the map, stop updating its position/text.
 for (var i = 0, I = this.listeners_.length; i < I; ++i) {
   google.maps.event.removeListener(this.listeners_[i]);
 }
};

// Implement draw
Label.prototype.draw = function() {
	var projection = this.getProjection();
 if(projection == undefined) //not sure why this happens, but for some reason we need to gaurd against it.
 {
	 return;
 }
 var position = projection.fromLatLngToDivPixel(this.get('position'));

 var div = this.figurediv;
 div.style.left = position.x + 'px';
 div.style.top = position.y + 'px';
 div.style.display = 'block';

 var areaValue = this.get('areaValue');
 var areaValueTxt = "";
 if (areaValue != undefined && areaValue != "" && areaValue != null)
{
	 areaValueTxt = '<div class="areaVal">'+areaValue+'</div>';
}
	  
 if(Label.renderLabelVals && Label.renderLabelNames){
	 if(areaValueTxt == "" && !this.get('show_empty_name')){
		 //don't draw empty name labels
		 //clear out past labels
		 this.labeldiv_.innerHTML = '';
	 }
	 else {
		 this.labeldiv_.innerHTML = areaValueTxt + '<div class="countylabelname">' + this.get('areaName').toString() + '</div>';
	 }
 }
 else if(Label.renderLabelVals){
	 this.labeldiv_.innerHTML = areaValueTxt;
 }
 else if(Label.renderLabelNames){
	 if(areaValueTxt == "" && !this.get('show_empty_name')){
		 //don't draw empty name labels
		 //clear out past labels
		 this.labeldiv_.innerHTML = '';
	 }
	 else {
		 this.labeldiv_.innerHTML = '<div class="countylabelname">' + this.get('areaName').toString() + '</div>';
	 }
 }
 else {
	 this.labeldiv_.innerHTML = '';
 }
};

//Used to global turn on or off the labels
Label.renderLabelNames = true;
Label.renderLabelVals = true;
