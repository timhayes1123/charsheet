<?php
isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();
$actualRank = array_key_exists('actualRank', $characterObj) ? $characterObj['actualRank'] : 1;
$actualRank++;
$characterObj['actualRank'] = $actualRank;
$skillPoints = isset($_POST['ptsAvail']) ? $_POST['ptsAvail'] : 0;
$characterObj['availableSP'] = $skillPoints;

$updatedSkillArray = array();
foreach ($characterObj["fullskillcollection"] as $keyItem) {
  if (isset($_POST[$keyItem])) {
    $keyItemArray = parseKey($keyItem);
    $keyItemArray[LEVEL] = $_POST[$keyItem];
    $keyItem = makeKey($keyItemArray);
  }
  array_push($updatedSkillArray, $keyItem);
}

$characterObj["fullskillcollection"] = $updatedSkillArray;

$pageObj->appendInstructions("Bio Data/Inventory");
# $pageObj->appendBody($formHTML);
 ?>
