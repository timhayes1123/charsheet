<?php
if (isset($_POST['race'])) {
    $raceId = $_POST['race'];
} else {
	$raceId = 1;
}

$characterObj["raceId"] = $raceId;
$_SESSION["characterObj"] = $characterObj;

$nextPage = "forcesensitive";
$jsArray = "";


$dm = new DataModel($pageToLoad);
$dm->addParams("parent_id");
$dm->addSort("name");
$dataArray = $dm->getData(array($raceId));

$jsArray = ""; # Help text for Javascript array.
$count = 1; # Marker for the first row.

$outputHTML = "";


if (sizeof($dataArray) > 0) {
  # Build a drop down based on the result set.
	$pageObj->appendInstructions("Select character sub-race");
	# <select class="select-css" id="subrace" name="subrace" size="1" onchange="updateHelp('subrace')">
  $selectBox = new htmlElement("select", "subrace", "select-css");
  $selectBox->addAttribute("name", "subrace");
  $selectBox->addAttribute("size", "1");
  $selectBox->addAttribute("onchange", "updateHelp('subrace')");
  $contents = "";

	$count = 1;
  foreach ($dataArray as $row) {
    # <option value="$row["race_id"]">$row["name"]</option>
    $option = new htmlElement("option");
    $option->addAttribute("value", $row["race_id"]);
    if (array_key_exists("subRaceId", $characterObj) and ($characterObj["subRaceId"] == $row["race_id"])) {
      $option->addString("selected");
      $pageObj->appendHelp($row["descr"] . "<br><br>" . $row["special_abilities"]);
    } else if ($count++ == 1) {
      $pageObj->appendHelp($row["descr"] . "<br><br>" . $row["special_abilities"]);
    }

    $option->setContents($row["name"]);
    $contents .= $option->getHtml();
    $jsArray .= "helpArray[" . $row["race_id"] . "] = \"" . $row["descr"] . "<br><br>" . $row["special_abilities"] . "\";" . PHP_EOL;
  }
  $selectBox->setContents($contents);
	# $outputHTML .= '</select>';
  $outputHTML .= $selectBox->getHtml();
} else {
  # No results found.
	$pageObj->appendInstructions("No subraces available. Click Save to continue.");
}
 ?>
