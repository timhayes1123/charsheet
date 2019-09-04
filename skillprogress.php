<?php
include_once "SkillCollection.php";

include_once $controller->getController($pageToLoad);
?>

<form action="charcreate.php" name="progressskills" id="progressskills" novalidate method="post">
  <input type="hidden" name="nextrank" value="<?php echo ++$actualRank;?>" />
  <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
  <input type="hidden" id="page" name="page" value="skillprogress" />
  <table width="100%" class="inner">
      <tr>
          <th>Skill</th><th>Specialized Field<br>(if any)</th><th>Specialization or Advanced</th><th>Skill Level</th>
      </tr>
      <tr>
          <th colspan="3">Rank: <?php echo $rankLabel;?></th>
          <th><p id="pointsremaining">Points Remaining: <input type="text" id="ptsAvail" name="ptsAvail" readonly="readonly" size="4" value="<?php echo $skillPoints?>"></p></th>
          <?php echo $skillRowHTML;?>
      </tr>
      <tr>
        <td colspan="3">
          <input id="rankraise" type="submit" value="Raise Rank" <?php echo $raiseRankDisabled;?>>
          <input id="addSkillBtn" type="button" value="Add Skill" <?php echo $addBtnDisabled;?>>
          <input id="nextpage" type="button" value="Finish Skills" onclick="sendForm()" disabled >
        </td>
      </tr>
  </table>
  <table width="100%"><tr><td><p id="notify" class="errortext"></p></td></tr></table>
</form>

  <div id="addSkillModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
      <form action="charcreate.php" novalidate method="post" onsubmit="getSkillLevels()">
      <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
      <input type="hidden" id="page" name="page" value="skillprogress" />
      <input type="hidden" name="skilllevels" id="skilllevels" value="" />
      <input type="text" id="ptsAvailAdd" name="ptsAvail" readonly="readonly" size="4" value="">
      <table width="100%">
        <tr><th>Available Skills</th></tr>
        <tr><th><p id="skillattr"><?php echo $currentDisplay;?></p><input type="hidden" id="currentattr" value="<?php echo $currentAttr;?>"></th></tr>
        <?php echo $availableSelectHTML;?>
          <tr><td>
              <table width="100%">
                <tr>
                  <td><input type="radio" name="skilltype" onChange="showRequire('')" value="" checked>Basic</td>
                  <td><input type="radio" id="radio2" name="skilltype" onChange="showRequire('S')" value="S">Specialization</td>
                  <td><input type="radio" id="radio3" name="skilltype" onChange="showRequire('A')" value="A">Advanced</td>
                </tr>
                <tr><td colspan="3"><hr></td></tr>
                <tr><td>Category Selection</td><td colspan="2">&nbsp;</td></tr>
                <tr><td><input type="button" value="<" onclick="navigate('<')"><input type="button" value=">" onclick="navigate('>')"></td>
                  <td>Specialization:</td><td><input type="text" name="spectext" id="spectext" value=""></td></tr>
                <tr>
                  <td>&nbsp;</td><td><p class="errortext" id="requirenote"></p></td><td><input type="submit" value="Add" ><input type="button" value="Cancel" onclick="modalClose()"></td>
                </tr>
              </table>
          </td></tr>
      </table>

      <input type="hidden" value="add" name="process">
      </form>
    </div>
  </div>
</form>

<script type="text/javascript">
var availablePoints = <?php echo $skillPoints;?>;
var rank = <?php echo $effectiveRank;?>;
var skillCap = <?php echo $maxLevel;?>;
var attrArray = [];
var lookupArray = {};
var optionArray = {};
var helpObj = {};
var lockedIdArray = [];
var allowSpecArray = ["DEX","KNO","OP","PER","STR","TECH"];
<?php echo $jsLockedIdArray;?>
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

function getSkillLevels() {
  var textBoxArray = document.getElementsByClassName("skillleveldisplay");
  var jsonStr = '{';
  var txtIndex = 0;
  var id = 0;
  for (txtIndex = 0; txtIndex < textBoxArray.length; txtIndex++) {
    id = textBoxArray[txtIndex].id;
    jsonStr += '"' + id + '":"' + textBoxArray[txtIndex].value + '"';
    if (txtIndex != textBoxArray.length - 1) {
      jsonStr += ',';
    }
  }
  jsonStr += '}';
  document.getElementById("skilllevels").value = jsonStr;
  document.getElementById("ptsAvailAdd").value = document.getElementById("ptsAvail").value;
  sanitizeText();
  return true;
}

