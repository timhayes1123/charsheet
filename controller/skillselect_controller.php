<?php
include_once "SkillCollection.php";

define("MAXSKILLS", 30);
define("ST", "</td><td>"); # separator tag

define("MINSKILLS", 15); # Set low for development purposes.
define("STARTINGSP", 50);

/**
 *
 * @param array $skillArray
 * @param int $searchId
 * @return string
 */
function getKey($skillArray, $searchId) {
    ## SkillArray is an array of strings. Strings should be in the form of ID:SPECIALIZATON:LEVEL (int:string:int)
    foreach ($skillArray as $thisSkill) {
        $thisItem = explode(":", $thisSkill);
        if ($searchId == $thisItem[ID]) {
            return $thisSkill;
        }
    }
    return "";
}

$nextPage = "skillprogress";
$errMessage = ""; # Text to display at bottom of the screen if any processing fails.

isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();

$workingSkillColl = new SkillCollection(); # The selected skillset. Excludes the race skills.
$raceSkillColl = new SkillCollection(); # These skills cannot be removed. However, they must be considered when checking prereqs.
$raceKeyArray = array(); # For the character object.
$workingKeyArray = array(); # For the character object.
$slotsUsed = 0; # count of the number of currently selected skills. Does not include the Racial bonuses.

if (!isset($_POST["reprocess"])) {
    ### Process handler from coming directly from racebonus.php
    if (isset($_POST['autoid'])) {
        $skillArray = $_POST["autoid"];
        foreach ($skillArray as $itemKey) {
            $keyArray = parseKey($itemKey);
            $thisSkillArray = array();
            $thisSkillArray["skill_id"] = $keyArray[ID];
            $thisSkillArray["specialization"] = $keyArray[SPEC];
            $thisSkillArray["level"] = $keyArray[LEVEL];
            $thisSkillObj = new Skill($thisSkillArray);
            $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $keyArray[SPEC]));

            $skillKey = makeKey(array($thisSkillArray["skill_id"], $thisSkillArray["specialization"], $thisSkillArray["level"]));
            $raceSkillColl->addSkill($skillKey, $thisSkillObj);
            # $skillKey .=  ":" . $thisSkillArray["level"];
            array_push($raceKeyArray, $skillKey);
        }
    }

    $groupId = 1;
    $found = TRUE;

    foreach ($_POST as $key => $value) {
        if (substr_count($key, ":") == 2) {
            $thisSkillArray = array();

            $passedKeyArray = explode(":", $key);
            $specText = "";
            $thisSkillArray["skill_id"] = $passedKeyArray[0];
            $groupId = $passedKeyArray[1];
            $specText = $passedKeyArray[2];
            isset($_POST["level"]) ? $level = $_POST["level"][$thisSkillArray["skill_id"]] : $level = 1;
            isset($_POST["spec_" . $groupId . "_"]) ? $skillSpecArray = $_POST["spec_" . $groupId . "_"] : $skillSpec = array();
            if ($specText == "") {
                $thisSkillArray["specialization"] = $skillSpecArray[$thisSkillArray["skill_id"]];
            } else {
                $thisSkillArray["specialization"] = $specText;
            }
            $thisSkillArray["level"] =  $level;


            # echo "KEY = " . $key . "<br>VALUE = " . $value . "<br>";
            # echo "SKILL_ID = " . $thisSkillArray["skill_id"] . "<br>GROUP ID = " . $groupId . "<br>SPEC = " . $skillSpec . SEP;

            $skillKey = makeKey(array($thisSkillArray["skill_id"], $thisSkillArray["specialization"], $thisSkillArray["level"]));
            $thisSkillObj = new Skill($thisSkillArray);
            $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $thisSkillArray["specialization"]));


            $raceSkillColl->addSkill($skillKey, $thisSkillObj);

            array_push($raceKeyArray, $skillKey);

        }
    }


} else {
    ### Process handler from Add/remove skill button.

    ## First build the collection of Racial bonus skills.
    if ((array_key_exists("raceskillcollection", $characterObj)) && (is_array($characterObj["raceskillcollection"]))) {
        # echo SEP . gettype($characterObj["raceskillcollection"]) . SEP;
        foreach ($characterObj["raceskillcollection"] as $itemKey) {
            # echo SEP . "SECTION 1" . var_dump($itemKey) . SEP;
            $keyArray = parseKey($itemKey);
            $thisSkillArray = array();
            $skillKey = substr($itemKey, 0, strrpos($itemKey, ":"));
            $level = $keyArray[LEVEL];
            $thisSkillArray["skill_id"] = $keyArray[ID];
            $thisSkillArray["specialization"] = $keyArray[SPEC];
            $thisSkillArray["level"] = $keyArray[LEVEL];
            $thisSkillObj = new Skill($thisSkillArray);
            $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $keyArray[SPEC]));
            # echo SEP . "3: " . $keyArray[SPEC] . SEP;

            $thisSkillObj->setLevel($level);

            $raceSkillColl->addSkill($itemKey, $thisSkillObj);
            array_push($raceKeyArray, $itemKey);

        }

    } else {
        $characterObj["raceskillcollection"] = array();
    }

    ### Build the collection of currently selected skills.

    if ((array_key_exists("addskillcollection", $characterObj)) && (is_array($characterObj["addskillcollection"]))) {
        foreach ($characterObj["addskillcollection"] as $itemKey) {
            # echo "ITEMKEY= " . $itemKey . SEP;
            $keyArray = parseKey($itemKey);
            $thisSkillArray = array();
            $skillKey = substr($itemKey, 0, strrpos($itemKey, ":"));
            $level = $keyArray[LEVEL];
            $thisSkillArray["skill_id"] = $keyArray[ID];
            $thisSkillObj = new Skill($thisSkillArray);
            $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $keyArray[SPEC]));
            # echo SEP . "4: " . $keyArray[SPEC] . SEP;
            $thisSkillObj->setLevel($level);

            $workingSkillColl->addSkill($itemKey, $thisSkillObj);
            # echo SEP . "itemKey=" . var_export($itemKey, true) . SEP . "thisSkillObj=" . var_export($thisSkillObj, true) . SEP;
            array_push($workingKeyArray, $itemKey);
        }
    } else {
        $characterObj["addskillcollection"] = array();
    }

    isset($_POST["formprocess"]) ? $action = $_POST["formprocess"] : $action = "";

    include_once $controller->getController($pageToLoad, strtolower($action));
