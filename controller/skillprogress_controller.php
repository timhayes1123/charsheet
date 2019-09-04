<?php
## Skill point cap per rank.
define("CAP", 5);

# echo var_export($_POST, true) . SEP;

function htmlSafe($text) {
  $text = str_replace("'", "%%%", $text);
  $text = str_replace("&#39;", "%%%", $text);
  return $text;
}

$jsLookupArray = ""; # Javascript Array of all Skill attributes
$jsAttrArray = ""; # Lookup for skill attributes by happy display name.
$jsLockedIdArray = ""; #An array of skill Ids. Anything in this array cannot have its skill level brought below 5 because a dependent Advanced skill has been added.
$errMessage = "";
isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();

array_key_exists("availableSP", $characterObj) ? $skillPoints = $characterObj["availableSP"] : $skillPoints = 0; # This number only gets used on the first time through the page.
array_key_exists("actualRank", $characterObj) ? $actualRank = $characterObj["actualRank"] : $actualRank = 0;

$action = "default";
$defaultSkillValues = array();
if (isset($_POST["process"])) {
  $action = $_POST["process"]; # Set by the modal dialog window for adding a skill.
  $standardObj = json_decode($_POST["skilllevels"]); # Needs to be converted to an array.
  foreach ($standardObj as $key => $value) {
    $defaultSkillValues[$key] = $value;
  }
}



$actualRank == 0 ? $effectiveRank = 1 : $effectiveRank = $actualRank; ## Rank cannot be 0 for skill point calculations to work.
if (isset($_POST['nextrank']))  {
  $actualRank = $_POST['nextrank'];
  $effectiveRank = $actualRank;
  $skillPoints *= $actualRank;
  $characterObj["actualRank"] = $actualRank;
  isset($_POST['ptsAvail']) ? $skillPoints += $_POST['ptsAvail'] : $skillPoints = $skillPoints;
} else if ($action == "add") {
  isset($_POST['ptsAvail']) ? $skillPoints = $_POST['ptsAvail'] : $skillPoints = 0;
}


$rankLabel = $actualRank == 0 ? "Starter" : $effectiveRank; # Creates a label for the display table.
$maxLevel = $effectiveRank * CAP; # Sets the maximum value that any skill can be raised to at the current rank.

$allSkillsArray = array(); # Single skill array. Contains string keys.

## Build a single array with all skills. Since skills cannot be deleted at this point, there is no need to keep them separate.

if (array_key_exists("fullskillcollection", $characterObj)) {
  # This condition should only occur if we are resubmitting to this page either as a result of a click to the raise rank button or
  # adding a skill.

    foreach ($characterObj["fullskillcollection"] as $itemKey) {
        # echo "ITEMKEY = " . $itemKey . "<BR>";
        # echo "POST[itemKey] = " . $_POST[$itemKey] . "<BR>";
        $keyArray = parseKey($itemKey);
        if ((isset($_POST[$itemKey])) && ($action != "add")) {
            $keyArray[LEVEL] = $_POST[$itemKey];
        }
        array_push($allSkillsArray, makeKey($keyArray));
    }
} else {
  # Set initial state coming from previous page.
    if (array_key_exists("raceskillcollection", $characterObj)) {
        foreach ($characterObj["raceskillcollection"] as $itemKey) {
            $keyArray = parseKey($itemKey);
            array_push($allSkillsArray, makeKey($keyArray));
        }

    }

    if (array_key_exists("addskillcollection", $characterObj)) {
        foreach ($characterObj["addskillcollection"] as $itemKey) {
            $keyArray = parseKey($itemKey);
            array_push($allSkillsArray, makeKey($keyArray));
        }
    }
}


#Create a collection with fully populated objects, based upon the the array created above.
$skillColl = new SkillCollection();

foreach ($allSkillsArray as $thisItem) {
    $keyArray = parseKey($thisItem);
    $skillArray["skill_id"] = $keyArray[ID];
    $thisSkillObj = new Skill($skillArray);
    $thisSkillObj->populateFieldsById(str_replace("---", " ", $keyArray[SPEC]));
    $thisSkillObj->setLevel($keyArray[LEVEL]);
    $thisSkillObj->setAdvOrSpec($keyArray[STYPE]);
    $skillColl->addSkill($thisItem, $thisSkillObj);
    # echo var_export($thisSkillObj) . SEP;
}

