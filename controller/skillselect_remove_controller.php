<?php
### Get the ID of the selected item. If there is no selected item, return an error message.
isset($_POST["selectedskills"]) ? $selectedSkillId = $_POST["selectedskills"] : $selectedSkillId = "";
# $errMessage .= $selectedSkillId;
if ($selectedSkillId != "") {
    $keyArray = parseKey($selectedSkillId);
    $thisSkillArray = array();
    $thisSkillArray["skill_id"] = $keyArray[ID];
    $thisSkillArray["specialization"] = $keyArray[SPEC];
    $thisSkillArray["level"] = $keyArray[LEVEL];

    $thisSkillObj = new Skill($thisSkillArray);
    $thisSkillObj->populateFieldsById(str_replace("%%%", "&#39;", $keyArray[SPEC]));
    # echo SEP . "6: " . $keyArray[SPEC] . SEP;
    # echo SEP . var_export($workingSkillColl, true) . SEP . var_export($thisSkillObj, true) . SEP;

    ## Check if this skill is a prerequisite for others on the list. If it is, it can't be removed while they remain.
    if (!$workingSkillColl->isPrereq($thisSkillObj)) {

        # $collKey = $keyArray[ID] . ":" . $keyArray[SPEC] . ":" . $keyArray[LEVEL];
        $collKey = makeKey(array($keyArray[ID], $keyArray[SPEC], $keyArray[LEVEL]));

       # echo SEP . var_export($workingSkillColl);
        # echo "collKey = " . $collKey . SEP;

        $workingSkillColl->removeSkill($collKey);

        # echo SEP . var_export($workingSkillColl) . SEP;



        # echo SEP . var_export($workingKeyArray) . SEP;
        if (($key = array_search($collKey, $workingKeyArray)) !== false) {
            unset($workingKeyArray[$key]);
        }
        # echo SEP . var_export($workingKeyArray) . SEP;
    } else {
        $errMessage .= "Unable to remove skill: " . $thisSkillObj->getName() . ". Skill is prerequisite to other selected skills.";
    }
} else {
    $errMessage .= "No skill selected to remove.<br>";
}
 ?>
