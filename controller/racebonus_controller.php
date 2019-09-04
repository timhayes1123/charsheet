<?php
include_once "SkillCollection.php";
### Based on race selection, present an option for bonus skills. Skills may be assigned by default, selectable from a range, or a mix of both.

define("TD", "</td><td>"); # separator tag
# isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();
isset($_POST['forcesensitive']) ? $characterObj["forcesensitive"] = $_POST['forcesensitive'] : $characterObj["forcesensitive"] = 0;


$nextPage = "skillselect";

## Populated the characterObj from the POST variables.

$characterObj["subRaceId"] == -1 ? $characterObj["queryRaceId"] = $characterObj["raceId"] : $characterObj["queryRaceId"] = $characterObj["subRaceId"];
$currentSkillsArray = array();

#Ignore rank for Miraluka bonus power.
$characterObj["queryRaceId"] == 12 ? $currentEffectiveRank = 3 : $currentEffectiveRank = 1;
$characterObj["actualRank"] = 0;
$availableSkillsArray = array();


$dm = new DataModel($pageToLoad);
$dm->addParams("race_id", "=", "a");
$dm->addParams("rank", "<=", "c");
$dm->addSort("group_id", "a");
$dm->addSort("name", "c");
$dataArray = $dm->getData(array($characterObj["queryRaceId"], $currentEffectiveRank));