#### After building our full collection of current skills, there is enough information to process the added skill.
if ($action == "add") {
  # Check for duplicates
  # Check if prerequisites are met.
  # If all conditions are met, add the skill to allSkillsArray and $skillColl.
  # Subtract 5 skill Points.
  isset($_POST["spectext"]) ? $specText = $_POST["spectext"] : $specText = "";
  isset($_POST["availableskills"]) ? $selectedSkillId = $_POST["availableskills"] : $selectedSkillId = "";
  isset($_POST["skilltype"]) ? $selectedSkillType = $_POST["skilltype"] : $selectedSkillType = "";

  # echo "ADD SKILL PROCESSING" . SEP;

  $thisSkillArray = array();

  if ($selectedSkillId == "") {
      $errMessage .= "Unable to add skill. No skill selected.<br>";
  } else {
      if ($selectedSkillType == "") {
        $specText = "";
      }
      $selectedArray = parseKey($selectedSkillId);
      $thisSkillArray["skill_id"] = $selectedArray[0];
      $thisSkillArray["level"] = 1;
      $thisSkillArray["specialization"] = $specText;
      $thisSkillObj = new Skill($thisSkillArray);
      $specText = str_replace("%%%", "&#39;", $specText);
      $specText = str_replace(" ", "---", $specText);
      $thisSkillObj->populateFieldsById($specText);
      $thisSkillObj->setAdvOrSpec($selectedSkillType);
      # echo SEP . "5: " . $specText . SEP;
      # echo "selectedSkillId = " . $selectedSkillId . "<br>specText = " . $specText . SEP;
      # echo SEP . var_export($thisSkillObj, true) . SEP;
      if ((!$thisSkillObj->isSpecAllowed()) && (!$thisSkillObj->isAdvAllowed())) {
          # echo "<br>Failed !isSpecAllowed && !isAdvAllowed<br>";
          $specText = "";
      }

      $skillKey = makeKey(array($thisSkillArray["skill_id"], $specText, $thisSkillArray["level"], $selectedSkillType));
      ## Check for duplicates and check if prerequisites are met. Check if specialization requirements are met.

      if (!$skillColl->skillExists($skillKey, $thisSkillObj)) {
          # echo "<br><br>CALL TO PREREQSMET FOR ADD PROCESSING<br><br>";

          # echo var_export($combArr, true) . "<br>";
          # echo "<br><br>END COMBINED SKILL ARRAY<br><br>";
          $prereqArray = $skillColl->getSimpleArray();
          if ($thisSkillObj->prereqsMet($prereqArray)) {
            # echo "<br>Prereqsmet Passed<br>";
              if (($thisSkillObj->isSpecRequired() && $specText != "") || (!$thisSkillObj->isSpecRequired())) {
                if ($selectedSkillType == "A") {
                  # echo "<br>Advanced Type Recognized. Checking Prereqs<br>";
                  ### It is possible that the user has updated the base skill level and not raised the rank to commit the value.
                  ### So search through the defaultSkillValues for a non-specialized skill that has the same Id number. Pass its value
                  ### to the prereqsMet method to override the current value.
                  $itemAdded = FALSE;
                  foreach ($defaultSkillValues as $key => $value) {
                    if ((substr($key, -1) != "S") && (substr($key, -1) != "A") && (explode(":", $key)[0] == $thisSkillArray["skill_id"])) {
                      if ($value >= 5) {
                        $skillColl->addSkill($skillKey, $thisSkillObj);
                        array_push($allSkillsArray, $skillKey);
                        $skillPoints -= 5;
                        $jsLockedIdArray .= "lockedIdArray.push(\"" . $thisSkillArray["skill_id"] . "\");" . PHP_EOL;
                        $itemAdded = TRUE;
                        break;
                      }
                    }
                  }
                  if (!$itemAdded) {
                    $errMessage .= "Unable to add advanced skill: " . $thisSkillObj->getName() . ". Must specify specialization and have level 5 or greater in base field.";
                  }
                } else {
                  # echo SEP . var_export($workingSkillColl);
                  $skillColl->addSkill($skillKey, $thisSkillObj);
                  array_push($allSkillsArray, $skillKey);
                  $skillPoints -= 5;
                  # echo SEP . var_export($workingKeyArray) . SEP;
                }
              } else {
                  $errMessage .= "Unable to add skill: " . $thisSkillObj->getName() . ". Specialization required.";
              }
          } else {
              $errMessage .= "Unable to add skill: " . $thisSkillObj->getName() . ". Prerequisites not met. See skill description for details.";
          }
      } else {
          $errMessage .= "Unable to add duplicate skill: " . $thisSkillObj->getName();
          $specText != "" ? $errMessage .= " ($specText)<br>" : $errMessage .= "<br>";

      }
    }
  }

$characterObj["fullskillcollection"] = $allSkillsArray;
$counter = 1;

## Build javascript arrays that govern category navigation for skill add selection.
$sqlStmt = "SELECT `attr`, `displayname` FROM `attrlookup` ORDER BY `sort`;";
$result = $conn->prepare($sqlStmt);
$result->execute();
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attrLookupArray[$row["attr"]] = $row["displayname"];
    $jsLookupArray .= "lookupArray[\"" . $row["attr"] . "\"] = \"" . $row["displayname"] . "\";" . PHP_EOL;
    $jsAttrArray .= "attrArray.push(\"" . $row["attr"] . "\");" . PHP_EOL;
    if ($counter++ == 1) {
        $currentAttr = $row["attr"];
        $currentDisplay = $row["displayname"];
    }
}

