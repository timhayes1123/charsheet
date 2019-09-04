<?php
## Present options for Force Sensitive characters. May be disallowed or required based on race selection. Otherwise, present a Yes/No selection box.

if (isset($_POST['race'])) {
    $raceId = $_POST['race'];
} else {
	$raceId = -1;
}

if (isset($_POST['subrace'])) {
    $subRaceId = $_POST['subrace'];
} else {
	$subRaceId = -1;
}

$nextPage = "racebonus";
### Create an associative array for JSON encoding.
$characterObj["raceId"] = $raceId;
$characterObj["subRaceId"] = $subRaceId;
$_SESSION["characterObj"] = $characterObj;

### Use the subRaceId if it exists.
$queryId = $raceId;
if ($subRaceId != -1) {
	$queryId = $subRaceId;
}
is_numeric($queryId) ? $queryId = $queryId : $queryId = 1;

$sql = "SELECT `has_force` FROM `forcerestrict` WHERE `race_id` = ? LIMIT 1;";

### Query should return one or zero results.

$result = $conn->prepare($sql);
$result->execute(array($queryId));

$dm = new DataModel($pageToLoad);
$dm->addParams("race_id");
$dm->setLimit(1);
$dataArray = $dm->getData(array($queryId));

$fixedChoice = -1;

foreach ($dataArray as $row) {
	if ($row["has_force"]) {
		$fixedChoice = 1;
	} else {
		$fixedChoice = 0;
	}
}

$pageObj->appendInstructions("Select Force sensitivity. Race selection may require or disallow Force sensitivity.");

$outputHTML = "";

### In the event the query returned a result, the boolean value returned is the only option available to the user. If no results were returned, the user must select Yes or No.

switch ($fixedChoice) {
	case 0:		$outputHTML .= '<input type="hidden" name="forcesensitive" value="0" />' . "Not Allowed";
				break;
	case 1:		$outputHTML .= '<input type="hidden" name="forcesensitive" value="1" />' . "Required";
				break;
	default:	$outputHTML .= '<select class="select-css" id="forcesensitive" name="forcesensitive" size="1">';
				$outputHTML .= '<option value="0">No</option>';
				$outputHTML .= '<option value="1">Yes</option>';
				$outputHTML .= '</select>';
}
 ?>
