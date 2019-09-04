<?php
include_once $controller->getController($pageToLoad);

?>
<form action="charcreate.php" novalidate method="post" onsubmit="return validateForm()">
	<input type="hidden" name="page" value="<?php echo $nextPage?>" />
  <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
	<?php echo $autoGrantText;?>
	<?php echo $outputHTML;?>
	<br>
<input type="submit" value="Continue" />
</form>
<table width="100%"><tr><td><p id="notify" class="errortext"></p></td></tr></table>

<script type="text/javascript">
function validateForm() {
    var groupIdArray = {};
    var outMessage = "";

    var groupValidateArray = [];
    <?php echo $jsGroupIdArray;?>
    // Loop through checkboxes in each group. Compare the number selected to the number required.

    for (var groupId in groupIdArray) {
        var validated = false;
        requiredSelections = groupIdArray[groupId];
        var numSelected = 0;
        var elName = "chkbox" + groupId;
        var allCheck = document.getElementsByClassName(elName);
        var specName = "spec" + groupId;
        var allSpec = document.getElementsByClassName(specName);

       // alert (" " + allCheck.length + " " + allSpec.length);
        for (var checkIndex = 0; checkIndex < allCheck.length; checkIndex++) {
            if (allCheck[checkIndex].type == "checkbox") {
                if (allCheck[checkIndex].checked == true) {
                    // Remove non-alphanumeric characters and check to make sure there is some value left.
                    var testStr = allSpec[checkIndex].value;
                    testStr = testStr.replace(/[^a-zA-Z0-9' ]+/g,'');
                    testStr = testStr.replace(/[']+/g,'&#39;');
                    testStr = testStr.trim();
                    allSpec[checkIndex].value = testStr;
                    if ((allSpec[checkIndex].getAttribute("required") == 1) && (testStr.length == 0)) {
                         outMessage += allSpec[checkIndex].getAttribute("assocskill") + " requires Specialization.<br>";
                    }
                    numSelected++;
                }
            }
        }
        if (numSelected == requiredSelections && outMessage == "") {
            validated = true;
        } else if (numSelected < requiredSelections) {
            outMessage += "Not enough skills selected. Select required amount from each group.<br>";
        } else if (numSelected > requiredSelections) {
            outMessage += "Too many skills selected. Select required amount from each group.<br>";
        }
        groupValidateArray.push(validated);
    }
    validated = true;
    for (var validateIndex = 0; validateIndex < groupValidateArray.length; validateIndex++) {
        if (!groupValidateArray[validateIndex]) {
            validated = false;
        }
    }
    document.getElementById("notify").innerHTML = outMessage;

    return validated;
}
function updateHelpLocal(clickedId) {
	var helpArray = {};
	<?php echo $jsHelpTextArray?>
	document.getElementById("helptext").innerHTML = helpArray[clickedId];
    return false;
}
</script>
