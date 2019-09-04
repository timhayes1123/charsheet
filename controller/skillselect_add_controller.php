<?php
isset($_POST["spectext"]) ? $specText = $_POST["spectext"] : $specText = "";
isset($_POST["availableskills"]) ? $selectedSkillId = $_POST["availableskills"] : $selectedSkillId = "";

# echo "ADD SKILL PROCESSING" . SEP;

$thisSkillArray = array();

if ($selectedSkillId == "") {
    $errMessage .= "Unable to add skill. No skill selected.<br>";
} else {
    $selectedArray = parseKey($selectedSkillId);
    $thisSkillArray["skill_id"] = $selectedArray[0];
    $thisSkillArray["level"] = 1;
    $thisSkillArray["specialization"] = $specText;
    $thisSkillObj = new Skill($thisSkillArray);
    $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $specText));
    # echo SEP . "5: " . $specText . SEP;
    # echo "selectedSkillId = " . $selectedSkillId . "<br>specText = " . $specText . SEP;
    # echo SEP . var_export($thisSkillObj, true) . SEP;
    if (!$thisSkillObj->isSpecAllowed()) {
        $specText = "";
    }
    $skillKey = makeKey(array($thisSkillArray["skill_id"], $specText, $thisSkillArray["level"]));
    ## Check for duplicates and check if prerequisites are met. Check if specialization requirements are met.


    if ((!$workingSkillColl->skillExists($skillKey, $thisSkillObj)) && (!$raceSkillColl->skillExists($skillKey, $thisSkillObj))) {
        # echo "<br><br>CALL TO PREREQSMET FOR ADD PROCESSING<br><br>";
        # echo "BEGIN COMBINED SKILL ARRAY<br><br>";
        $combArr = array();
        foreach ($workingSkillColl->getSimpleArray() as $collSkillObj) {
            array_push($combArr, $collSkillObj);
        }
        foreach ($raceSkillColl->getSimpleArray() as $collSkillObj) {
            array_push($combArr, $collSkillObj);
        }

        # echo var_export($combArr, true) . "<br>";
        # echo "<br><br>END COMBINED SKILL ARRAY<br><br>";
        if ($thisSkillObj->prereqsMet($combArr)) {
            if (($thisSkillObj->isSpecRequired() && $specText != "") || (!$thisSkillObj->isSpecRequired())) {
                # echo SEP . var_export($workingSkillColl);
                $workingSkillColl->addSkill($skillKey, $thisSkillObj);
                array_push($workingKeyArray, $skillKey);
                # echo SEP . var_export($workingKeyArray) . SEP;
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

 ?>