$skillObjCollection = new SkillCollection(); # All the data from that query will end up here.
$selectedSkillArray = array();  # Used for determining prerequisites. Any auto granted skills will be stored here.
$outputHTML = ""; # String for outputting skill choices.
$autoGrantText = ""; # String for outputting fixed choices.
$helpText = "";
$jsGroupIdArray = ""; # Number of choices indexed by groupId.
$jsHelpTextArray = ""; #javascript array that contains all the skill descriptions. When the user clicks an icon near a skill, information for that skill is displayed.
/*
if ($result->rowCount() > 0) {
	$resultCounter = 0;
	foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
*/
if (sizeof($dataArray) > 0) {
  $resultCounter = 0;
  foreach ($dataArray as $row) {
		# Get all results from the database. Each row is used to build a Skill object. The associative array key is skill_id:group_id:specialization

		$thisSkillObj = new Skill($row);
		$skillKey = $row["skill_id"] . ":" . $row["group_id"] . ":" . $row["specialization"];

		$skillObjCollection->addSkill($skillKey, $thisSkillObj);
		# Since skill id is not necessarily unique, elements of this array may get overwritten. That's ok,
		# since the descr text will be exactly the same for both.
		$jsHelpTextArray .= "helpArray[" . $row["skill_id"] . "] = \"" . $row["descr"] . "\";" . PHP_EOL;
	}
	# Skills marked as autogrant are processed first.
	# Start by retrieving a collection to loop through.

	$autoGrantArray = $skillObjCollection->getAutoSkills();
	$autoGrantsExist = FALSE;

	### Create the output table that displays the automatically granted skills.
	foreach ($autoGrantArray as $thisKey => $autoSkillObj) {
		$autoGrantsExist = TRUE;
		$outputHTML .= '<tr><td class="form">' . $autoSkillObj->getName() . TD . $autoSkillObj->getSpec() . TD . $autoSkillObj->getLevel() . TD;

		$postValue = $autoSkillObj->getId() . ":" . $autoSkillObj->getSpec() . ":" . $autoSkillObj->getLevel();
		##ID FIELD
		$outputHTML .= "<input type=\"hidden\" id=\"autoid[" . $resultCounter . "]\" ";
		$outputHTML .= "name=\"autoid[" . $resultCounter . "]\" value=\"" . $postValue . "\" >";
		##SPECIALIZATION FIELD
		# $outputHTML .= "<input type=\"hidden\" id=\"spec[" . $resultCounter . "]\" ";
		# $outputHTML .= "name=\"spec[" . $resultCounter . "]\" value=\"" . $autoSkillObj->getSpec() . "\" >";
		$outputHTML .= TD. '<img src="eyecon.png" id="' . $autoSkillObj->getId() . '" ';
		$outputHTML .= 'onclick="updateHelpLocal(' . $autoSkillObj->getId() . ')"/>';
		$outputHTML .= "</td></tr>";

		## These skills are considered 'selected' for the purpose of determining prerequisites.
		array_push($selectedSkillArray, $autoSkillObj);
		++$resultCounter;
	}

	$autoGrantText = "";
	if ($autoGrantsExist) {
		$autoGrantText = <<<EOT
			<table width="100%">
				<tr><th colspan="5">The following skills are granted automatically:</th></tr>
				<tr><th>Skill</th><th>Specialization (if any)</th><th colspan="3">Level</th></tr>
				$outputHTML
			</table>
EOT;

	}
	$outputHTML = "";
	$groupSelectionTextArray = array();
	while ($skillObjCollection->nextGroup()) {

		$skillGroupArray = $skillObjCollection->getNextSkillGroup();
		$rowCounter = 0;
		$outputHTML = "";
		foreach ($skillGroupArray as $thisKey => $selectSkillObj) {
			$thisGroupId = $selectSkillObj->getGroupId();
			# Pretty up the rows
			$odd = $rowCounter++ & 1;
			$odd ? $className = "oddrow" : $className = "evenrow";
			# Prerequisites will determine if the row is selectable or not.
			$selectSkillObj->prereqsMet($selectedSkillArray) ? $disabled = "" : $disabled = "disabled";

			# Checkbox id is in the form selectid_N_[skillId] Where N is the group number.
			# Checkbox name is in the form selectidN where N is the group number.
			# value is the skillId

			#### CREATE ROW. DEFINE FIRST FIELD.
			$outputHTML .= "<tr class=\"$className\"><td width=\"10%\">";

			$outputHTML .= "<input type=\"checkbox\" name=\"" . $thisKey;
			$outputHTML .= '" class="chkbox' . $thisGroupId . '" ';
			$outputHTML .= "id=\"selectid_" . $thisGroupId . "\" ";
			$outputHTML .= "value=\"" . $selectSkillObj->getId();
			if ($selectSkillObj->getSpec() != "") {
			    $outputHTML .= ":" . $selectSkillObj->getSpec();
			}
			$outputHTML .= "\" $disabled>";

			#### CLOSE FIRST FIELD. DEFINE SECOND FIELD.
			$outputHTML .= "</td><td width=\"45%\">";

			# Display choice information for the user. Skillname (N)   where N is the skill level.
			$outputHTML .= "<p class=\"maintext\">" . $selectSkillObj->getName();
			$outputHTML .= " (" . $selectSkillObj->getLevel() .  ")</p>";

			# Create a hidden text field to store the level. id and name properties both have format of level[N]  where N is the skillId
			$outputHTML .= "<input type=\"hidden\" id=\"level[" . $selectSkillObj->getId() . "]\" ";
			$outputHTML .= "name=\"level[" . $selectSkillObj->getId() . "]\" value=\"" . $selectSkillObj->getLevel() . "\" >";

			#### CLOSE SECOND FIELD. DEFINE THIRD FIELD.
			$outputHTML .= "</td><td width=\"40%\">";

			# Specialization field. In order to be edited, this field must be empty (no default value) and the skill must allow specialization.
			# Readonly is used since the value of the field must be passed.
			($selectSkillObj->getSpec() == "" && $selectSkillObj->isSpecAllowed()) ? $disabled = "" : $disabled = 'readonly="readonly"';

			# Name and id tags are set in the form spec_M_[N]  - where M is the groupId and N is the skillId.
			$outputHTML .= "<input type=\"text\" id=\"spec_";
			$outputHTML .= $thisGroupId . '_[';
			$outputHTML .=  $selectSkillObj->getId() . ']" ';
			$outputHTML .= 'class="spec' . $thisGroupId . '" ';
			$outputHTML .= 'name="spec_' . $thisGroupId . '_[' . $selectSkillObj->getId() . ']"';

			# Value is either provided from the database (field not editable) or is potentially user editable.
			$outputHTML .= " value=\"" . $selectSkillObj->getSpec() . "\" $disabled ";
      $outputHTML .= 'required="' . $selectSkillObj->isSpecRequired() . '" ';
      $outputHTML .= 'assocskill="' . $selectSkillObj->getName() . '">';

			#### CLOSE THIRD FIELD. DEFINE FOURTH FIELD.
			# Create an icon that can be clicked to select help.
      $outputHTML .= TD . '<img src="eyecon.png" id="' . $selectSkillObj->getId() . '" ';
      $outputHTML .= 'onclick="updateHelpLocal(' . $selectSkillObj->getId() . ')"/>';
			$outputHTML .= "</td></tr>";

		}

		if ($outputHTML != "") {
			$numChoices = $skillObjCollection->getNumChoices($selectSkillObj->getGroupId());

			# This javascript array is used in validation. Contains the required number of choices per group.
			$jsGroupIdArray .= "groupIdArray[" . $selectSkillObj->getGroupId() . "] = " . $numChoices . ";" . PHP_EOL;
			$groupSelectionText = <<<EOT
		<table width="100%">
			<tr><th colspan="4">Choose $numChoices skills from below:</th></tr>
			<tr><th width="10%">&nbsp;</th><th width="45%">Skill (Level)</th><th width="40%">Specialization (if any)</th><th></th></tr>
			$outputHTML
		</table>
EOT;
			array_push($groupSelectionTextArray, $groupSelectionText);
		}
	}
	$outputHTML = "";
	foreach ($groupSelectionTextArray as $outputBlock) {
		$outputHTML .= $outputBlock;
	}
} else {
	$outputHTML .= "<p class=\"instructions\">No bonus skills granted.</p>";
}


$pageObj->appendInstructions("Skill bonuses by race");
# $pageObj->appendBody($prefaceHTML . $autoGrantText . $outputHTML . $postfixHTML . $jsText);
$pageObj->appendHelp($helpText);
 ?>