function sendForm() {
  document.getElementById("page").value = "basicdata";
  document.getElementById("progressskills").submit();
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

//Get the modal
var modal = document.getElementById("addSkillModal");

// Get the button that opens the modal
var btn = document.getElementById("addSkillBtn");
// When the user clicks on the button, open the modal
btn.onclick = function() {
  modal.style.display = "block";
}


function modalClose() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}


function showRequire(opt) {
  if (opt == "") {
    document.getElementById("requirenote").innerHTML = "";
  } else {
    document.getElementById("requirenote").innerHTML = "Required";
  }

}

function subtract(itemKey) {
    var keyArray = itemKey.split(":");
    var skillId = keyArray[0];
    if (keyArray.length < 3) {
        return false;
    }


    var minValue = keyArray[2];
    var currentValue = document.getElementById(itemKey).value;


    if (parseInt(currentValue) <= parseInt(minValue)) {
        return false;
    }

    var specType = "";
    if (keyArray.length == 4) {
        specType = keyArray[3];
    }

    var previousValue = currentValue - 1;
    var pointsDeducted = 0;

    if (specType == "") {
        if (lockedIdArray.includes(skillId)) {
          if (parseInt(currentValue) <= 5) {
              return false;
          }
        }
        pointsDeducted = rank * previousValue;
    } else if (specType == "S") {
        pointsDeducted = Math.ceil((rank * previousValue)/2);
    } else if (specType == "A") {
        var effectiveRank = rank - 1;
        if (effectiveRank < 1) {
            effectiveRank = 1;
        }
        pointsDeducted = effectiveRank * previousValue;
    } else {
        return false;
    }

    availablePoints += pointsDeducted;
    --currentValue;
    document.getElementById(itemKey).value = currentValue;
    document.getElementById("ptsAvail").value = availablePoints;
    if (parseInt(availablePoints) < 10) {
        document.getElementById("rankraise").disabled = false;
        document.getElementById("nextpage").disabled = false;
    } else {
        document.getElementById("rankraise").disabled = true;
        document.getElementById("nextpage").disabled = true;
    }
    if (parseInt(availablePoints) < 5) {
        document.getElementById("addSkillBtn").disabled = true;
    } else if ('<?php echo $rankLabel;?>'.toLowerCase() != "starter") {
        document.getElementById("addSkillBtn").disabled = false;
    }

}

function add(itemKey) {
    var keyArray = itemKey.split(":");
    if (keyArray.length < 3) {
        return false;
    }

    var currentValue = document.getElementById(itemKey).value;
    if (parseInt(currentValue) >= parseInt(skillCap)) {
        return false;
    }

    var specType = "";
    if (keyArray.length == 4) {
        specType = keyArray[3];
    }

    var pointsToDeduct = 0;
    if (specType == "") {
        pointsToDeduct = rank * currentValue;
    } else if (specType == "S") {
        pointsToDeduct = Math.ceil((rank * currentValue)/2);
    } else if (specType == "A") {
        var effectiveRank = rank - 1;
        if (effectiveRank < 1) {
            effectiveRank = 1;
        }
        pointsToDeduct = effectiveRank * currentValue;
    } else {
        return false;
    }

    if (parseInt(pointsToDeduct) > parseInt(availablePoints)) {
        return false;
    }

    availablePoints -= pointsToDeduct;
    ++currentValue;
    document.getElementById(itemKey).value = currentValue;
    document.getElementById("ptsAvail").value = availablePoints;

    if (availablePoints < 10) {
        document.getElementById("rankraise").disabled = false;
        document.getElementById("nextpage").disabled = false;
    } else {
        document.getElementById("rankraise").disabled = true;
        document.getElementById("nextpage").disabled = true;
    }
    if (parseInt(availablePoints) < 5) {
        document.getElementById("addSkillBtn").disabled = true;
    } else if ('<?php echo $rankLabel;?>'.toLowerCase() != "starter") {
        document.getElementById("addSkillBtn").disabled = false;
    }
}

function updateHelpLocal() {
	var selElement = document.getElementById("availableskills");
	var selectedId = selElement.options[selElement.selectedIndex].value;
  var currentAttr = document.getElementById("currentattr").value;
  console.log(currentAttr);
  if (allowSpecArray.includes(currentAttr)) {
    document.getElementById("radio2").disabled = false;
    document.getElementById("radio3").disabled = false;
  } else {
    document.getElementById("radio2").disabled = true;
    document.getElementById("radio3").disabled = true;
  }
	document.getElementById("helptext").innerHTML = helpObj[selectedId];
}

</script>