/*
    if ($action == "Add") {

    } else if ($action == "Remove") {

    }
*/
}

$raceSkillColl->setNamesBySkillId();
# echo SEP . var_dump($raceKeyArray) . SEP;
$characterObj["raceskillcollection"] = $raceKeyArray;

$tableRow = ""; # HTML contents of the table displaying the Race Selected skills.

foreach ($raceSkillColl->getFullCollection() as $itemKey => $thisSkillObj) {
    $tableRow .= "<tr><td>" . $thisSkillObj->getName() . ST . displayFriendly($thisSkillObj->getSpec()) . ST . $thisSkillObj->getLevel() . "</td></tr>";
}

# Build the drop down containing the currently selected skills.
$slotsUsed = $workingSkillColl->getSkillCount($characterObj["queryRaceId"]);
$slotsAvail = MAXSKILLS;
$selectRow = '<tr><td><select name="selectedskills" id="selectedskills" size="10" style="width: 400px;">';
# echo SEP . var_export($workingSkillColl, true) . SEP;
foreach ($workingSkillColl->getFullCollection() as $itemKey => $thisSkillObj) {
    $thisSpec = $thisSkillObj->getSpec();
    $selectKey = makeKey(array($thisSkillObj->getId(), $thisSpec, $thisSkillObj->getLevel()));
    $selectRow .= '<option value="' . $selectKey . '">' . $thisSkillObj->getName();

    # Since HTML does not support multi-column format for select boxes, list the specialization in parens after.
    if ($thisSpec != "") {
        $selectRow .= " (" . displayFriendly($thisSpec) . ")";
    }
    $selectRow .= "</option>";
}
$selectRow .= "</select></td></tr>";

# echo SEP . var_dump($workingKeyArray) . SEP;
$characterObj["addskillcollection"] = $workingKeyArray;

$availableSkillColl = new SelectableSkillCollection();
$availableSkillColl->setFull($characterObj["actualRank"], $characterObj["forcesensitive"]);
# echo (var_export($raceSkillColl, true));
$arrayCurrentSkills = array($workingSkillColl, $raceSkillColl); # All skills. Necessary to determine duplicate status and prerequisites.

## Build the Javascript array to hold the column headings.
$attrLookupArray = array();
$jsLookupArray = ""; # Variable used in the javascript array[attr] = displayname
$jsAttrArray = ""; # Used to create an actual ordered array in JavaScript.
$currentAttr = ""; # Default attr. The first one returned by the sql query.
$currentDisplay = ""; # Default displayname. Used in the HTML table title.
$counter = 1; # Counter used to get first row only.
$sqlStmt = "SELECT `attr`, `displayname` FROM `attrlookup` ORDER BY `sort`;";
$result = $conn->prepare($sqlStmt);
$result->execute();
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attrLookupArray[$row["attr"]] = $row["displayname"];
    $jsLookupArray .= "lookupArray[\"" . $row["attr"] . "\"] = \"" . $row["displayname"] . "\";" . PHP_EOL;
    $jsAttrArray .= "attrArray.push(\"" . $row["attr"] . "\");" . PHP_EOL;
    if ((($counter++ == 1) && (!isset($_POST["currentattr"]))) || ((isset($_POST["currentattr"])) && ($_POST["currentattr"] == $row["attr"]))) {
        $currentAttr = $row["attr"];
        $currentDisplay = $row["displayname"];
    }
}

