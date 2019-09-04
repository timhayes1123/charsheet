<?php
include_once "SkillCollection.php";

define("TOUGHNESS", 239);

isset($_POST['characterobj']) ? $characterObj = json_decode($_POST['characterobj'], TRUE) : $characterObj = array();

# echo var_dump($characterObj);

## Get the look-up array for the skill attributes.
$attrLookupArray = array();
$allSkillsArray = array();
$sqlStmt = "SELECT `attr`, `displayname` FROM `attrlookup` ORDER BY `sort`;";
$result = $conn->prepare($sqlStmt);
$result->execute();
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attrLookupArray[$row["attr"]] = $row["displayname"];
}

## Loop through the array of all skills and build the full collection
$hitPoints = 20;

$skillColl = new SkillCollection();

foreach ($characterObj["fullskillcollection"] as $thisItem) {
    $keyArray = parseKey($thisItem);
    $skillArray["skill_id"] = $keyArray[ID];
    ## Hit points = 20 + level of toughness skill.
    if ($keyArray[ID] == TOUGHNESS) {
      $hitPoints += $keyArray[LEVEL];
    }
    $thisSkillObj = new Skill($skillArray);
    $thisSkillObj->populateFieldsById(str_replace("---", " ", $keyArray[SPEC]));
    $thisSkillObj->setLevel($keyArray[LEVEL]);
    $thisSkillObj->setAdvOrSpec($keyArray[STYPE]);
    $skillColl->addSkill($thisItem, $thisSkillObj);
}

/**
 * Checks to see if the Object property with the specified name exists. If so return it. Otherwise return an empty string.
 *
 * @param string $key
 * @param associative array $chObj
 * @return string
 */

function cObj($key, $chObj) {
  if (array_key_exists($key, $chObj)) {
    return $chObj[$key];
  }
  return "";
}


function qEsc($str) {
  return str_replace("'", "&#39;", $str);
}

## Process any implant fields from the previous page form and add them to the characterObj associated array.
foreach ($_POST as $key => $value) {
  if (substr($key, 0, 7) == "implant") {
    $characterObj[$key] = qEsc($value);
  }
}

$race = raceDisplayName($characterObj['raceId'], $characterObj['subRaceId']);
$force = $characterObj['forcesensitive'] ? "Yes" : "No";
$progress = "0/" . ($characterObj['actualRank'] * 50);

$cObj = "cObj";

$jsonStr = json_encode($characterObj);
$sqlStmt = "INSERT INTO `characterjson` (`name`, `json`) VALUES (?,?);";
$result = $conn->prepare($sqlStmt);
$result->execute(array($characterObj['name'], $jsonStr));
# echo var_export($characterObj, true);
$cr = PHP_EOL;
$bioData = <<<EOT
[color=red][b]Name[/b][/color]: 	{$cObj('name', $characterObj)}
[color=red][b]Alias[/b][/color]:  {$cObj('alias', $characterObj)}
[color=red][b]Race[/b][/color]: 	$race
[color=red][b]Height[/b][/color]: 	{$cObj('feet', $characterObj)}' {$cObj('inches', $characterObj)}"
[color=red][b]Weight[/b][/color]: 	{$cObj('weight', $characterObj)}
[color=red][b]Gender[/b][/color]: 	{$cObj('gender', $characterObj)}
[color=red][b]Age[/b][/color]: 	{$cObj('age', $characterObj)}
[color=red][b]Eye color[/b][/color]: 	{$cObj('eyes', $characterObj)}
[color=red][b]Hair color[/b][/color]: 	{$cObj('hair', $characterObj)}
[color=red][b]Planet of Birth[/b][/color]: 	{$cObj('birth', $characterObj)}
[color=red][b]Native Language[/b][/color]: 	{$cObj('language', $characterObj)}
[color=red][b]Force Sensitive[/b][/color]: 	$force
[color=red][b]Alignment[/b][/color]: 	{$cObj('alignment', $characterObj)}
[color=red][b]Hit Points[/b][/color]: $hitPoints
[color=red][b]Current Rank[/b][/color]: 	{$cObj('actualRank', $characterObj)}
[color=red][b]Progress to Next Rank[/b][/color]: $progress
[color=red][b]UNSPENT SP[/b][/color]: 	{$cObj('availableSP', $characterObj)}
[rule]
EOT;





$skillsByAttrArray = array();
foreach ($attrLookupArray as $attr => $displayValue) {
  foreach ($skillColl->getFullCollection() as $skillKey => $thisSkillObj) {
    #echo SEP . "->getAttribute()" . var_export($thisSkillObj->getAttribute(), true) . "<br>";
    #echo "->getAttribute()" . var_export($attr, true) . "<br>";
    if ($thisSkillObj->getAttribute() == $attr) {
      $lineItem = "[tr][td]";
      switch ($thisSkillObj->getAdvOrSpec()) {
        case 'A':
                  $lineItem .= '[color=green]' . $thisSkillObj->getName() . ': ' . $thisSkillObj->getSpec() . '[/color]';
                  break;
        case 'S':
                  $lineItem .= '[color=yellow]' . $thisSkillObj->getName() . ': ' . $thisSkillObj->getSpec() . '[/color]';
                  break;
        default:
                  $lineItem .= $thisSkillObj->getName();

      }
      $lineItem .= "[/td][td]" . $thisSkillObj->getLevel() . "[/td][/tr]";
      if (!array_key_exists($attr, $skillsByAttrArray)) {
        $skillsByAttrArray[$attr] = "[tr][td][color=red][b]" . $displayValue . "[/b][/color][/td][td][/td][/tr]";
      }
      $skillsByAttrArray[$attr] .= $lineItem;
    }
  }
}

