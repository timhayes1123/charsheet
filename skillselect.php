<?php
include_once $controller->getController($pageToLoad);
?>

<form action="charcreate.php" novalidate method="post" onsubmit="return sanitizeText()">
  <input type="hidden" name="page" value="skillselect" />
  <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
  <input type="hidden" name="reprocess" value="true" />
<?php if ($tableRow != "") { ?>
  <table width="100%">
      <tr><th colspan="3">Race Bonus Skills</th></tr>
      <tr><th>Name</th><th>Specialization</th><th>Level</th></tr>
      <?php echo $tableRow;?>
  </table>
<?php } ?>
  <table width="100%">
      <tr><th>Selected Skills (<?php echo $slotsUsed;?> / <?php echo $slotsAvail;?>)</th></tr>
      <?php echo $selectRow;?>
      <tr><td><input type="submit" name="formprocess" value="Remove"></td></tr>
  </table>
  <table width="100%">
      <tr><th>Available Skills</th></tr>
      <tr><th><p id="skillattr"><?php echo $currentDisplay;?></p><input type="hidden" name="currentattr" id="currentattr" value="<?php echo $currentAttr;?>"></th></tr>
      <?php echo $availableSelectHTML;?>
      <tr><td>
          <table width="100%">
              <tr><td>Category Selection</td><td colspan="2"><input type="submit" <?php echo $disabled;?> name="formprocess" value="Add"></td></tr>
              <tr><td><input type="button" value="<" onclick="navigate('<')"><input type="button" value=">" onclick="navigate('>')"></td>
                  <td>Specialization:</td><td><input type="text" name="spectext" id="spectext" value=""></td></tr>
          </table>
      </td></tr>
  </table>
</form>
<form action="charcreate.php" novalidate method="post">
  <input type="hidden" name="page" value="<?php echo $nextPage?>" />
  <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
  <table width="100%">
      <tr><td><p class="maintext">You must select a minimum of <?php echo $minSkills?> Skills before proceeding. You are not required to
              fill all Skill Slots, but it is advisable that you do as it costs Skill Points to purchase them later.</p>
      </td></tr>
      <tr><td><input type="submit" <?php echo $proceedDisabled?> value="Proceed"></td></tr>
  </table>
</form>
<table width="100%"><tr><td><p id="notify" class="errortext"></p></td></tr></table>

<script type="text/javascript">
var attrArray = [];
var lookupArray = {};
var optionArray = {};

<?php echo $jsAttrArray;?>
<?php echo $jsOptionHTML;?>
<?php echo $jsLookupArray;?>
<?php echo $jsHelpArray;?>


document.getElementById("notify").innerHTML = "<?php echo $errMessage;?>";

function sanitizeText() {
    var textbox = document.getElementById("spectext");
    var specStr = textbox.value;
    specStr = specStr.replace(/[^a-zA-Z0-9' ]+/g,'');
    specStr = specStr.replace(/[']+/g,'&#39;');
    specStr = specStr.trim();
    document.getElementById("spectext").value = specStr;
}

function navigate(direction) {
    var newAttr = "";
    var currentAttr = document.getElementById("currentattr").value;
    var currentPos = attrArray.indexOf(currentAttr);
    var arrayLength = attrArray.length;
    if (direction == "<") {
        if (currentPos == 0) {
            currentPos = arrayLength - 1;
        } else {
            currentPos--;
        }
    } else {
        if (currentPos == arrayLength - 1) {
            currentPos = 0;
        } else {
            currentPos++;
        }
    }
    newAttr = attrArray[currentPos];
    document.getElementById("currentattr").value = newAttr;
    document.getElementById("skillattr").innerHTML = lookupArray[newAttr];

    var selectbox = document.getElementById("availableskills");
    var selectIndex;
    for(selectIndex = selectbox.options.length - 1 ; selectIndex >= 0 ; selectIndex--) {
        selectbox.remove(selectIndex);
    }
    if (!optionArray[newAttr]) {
        navigate(direction);
    } else {
        selectbox.innerHTML = optionArray[newAttr];
    }
}


</script>
