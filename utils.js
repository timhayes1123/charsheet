window.onload = scriptInit;
var helpArray = [];

function scriptInit() {

/* */
}

/**
* @param string elem  Id of the element for which we are referencing the value.
* @return void
*
**/

function updateHelp(elem) {
	var ddElement = document.getElementById(elem);
	var selectedId = ddElement.options[ddElement.selectedIndex].value;
	document.getElementById("helptext").innerHTML = helpArray[selectedId];
}