$skills = "[spoiler=Skills][table]";
foreach ($attrLookupArray as $attr => $displayValue) {
  $skills .= array_key_exists($attr, $skillsByAttrArray) ? $skillsByAttrArray[$attr] : "";
}
$skills .= "[/table][/spoiler][rule]";

$inventory = <<<EOT
[rule][br]
[spoiler=Inventory]
[color=red][b]Armor[/b][/color]
[table]
[tr][td]Armor Type[/td][td] {$cObj('armor', $characterObj)} [/td][/tr]
[tr][td]Armor Rating vs Force[/td][td]{$cObj('armorf', $characterObj)}[/td][/tr]
[tr][td]Armor Rating vs Energy[/td][td]{$cObj('armorn', $characterObj)}[/td][/tr]
[tr][td]Armor Rating vs Kinetic[/td][td]{$cObj('armork', $characterObj)}[/td][/tr]
[tr][td]Armor Rating vs Environmental[/td][td]{$cObj('armore', $characterObj)}[/td][/tr]
[tr][td]Penalties[/td][td]{$cObj('armorp', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 1[/td][td]{$cObj('armor1', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 2[/td][td]{$cObj('armor2', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 2[/td][td]{$cObj('armor3', $characterObj)}[/td][/tr]
[/table]
[br][color=red][b]Primary Weapon[/b][/color]
[table]
[tr][td]Weapon[/td][td]{$cObj('weapon', $characterObj)}[/td][/tr]
[tr][td]Skill[/td][td]{$cObj('weaponsk', $characterObj)}[/td][/tr]
[tr][td]Damage Type[/td][td]{$cObj('weapondt', $characterObj)}[/td][/tr]
[tr][td]Damage[/td][td]{$cObj('weapondmg', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 1[/td][td]{$cObj('weapon1', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 2[/td][td]{$cObj('weapon2', $characterObj)}[/td][/tr]
[/table]
[br][color=red][b]Secondary/Off-hand Weapon[/b][/color]
[table]
[tr][td]Weapon[/td][td]{$cObj('ohweapon', $characterObj)}[/td][/tr]
[tr][td]Skill[/td][td]{$cObj('ohweaponsk', $characterObj)}[/td][/tr]
[tr][td]Damage Type[/td][td]{$cObj('ohweapondt', $characterObj)}[/td][/tr]
[tr][td]Damage[/td][td]{$cObj('ohweapondmg', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 1[/td][td]{$cObj('ohweapon1', $characterObj)}[/td][/tr]
[tr][td]Mod Slot 2[/td][td]{$cObj('ohweapon2', $characterObj)}[/td][/tr]
[/table]
EOT;

$raInv = "";
$nraInv = "";

for ($loopIndex = 1; $loopIndex <= 8; $loopIndex++) {
  $ra = "rainventory" . $loopIndex;
  $nra = "nrainventory" . $loopIndex;
  if ($cObj($ra, $characterObj) != "") {
    $raInv .= "[*]" . $cObj($ra, $characterObj);
  }
  if ($cObj($nra, $characterObj) != "") {
    $nraInv .= "[*]" . $cObj($nra, $characterObj);
  }
}

$invLists = <<<EOT
[br][color=red][b]Readily Accessible Inventory Slots[/b][/color]
[list=1]
$raInv
[/list]
[br][color=red][b]Non-readily Accessible Inventory Slots[/b][/color]
[list=1]
$nraInv
[/list]
[/spoiler]
EOT;

$inventory .= $invLists;

$cyberSection = "[rule][spoiler=Cybernetics][br]";
$cybernetics = "";
for ($index = 1; $index <= 9; $index++) {
  $impRecord = "implant" . $index;
  $impRecordEff = $impRecord . "eff";
  $impSlots = $impRecord . "slots";
  $impMod1 = $impRecord . "mod1";
  $impMod1Eff = $impMod1 . "eff";
  $impMod2 = $impRecord . "mod2";
  $impMod2Eff = $impMod2 . "eff";
  $impMod3 = $impRecord . "mod3";
  $impMod3Eff = $impMod3 . "eff";

  if ($characterObj[$impRecord] != "") {
    $cybernetics .= <<<EOT
    [color=red]{$cObj("$impRecord", $characterObj)}[/color]
    [table]
    [tr][td]Description[/td][td]{$cObj("$impRecordEff", $characterObj)}[/td][/tr]
    [tr][td]Upgrade Slots[/td][td]{$cObj("$impSlots", $characterObj)}[/td][/tr]
    [tr][td]Mod Slot 1[/td][td]{$cObj("$impMod1", $characterObj)}[/td][/tr]
    [tr][td]Effect[/td][td]{$cObj("$impMod1Eff", $characterObj)}[/td][/tr]
    [tr][td]Mod Slot 2[/td][td]{$cObj("$impMod2", $characterObj)}[/td][/tr]
    [tr][td]Effect[/td][td]{$cObj("$impMod2Eff", $characterObj)}[/td][/tr]
    [tr][td]Mod Slot 3[/td][td]{$cObj("$impMod3", $characterObj)}[/td][/tr]
    [tr][td]Effect[/td][td]{$cObj("$impMod3Eff", $characterObj)}[/td][/tr]
    [/table][br]
EOT;
  }
}

if ($cybernetics != "") {
  $cyberSection .= $cybernetics . "[/spoiler]";
}


$textHTML = '';

$pageObj->appendInstructions("Character Sheet (Formatted for Enjin)");
 ?>