$skillsByAttrArray = array();
$jsHelpArray = ""; # Javascript help text.
$jsOptionHTML = "";

$availableSkillColl = new SelectableSkillCollection();
$availableSkillColl->setFull($effectiveRank, $characterObj["forcesensitive"]);

foreach ($availableSkillColl->getSkillsNoDuplicates(array($skillColl)) as $itemKey => $thisSkillObj) {
    $thisSpec = $thisSkillObj->getSpec();
    $thisAttr = $thisSkillObj->getAttribute();
    $thisOption = "";

    $itemKey = $thisSkillObj->getId() . ":" . str_replace("&#39;", "%%%", $thisSpec) . ":" . $thisSkillObj->getLevel();
    $thisOption .= '<option value="' . $itemKey . '" ';


    if (!$thisSkillObj->prereqsMet($skillColl->getSimpleArray())) {
        $thisOption .= 'class="unavailable" ';
    }
    $thisOption .= ' >';
    $thisOption .= $thisSkillObj->getName();
    if ($thisSpec != "") {
        $thisOption .= " ($thisSpec)";
    }
    $thisOption .= "</option>";
    $jsHelpArray .= "helpObj[\"$itemKey\"] = \"" . $thisSkillObj->getDescr() . $thisSkillObj->getHelpTextPrereqs() . "\";" . PHP_EOL;

    if (array_key_exists($thisAttr, $skillsByAttrArray)) {
        array_push($skillsByAttrArray[$thisAttr], $thisOption);
    } else {
        $skillsByAttrArray[$thisAttr] = array();
        array_push($skillsByAttrArray[$thisAttr], $thisOption);
    }
}

foreach ($skillsByAttrArray as $thisAttr => $optionsArray) {
    $jsOptionHTML .= "optionArray[\"$thisAttr\"] = \"" . addslashes(join("", $optionsArray)) . "\";" . PHP_EOL;
}


$skillRowHTML = "";
$jsText = "";
$rowCounter = 0;

$skillColl->sortByName();
foreach ($skillColl->getFullCollection() as $itemKey => $thisSkillObj) {
     # $noSemi = str_replace("&#39;", "%%%", $itemKey);
    $noSemi = htmlSafe($itemKey);
    $odd = $rowCounter++ & 1;
    $odd ? $className = "oddrow" : $className = "evenrow";

    $skillRowHTML .= "<tr class=\"$className\">";
    $skillRowHTML .= "<td>" . $thisSkillObj->getName() . "</td>";
    $skillRowHTML .= "<td>" . displayFriendly($thisSkillObj->getSpec()) . "</td>";
    $tagName = "";
    if ($thisSkillObj->getAdvOrSpec() == "S") {
        $tagName = "Specialization";
    } else if ($thisSkillObj->getAdvOrSpec() == "A") {
        $tagName = "Advanced";
    }
    $skillRowHTML .= "<td>" . $tagName . "</td>";

    $skillRowHTML .= "<td>";

    $skillRowHTML .= '<img src="minus.jpg" id="m' . $noSemi . '" ';
    $skillRowHTML .= 'onclick="subtract(\'' . $noSemi . '\')"/>';


    $skillRowHTML .= '<input type="text" id="' . $noSemi . '" name="' . $noSemi . '"';

    $defaultValue = $thisSkillObj->getLevel();
    if (($action == "add") && (array_key_exists($itemKey, $defaultSkillValues))) {
      $defaultValue = $defaultSkillValues[$itemKey];
    }
    $skillRowHTML .= ' value="' . $defaultValue . '" ';
    $skillRowHTML .= ' maxlength="3" ';
    $skillRowHTML .= ' size="3" ';
    $skillRowHTML .= ' class="skillleveldisplay" ';
    $skillRowHTML .= ' readonly="readonly" >';

    $skillRowHTML .= '<img src="plus.jpg" id="p' . $noSemi . '" ';
    $skillRowHTML .= 'onclick="add(\'' . $noSemi . '\')"/>';

    $skillRowHTML .= "</td>";

    $skillRowHTML .= "</tr>";
}

$availableSelectHTML = '<tr><td><select name="availableskills" onchange="updateHelpLocal()" id="availableskills" size="10" style="width: 400px;">';
$availableSelectHTML .= join("", $skillsByAttrArray[$currentAttr]);
$availableSelectHTML .= "</select></td></tr>";

$raiseRankDisabled = "";
$addBtnDisabled = "";
### Rank can only be raised to 5. Don't show the button to progress higher.
if (($effectiveRank >= 5) || ($skillPoints >= 10)) {
    $raiseRankDisabled = "disabled ";
}

if (($skillPoints < 5) || (strtolower($rankLabel) == "starter")){
  $addBtnDisabled = "disabled ";
}

$pageObj->appendInstructions("Skill Progression Page");
 ?>
