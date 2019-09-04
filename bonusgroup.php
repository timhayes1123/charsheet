<?php

include_once 'stdlib.php';
ini_set('display_errors', 1);

$conn = dbconn::getConnectionBuild()->getConnection();

$stmt = "SELECT DISTINCT(`race_id`) FROM `racebonus`;";
$result = $conn->prepare($stmt);
$result->execute();

$raceIdArray = array();

foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
	array_push($raceIdArray, $row["race_id"]);
}


$stmt = "SELECT `num_choices`, `race_id`, `auto_grant`, `prereq_exempt`, `specialization`, `level`, `cat_list`, `skill_id_list` FROM `racebonus` WHERE `race_id` = ?";
$result = $conn->prepare($stmt);
foreach ($raceIdArray as $thisRaceId) {
	$groupId = 1;
	$result->execute(array($thisRaceId));

	foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
		
		# echo $row["cat_list"] . " | " . $row["skill_id_list"] . " | " . $row["specialization"] . "<br>";
		if ($row["auto_grant"]) {
			$bonusSkillArray = explode(",", $row["skill_id_list"]);
			$groupInsert = "INSERT INTO `bonus_meta` (`race_id`, `group_id`, `num_choices`) VALUES (";
			$groupInsert .= $row["race_id"] . ", " . $groupId . ", " . $row["num_choices"] . ");";
			echo $groupInsert . "<br>";
			foreach ($bonusSkillArray as $thisSkillId) {
				$thisSpecStr = parseSpecialization($thisSkillId, $row["specialization"]);
				$bonusInsert = "INSERT INTO `bonus_skill` (`race_id`, `group_id`, `skill_id`, `specialization`, `auto_grant`, `prereq_exempt`, `level`) VALUES (";
				$bonusInsert .= $row["race_id"] . ", " . $groupId . ", " . $thisSkillId . ", '" . $thisSpecStr . "', 1, 1, " . $row["level"] . ");";
				echo $bonusInsert . "<br>";
			}
		} else {
			$groupInsert = "INSERT INTO `bonus_meta` (`race_id`, `group_id`, `num_choices`) VALUES (";
			$groupInsert .= $row["race_id"] . ", " . $groupId . ", " . $row["num_choices"] . ");";
			echo $groupInsert . "<br>";
			
			$whereCat = "";
			$whereSkill = "";
			if ($row["cat_list"] != "") {
				$whereCat = "AND `attr` IN (" . quoteList($row["cat_list"]) . ") ";
			}
			if ($row["skill_id_list"] != "") {
				$whereSkill = "AND `id` IN (" . $row["skill_id_list"] . ") ";
			}
			$subSelect = "SELECT DISTINCT(`id`) FROM `skill` WHERE 1=1 " . $whereCat . $whereSkill . ";";
			$subQuery = $conn->prepare($subSelect);
			$subQuery->execute();
			foreach ($subQuery->fetchAll(PDO::FETCH_ASSOC) as $skillRow) {
				
				$bonusInsert = "INSERT INTO `bonus_skill` (`race_id`, `group_id`, `skill_id`, `specialization`, `auto_grant`, `prereq_exempt`, `level`) VALUES (";
				$bonusInsert .= $row["race_id"] . ", " . $groupId . ", " . $skillRow["id"] . ", '', 0, 0, " . $row["level"] . ");";
				echo $bonusInsert . "<br>";
			}
		}
		++$groupId;
	}
	
}

?>