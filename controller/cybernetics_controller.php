<?php
isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();

function qEsc($str) {
  return str_replace("'", "&#39;", $str);
}

foreach ($_POST as $key => $value) {
  if (substr($key, 0, 6) == "chdata") {
    $characterObj[substr($key, 6)] = qEsc($value);
  }
  if (substr($key, 0, 5) == "armor") {
    $characterObj[$key] = qEsc($value);
  }
  if (substr($key, 0, 6) == "weapon") {
    $characterObj[$key] = qEsc($value);
  }
  if (substr($key, 0, 8) == "ohweapon") {
    $characterObj[$key] = qEsc($value);
  }
  if (substr($key, 0, 11) == "rainventory") {
    $characterObj[$key] = qEsc($value);
  }
  if (substr($key, 0, 12) == "nrainventory") {
    $characterObj[$key] = qEsc($value);
  }
}

if (array_key_exists("altlanguage", $characterObj)) {
  $newSkillKey = makeKey(array(191, $characterObj["altlanguage"], 5, "S"));
  array_push($characterObj["fullskillcollection"], $newSkillKey);
}

$pageObj->appendInstructions("Cybernetic Implants");
?>