$skillsByAttrArray = array();
$jsHelpArray = ""; # Javascript help text.
$jsOptionHTML = "";

foreach ($availableSkillColl->getSkillsNoDuplicates($arrayCurrentSkills) as $itemKey => $thisSkillObj) {
    $thisSpec = $thisSkillObj->getSpec();
    $thisAttr = $thisSkillObj->getAttribute();
    $thisOption = "";

    $itemKey = $thisSkillObj->getId() . ":" . str_replace("&#39;", "%%%", $thisSpec) . ":" . $thisSkillObj->getLevel();
    $thisOption .= '<option value="' . $itemKey . '" ';

    $combArr = array();
    foreach ($workingSkillColl->getSimpleArray() as $collSkillObj) {
        array_push($combArr, $collSkillObj);
    }
    foreach ($raceSkillColl->getSimpleArray() as $collSkillObj) {
        array_push($combArr, $collSkillObj);
    }
    if (!$thisSkillObj->prereqsMet($combArr)) {
        $thisOption .= 'class="unavailable" ';
    }
    $thisOption .= ' >';
    $thisOption .= $thisSkillObj->getName();
    if ($thisSpec != "") {
        $thisOption .= " ($thisSpec)";
    }
    $thisOption .= "</option>";
    $jsHelpArray .= "helpArray[\"$itemKey\"] = \"" . $thisSkillObj->getDescr() . $thisSkillObj->getHelpTextPrereqs() . "\";" . PHP_EOL;

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

if (!array_key_exists($currentAttr, $skillsByAttrArray)) {
  $currentAttr = "DEX";
  $currentDisplay = $attrLookupArray[$currentAttr];
}

$availableSelectHTML = '<tr><td><select name="availableskills" onchange="updateHelp(\'availableskills\')" id="availableskills" size="10" style="width: 400px;">';
$availableSelectHTML .= join("", $skillsByAttrArray[$currentAttr]);
$availableSelectHTML .= "</select></td></tr>";

/*
$raceBonusSkillHTML = "";
if ($tableRow != "") {
    $raceBonusSkillHTML = <<<EOT
    <table width="100%">
        <tr><th colspan="3">Race Bonus Skills</th></tr>
        <tr><th>Name</th><th>Specialization</th><th>Level</th></tr>
        $tableRow
    </table>
EOT;
}
*/
/*
$formHTML = '<form action="charcreate.php" novalidate method="post" onsubmit="return sanitizeText()">';
$formHTML .= "<input type=\"hidden\" name=\"characterobj\" value='" . json_encode($characterObj) . "' />";
$formHTML .= '<input type="hidden" name="page" value="skillselect.php" />';
$formHTML .= '<input type="hidden" name="reprocess" value="true" />';

$selectedSkillHTML = <<<EOT
$formHTML
    <table width="100%">
        <tr><th>Selected Skills ($slotsUsed / $slotsAvail)</th></tr>
        $selectRow
        <tr><td><input type="submit" name="formprocess" value="Remove"></td></tr>
    </table>
EOT;
*/
$slotsUsed >= MAXSKILLS ? $disabled = "disabled" : $disabled = "";
$slotsUsed < MINSKILLS ? $proceedDisabled = "disabled" : $proceedDisabled = "";
$minSkills = MINSKILLS;

/*
$availableSkillHTML = <<<EOT
    <table width="100%">
        <tr><th>Available Skills</th></tr>
        <tr><th><p id="skillattr">$currentDisplay</p><input type="hidden" id="currentattr" value="$currentAttr"></th></tr>
        $availableSelectHTML
        <tr><td>
            <table width="100%">
                <tr><td>Category Selection</td><td colspan="2"><input type="submit" $disabled name="formprocess" value="Add"></td></tr>
                <tr><td><input type="button" value="<" onclick="navigate('<')"><input type="button" value=">" onclick="navigate('>')"></td>
                    <td>Specialization:</td><td><input type="text" name="spectext" id="spectext" value=""></td></tr>
            </table>
        </td></tr>
    </table>
</form>
<table width="100%"><tr><td><p id="notify" class="errortext"></p></td></tr></table>
EOT;
*/

$characterObj["availableSP"] = STARTINGSP;
if (array_key_exists("addskillcollection", $characterObj)) {
    foreach ($characterObj["addskillcollection"] as $key => $itemKey) {
        $itemKey = str_replace("'", "%%%", $itemKey);
        $characterObj["addskillcollection"][$key] = $itemKey;
    }
}

$pageObj->appendInstructions("Starter Skill Selection Page");
# $pageObj->appendBody($raceBonusSkillHTML . $selectedSkillHTML . $availableSkillHTML);
 ?>
