<?php

$dm = new DataModel($pageToLoad);
$dm->addParams("parent_id");
$dm->addSort("name");
$dataArray = $dm->getData(array(0));

$pageObj->clearInstructions();
$pageObj->appendInstructions("Select character race");
$outputHTML = ""; # Select box options.

$jsArray = ""; # Help text for Javascript array.
$count = 1; # Marker for the first row.

# Loop through the resultset and build the contents of the drop down box.
foreach ($dataArray as $row) {
// foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $option = new htmlElement("option");
    $option->addAttribute("value", $row["race_id"]);
    $option->setContents($row["name"]);
    if (array_key_exists("raceId", $characterObj) and ($characterObj["raceId"] == $row["race_id"])) {
      $option->addString("selected");
      $pageObj->appendHelp($row["descr"] . "<br><br>" . $row["special_abilities"]);
    } else if ($count++ == 1) {
      $pageObj->appendHelp($row["descr"] . "<br><br>" . $row["special_abilities"]);
    }
    $outputHTML .= $option->getHtml();

    # Create the contents of the helpArray global javascript variable.
  	$jsArray .= "helpArray[" . $row["race_id"] . "] = \"" . $row["descr"] . "<br><br>" . $row["special_abilities"] . "\";" . PHP_EOL;
}

?>
