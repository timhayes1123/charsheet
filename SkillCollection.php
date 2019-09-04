<?php
/**
 * Custom sort of collection object.
 *
 * @param Prereq $a
 * @param Prereq $b
 * @return number
 */
function sortPrereqsByGroup($a, $b) {
	#Sort the prereqs array by the group_id of the prereq Object.

	if ($a->getGroupId() == $b->getGroupId()) {
		return 0;
	} else {
		return ($a->getGroupId() < $b->getGroupId()) ? -1 : 1;
	}
}

function sortSkillsByName($a, $b) {
	#Sort the prereqs array by the group_id of the prereq Object.

	if ($a->getName() == $b->getName()) {
		return 0;
	} else {
		return ($a->getName() < $b->getName()) ? -1 : 1;
	}
}

/**
 *
 * @author
 *
 */
class SkillCollection implements JsonSerializable {
	# The block of skills and Force powers available to be selected.

	protected $skillCollection = array(); # Associative Array of Skill Objects
	private $nextGroupId = null;
	private $numChoices = array(); # Associative Array with groupId as key and the number of choices as the value.

	function jsonSerialize() {
	    return get_object_vars($this);
	}

	function sortByName() {
		uasort ($this->skillCollection, "sortSkillsByName");
	}

	/**
	 *
	 * @param string $itemKey
	 * @param Skill $skillObj
	 */
	function addSkill($itemKey, $skillObj) {
		# itemKey is expected in the format of int:int:str wher str may be an empty string.
		# First int is skill_id, second int is group_id, string is specialization.
		$this->skillCollection[$itemKey] = $skillObj;
		$this->numChoices[$skillObj->getGroupId()] = $skillObj->getNumToSelect();

	}


	/**
	 *
	 * @param int $groupId
	 * @return int
	 */
	function getNumChoices($groupId) {
		return $this->numChoices[$groupId];
	}

	/**
	 * Count the number of skill slots used. For the most part, one skill uses one slot, but there are exceptions.
	 *
	 * @param int $raceId
	 * @return int
	 */
	function getSkillCount($raceId) {
	    $counter = 0;

	    if ($raceId == 4) {
	        foreach ($this->skillCollection as $itemKey => $skillObj) {
	            if (($skillObj->getAttribute() == "OP") || ($skillObj->getAttribute() == "TECH")) {
	                $counter += 2;
	            } else {
	                ++$counter;
	            }
	        }
	    } else {
	        $counter = count($this->skillCollection);
	    }

	    return $counter;
	}

	/**
	 * If the collection object was built using minimal data, populate name fields by database lookup.
	 */
	function setNamesBySkillId() {
	    ### Loop through all the skills in the skillCollection array and set the name field from the database.

			$dm = new DataModel("skill");
			$dataArray = $dm->getData(array(0));

	    $nameToIdLookup = Array();
	    foreach ($dataArray as $row) {
	        $nameToIdLookup[$row["skill_id"]] = $row["name"];
	    }

	    foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
	        $this->skillCollection[$itemKey]->setName($nameToIdLookup[$thisSkillObj->getId()]);
	    }
	}

	/**
	 * Determines if the given item exists in the skill array.
	 *
	 *
	 * @param string $key
	 * @param Skill $thisSkillObj
	 * @return boolean
	 */

	function skillExists($key) {
	    $keyIgnoreLevel = substr($key, 0, strrpos($key, ":"));
	    foreach ($this->skillCollection as $fullKey => $obj) {
	        $testKey = substr($fullKey, 0, strrpos($fullKey, ":"));
	        # echo "<hr>keyIgnoreLevel = $keyIgnoreLevel<br>testKey = $testKey<hr>";
	        if ($keyIgnoreLevel == $testKey) {
	            return TRUE;
	        }
	    }
	    return FALSE;
	}

	/**
	 * Generate a list of all skills via database lookup. Rank and Force sensitivity can be used as parameters to limit the selection.
	 *
	 * @param int $rank
	 * @param int $forceSensitive
	 */
	function setFull($rank = -1, $forceSensitive = -1) {
		$paramsArray = array();

		$dm = new DataModel("skill");
		$rank = ($rank == 0) ? 1 : $rank;
		if ($rank != -1) {
			$dm->addParams("rank", "<=");
			array_push($paramsArray, $rank);
		}
		if (!$forceSensitive) {
			$dm->addConstraint("type", "S");
		}

		$dataArray = $dm->getData($paramsArray);
		# echo "--" . $dm->getSQLStmt() . "<BR>";
		# echo "rank = $rank<br>";
		 #echo var_export($dataArray, true);
		# die;

    foreach ($dataArray as $row) {
        $thisSkillObj = new Skill($row);
        $thisKey = $thisSkillObj->getId() . ":0:" . $thisSkillObj->getSpec();
        $this->addSkill($thisKey, $thisSkillObj);
        # echo "Adding item: $thisKey to collection.<br>";
    }

	}

	/**
	 * Return the contents of the current collection (Associative array of Skill objects).
	 *
	 * @return array
	 */
	function getFullCollection() {
	    return $this->skillCollection;
	}

	/**
	 * For processing of the Collection that must be done on a group by group basis (as defined by groupId field of
	 * the SkillObject), this function determines if there are additional groups to process.
	 *
	 * @return boolean
	 */
	function nextGroup() {
		# Determines if there are more skill groups to process. Returns a boolean.
		$allGroupIds = array();

		foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
			# $thisSkillObj->displayPrereqs();
			if (!$thisSkillObj->isAutoGrant()) {
				array_push($allGroupIds, $thisSkillObj->getGroupId());
			}
		}

		#Sort the unique group ids into an array.
		$distinctGroupArray = array_unique($allGroupIds, SORT_NUMERIC);

		$nextFound = FALSE;

		foreach ($distinctGroupArray as $thisGroupId) {
			if (is_null($this->nextGroupId) || $thisGroupId > $this->nextGroupId) {
				$this->nextGroupId = $thisGroupId;
				$nextFound = TRUE;
				break;
			}
		}


		if ($nextFound) {
			return TRUE;
		} else {
			$this->nextGroupId = null;
			return FALSE;
		}

	}

	/**
	 * Remove the skill from the current collection.
	 *
	 * @param string $key
	 */

	function removeSkill($key) {
	    if (array_key_exists($key, $this->skillCollection)) {
	        unset($this->skillCollection[$key]);
	    }
	}

	/**
	 * Loops through all skills in the collection and determines if the input parameter is a prerequisite for any of them.
	 * Return true if it is.
	 *
	 * @param Skill $testSkillObj
	 * @return boolean
	 */

	function isPrereq($testSkillObj) {
	    $isPrereq = FALSE;
	    foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
	        if ($testSkillObj == $thisSkillObj) {
	            continue;
	        } else {
	            if ($thisSkillObj->skillIsPrereq($testSkillObj)) {
	                return TRUE;
	            }
	        }
	    }


        return $isPrereq;
	}

	/**
	 * Iterate through the current collection and build an array of Skill objects that belong to the next group to be processed.
	 *
	 * @return array of Skill objects.
	 */
	function getNextSkillGroup() {
		## Returns an associative array based on the autogrant property and group_id of the skillObj.
		$skillGroupArray = array();

		foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
			#

			if (!$thisSkillObj->isAutoGrant() && ($thisSkillObj->getGroupId() == $this->nextGroupId)) {
				$skillGroupArray[$itemKey] = $thisSkillObj;
			}
		}

		return $skillGroupArray;
	}

	/**
	 * Iterate through the collection and return an array of Skill objects that have the autogrant flag set to true.
	 *
	 * @return array of Skill objects
	 */

	function getAutoSkills() {

		$autoSkillArray = array();
		foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
			# echo "Adding: $itemKey <br>";
			if ($thisSkillObj->isAutoGrant()) {
				$autoSkillArray[$itemKey] = $thisSkillObj;
			}
		}

		return $autoSkillArray;
	}

	/**
	 * Return an non associative array of Skill objects based from the current SkillCollection.
	 *
	 * @return array of Skill Objects
	 */
	function getSimpleArray() {
	    $simpleArray = array();
	    foreach($this->skillCollection as $itemKey => $thisSkillObj) {
	        array_push($simpleArray, $thisSkillObj);
	    }
	    return $simpleArray;
	}
}

/**
 *
 * @author
 *
 * The main object used for processing.
 *
 */
class Skill implements JsonSerializable{
	private $rank = 1; # rank field from skill table.
	private $numToSelect = 1; # num_choices field from bonus_meta table
	private $autoGrant = 0; # auto_grant field from bonus_skill table
	private $prereqExempt = 0; # prereq_exempt field from bonus_skill table
	protected $specText = ""; # specialization. Can be pre-populated from specialization field of bonus_skill table. May be user input.
	private $skillLevel = 1; # level field from bonus_skill table.
	protected $skillId = 0; # skill_id field from skill table.
	private $name = ""; # name field from skill table.
	protected $groupId = 0; # group_id field from bonus_skill table.
	protected $type = 'S'; # type field from skill table
	private $specializationAllowed = 0; # spec field from skill table.
	private $specializationRequired = 0; # specreq field from skill table.
	private $descr = ""; # descr field from skill table.
	private $prereqs = array(); # Associative array of Prereqs
	private $advancedAllowed = 0; # adv field from skill table.
	private $advancedRequired = 0; # advreq field from skill table.
	private $attribute = ""; # attr field from skill table.
	protected $advOrSpec = ""; # Based on user selection. A if the skill is Advanced. S if the skill is Specialization.

	/**
	 * Loop through the current skills prerequisite items and determine if the parameter object is a prerequisite.
	 *
	 * @param Skill $testSkillObj
	 * @return boolean
	 */
	function skillIsPrereq($testSkillObj) {
	    $prereqStatus = FALSE;
	    foreach ($this->prereqs as $itemKey => $prereqObj) {
	        # echo "<hr>" . "testSkillObj->getSpec() = " . $testSkillObj->getSpec() . "<BR>";
	        # echo "prereqObj->getSpec() = " . $prereqObj->getSpec() . "<hR>";
	        if (($testSkillObj->getId() == $prereqObj->getId()) && (strtolower($testSkillObj->getSpec()) == strtolower($prereqObj->getSpec()))) {
	            return TRUE;
	        }
	    }

	    return $prereqStatus;
	}

	/**
	 * Basic setter function. $val must be S or A.
	 *
	 * @param string $val
	 */

	function setAdvOrSpec($val) {
	    if (($val == "S") || ($val == "A")) {
	       $this->advOrSpec = $val;
	    }
	}

	/**
	 * Basic getter function.
	 *
	 * @return string
	 */

	function getAdvOrSpec() {
	    return $this->advOrSpec;
	}

	function jsonSerialize() {
	    return get_object_vars($this);
	}

	/**
	 * Basic setter function.
	 *
	 * @param string $name
	 */
	function setName($name) {
	    $this->name = $name;
	}

	/**
	 * Basic getter function.
	 *
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Basic getter function.
	 *
	 * @return string
	 */
	function getSpec() {
		return $this->specText;
	}

	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function getLevel() {
		return $this->skillLevel;
	}

	/**
	 * Basic setter function
	 *
	 * @param int $level
	 */

	function setLevel($level) {
	    $this->skillLevel = $level;
	}

	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function getId() {
		return $this->skillId;
	}


	/**
	 * Basic getter function.
	 *
	 * @return string
	 */
	function getSpecType() {
		return $this->type;
	}

	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function getNumToSelect() {
		return $this->numToSelect;
	}

	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function isSpecAllowed() {
		return $this->specializationAllowed;
	}

	function isAdvAllowed() {
		return $this->advancedAllowed;
	}

	function isAdvRequired() {
		return $this->advancedRequired;
	}


	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function isSpecRequired() {
	    return $this->specializationRequired;
	}

	/**
	*  Look through all of the skills in skillObjArray and determine if any of them qualify to be the prerequisite for the current skill.
	*  To qualify as a prerequsite, the Id fields must match, the prerequisite must not be a specialization, and it must have a level of
	*  5 or greater.
	*
	*  @param array $skillObjArray
	*  @return boolean
	**/

	function advPrereqsMet($skillObjArray) {
		$foundBase = FALSE;
		foreach ($skillObjArray as $candidateSkillObj) {
			if (($this->getId() == $candidateSkillObj->getId()) && ($candidateSkillObj->getSpec() == "") && ($candidateSkillObj->getLevel() >= 5)) {
				$foundBase = TRUE;
				break;
			}
		}
		return $foundBase;
	}

    /**
     * In instances where the Skill object is created by minimal data, it is necessary to get the other information
     * directly from the database.
     *
     * @param string $spec;
     */
  function populateFieldsById($spec = "") {

		$dm = new DataModel("skill");
		$dm->addParams("skill_id");
		$dm->setLimit(1);

		$dataArray = $dm->getData(array($this->skillId));

    foreach ($dataArray as $row) {
        $this->attribute = $row["attr"];
  	    $this->name = $row["name"];
  	    $this->type = $row["type"];
  	    $this->specializationAllowed = $row["spec"];
  	    $this->specializationRequired = $row["specreq"];
  	    $this->descr = $row["descr"];
  	    $this->advancedAllowed = $row["adv"];
  	    $this->advancedRequired = $row["advreq"];
  	    $this->rank = $row["rank"];
  	    if ($this->specializationAllowed) {
  	        $spec = str_replace("'", "&#39;", $spec);
  	       $this->specText = $spec;
  	    } else {
  	        $this->specText = "";
  	    }
    }

    $this->setPrereqs($this->skillId);
	}

	/**
	 * Determine if the prereqObj satisfies the requirements of a prerequisite for the SkillObj
	 *
	 * @param Prereq $prereqObj
	 * @param Skill $skillObj
	 * @return boolean
	 */
	function isMatch($prereqObj, $skillObj) {


		if ($prereqObj->getId() != $skillObj->getId()) {
			return FALSE;
		}

		if ($prereqObj->getSpec() != "") {

			if (strtolower($prereqObj->getSpec()) != strtolower($skillObj->getSpec())) {
				# text does not match.
				return FALSE;
			} else if ($prereqObj->getSpecType() == "E") {
				# text matches and either case is allowed.
				return TRUE;
			} else if ($prereqObj->getSpecType() == $skillObj->getSpecType()) {
				# text matches and type matches.
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			#Ids match and no other criteria are involved.
			return TRUE;
		}
	}

	/**
	 * Debugging function
	 */
	function displayPrereqs() {
		foreach ($this->prereqs as $thisPrereqId => $thisPrereqObj) {
			echo "####################################################<br>";
			echo "current skill: " . $this->getId() . "<br>";
			echo "prereq ID:" . $thisPrereqObj->getId() . "<br>";
			echo "group ID:" . $thisPrereqObj->getGroupId() . "<br>";
			echo "Specialization:" . $thisPrereqObj->getSpecText() . "<br>";
			echo "Specialization Type:" . $thisPrereqObj->getSpecType() . "<br>";

			echo "####################################################<br>";
		}
	}



	/**
	 * Looks through the Skill objects in the input parameter and determines if all prerequisites are met
	 *
	 * @param array of Skill objects $selectedSkillArray
	 * @return boolean
	 */
	function prereqsMet($selectedSkillArray) {
		# returns a boolean value based on whether or not the skill Objects in selectedSkillArray meet the requirements of the current skill.

		$lastGroupId = -1; # last group that was processed.
		$notFoundArray = array(); # Skill Ids for which no match has yet been found.
		$foundGroupArray = array(); # Group Ids for which a match has been found.
		# var_dump($this->prereqs);

		usort ($this->prereqs, "sortPrereqsByGroup");

		#echo "<hr>" . var_export($this->prereqs, true) . "<hr>" . var_export($selectedSkillArray) . "<hr>";

		foreach ($this->prereqs as $thisPrereqId => $thisPrereqObj) {

			$currentGroupId = $thisPrereqObj->getGroupId();
			if (in_array($currentGroupId, $foundGroupArray)) {
				# Criteria has already been met for this group.
				# debugTrace("Group $currentGroupId already processed.");
				continue;
			}

			foreach ($selectedSkillArray as $candidateSkillObj) {
				# echo "<HR>TESTING FOR MATCH<HR>";
				# echo var_export($thisPrereqObj, true) . "<hr>";
				# echo var_export($candidateSkillObj, true) . "<hr><hr>";
				if ($this->isMatch($thisPrereqObj, $candidateSkillObj)) {
					# echo "MATCH FOUND<br><br>";
					if ($lastGroupId == $currentGroupId) {
					    # Remove item from the notFoundArray.
						$notFoundArray = array_diff($notFoundArray, array($candidateSkillObj->getId()));
					}
					# Add the current group to those found.
					array_push($foundGroupArray, $currentGroupId);
					$lastGroupId = $currentGroupId;
					continue 2;
				}
				# echo "NOT A MATCH FOUND<br><br>";
			}
			## A single failure doesn't automatically disqualify. Another match with the same group id will qualify.
			debugTrace("Adding " . $thisPrereqObj->getId() . " to notFoundArray.");
			array_push($notFoundArray, $thisPrereqObj->getId());
			$lastGroupId = $currentGroupId;
		}
		debugTrace(var_export($notFoundArray, true));
		if (sizeof($notFoundArray) > 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function __construct($row) {
		## Associative array from the database.
	    $this->skillId = $row["skill_id"];
	    array_key_exists("name", $row) ? $this->name = $row["name"] : $this->name = "";
	    array_key_exists("num_choices", $row) ? $this->numToSelect = $row["num_choices"] : $this->numToSelect = 0;
	    array_key_exists("prereq_exempt", $row) ? $this->prereqExempt = $row["prereq_exempt"] : $this->prereqExempt = 0;
	    array_key_exists("specialization", $row) ? $this->specText = $row["specialization"] : $this->specText = "";
	    array_key_exists("level", $row) ? $this->skillLevel = $row["level"] : $this->skillLevel = 0;
	    array_key_exists("auto_grant", $row) ? $this->autoGrant = $row["auto_grant"] : $this->autoGrant = 0;
	    array_key_exists("group_id", $row) ? $this->groupId = $row["group_id"] : $this->groupId = 0;
	    array_key_exists("type", $row) ? $this->type = $row["type"] : $this->type = "";
	    array_key_exists("spec", $row) ? $this->specializationAllowed = $row["spec"] : $this->specializationAllowed = 0;
	    array_key_exists("specreq", $row) ? $this->specializationRequired = $row["specreq"] : $this->specializationRequired = 0;
	    array_key_exists("descr", $row) ? $this->descr = $row["descr"] : $this->descr = "";
	    array_key_exists("adv", $row) ? $this->advancedAllowed = $row["adv"] : $this->advancedAllowed = 0;
	    array_key_exists("advreq", $row) ? $this->advancedRequired = $row["advreq"] : $this->advancedRequired = 0;
	    array_key_exists("rank", $row) ? $this->rank = $row["rank"] : $this->rank = 0;
	    array_key_exists("attr", $row) ? $this->attribute = $row["attr"] : $this->attribute = 0;

		$this->setPrereqs($this->skillId);
	}

	/**
	 * Populate the prerequisites from the prereq table in the database.
	 *
	 * @param int $skillId
	 */

	private function setPrereqs($skillId) {
		# returns an associative array of prereq objects based on the skill id.
		$dm = new DataModel("prereq");
		$dm->addParams("skill_id");

		$dataArray = $dm->getData(array($skillId));

		foreach ($dataArray as $row) {
			$thisPrereq = new Prereq($row["prereq_id"], $row["group_id"], $row["specialization"], $row["spec_type"]);
			$this->prereqs[$row["prereq_id"]] = $thisPrereq;
		}
		# $this->displayPrereqs();
	}

	/**
	 * Return all class properties as an array.
	 *
	 * @return array
	 */
	function getPropertiesAsArray() {

		$outArray = array();
		foreach ($this as $varname => $value) {
			array_push($outArray, $value);
		}
		return $outArray;
	}

	/**
	 * Get an array of prereq names for display.
	 *
	 * @return array
	 */
	function getPrereqsByName() {
	    $prereqNameArray = array();


	    foreach ($this->prereqs as $itemKey => $thisPrereq) {
	        $thisId = $thisPrereq->getId();
	        # echo "THISID = " . var_export($thisId, true) . "<BR><BR>";

	        $specText = "";

					$dm = new DataModel("prereq");
					$dm->addParams("skill_id");
					$dm->addParams("prereq_id");
					$dm->setLimit(1);

					$dataArray = $dm->getData(array($this->getId(), $thisPrereq->getId()));

	        foreach ($dataArray as $row) {
	            $specText = $row["specialization"];
	        }
	        $thisPrereq->populateFieldsById($specText);
	        $displayArray = array($thisPrereq->getName(), $thisPrereq->getSpec());
	        array_push($prereqNameArray, $displayArray);
	    }
	    return $prereqNameArray;
	}

	/**
	 * Get the names of skill prerequisites in help text friendly format.
	 *
	 * @return string
	 */
	function getHelpTextPrereqs() {
	    $prereqNameArray = $this->getPrereqsByName();
	    $displayHTML = "<br><hr><br>";
	    $displayHTML .= "PREREQUISITES<br><br>";
	    if (sizeof($prereqNameArray) == 0) {
	        $displayHTML .= "None<br>";
	    } else {
	        foreach ($prereqNameArray as $thisNameArray) {
	            $displayHTML .= $thisNameArray[0];
	            if (sizeof($thisNameArray) >= 2 && $thisNameArray[1] != "") {
	                $displayHTML .= " (" . $thisNameArray[1] . ")";
	            }
	            $displayHTML .= "<br>";
	        }
	        $displayHTML .= "<br>";
	    }

	    return $displayHTML;
	}

	/**
	 * Basic getter function.
	 *
	 * @return int
	 */
	function getGroupId() {
		return $this->groupId;
	}

	/**
	 * Basic getter function
	 *
	 * @return string
	 */
	function getDescr() {
	    return $this->descr;
	}

	/**
	 * Basic getter function
	 *
	 * @return int
	 */
	function isAutoGrant() {
		return $this->autoGrant;
	}

	/**
	 * Basic getter function
	 *
	 * @return string
	 */

	function getAttribute() {
	    return $this->attribute;
	}



}
/**************
class Prereq {
	private $id = 0;
	private $specText = "";
	private $type = ""; # Valid values are "", S, A, or E (Specialization, Advanced, or Either)
	private $groupId = 0;

	function __construct($id, $groupId, $specText = "", $type = "") {
		$this->id = $id;
		$this->specText = $specText;
		$this->type = $type;
		$this->groupId = $groupId;
	}

	function getId() {
		return $this->id;
	}

	function getGroupId() {
		return $this->groupId;
	}

	function getSpecText() {
		return $this->specText;
	}

	function getSpecType() {
		return $this->type;
	}
}
******************/

/**
 *
 * @author
 *
 * A lightweight version of the Skill object. Only needs a few properties populated to work.
 *
 */
class Prereq extends Skill {

    function __construct($id, $groupId, $specText = "", $type = "") {
        $this->skillId = $id;
        $this->specText = $specText;
        $this->type = $type;
        $this->groupId = $groupId;
    }

}


/**
 * Specialized SkillCollection.
 *
 * @author
 *
 */
class SelectableSkillCollection extends SkillCollection {

    protected $skillCollection = array(); # Associative Array of Skill Objects


    /**
     * Returns an associative array of skills which excludes duplicates. A skill is considered a duplicate if one of the two
     * following situations is true:
     * 1. skillIds are equal and specialization is NOT allowed.
     * 2. skillIds are equal, specialization is allowed, specialization text is NOT blank and is equivalent in both objects.
     *
     * @param array of SkillCollection Objects $collectionArray
     * @return array of Skill Objects
     */
    function getSkillsNoDuplicates($collectionArray) {
        ### Returns an associative array of skills which excludes duplicates.

        $returnArray = array();

        # echo var_export($collectionArray, true);

        foreach ($this->skillCollection as $itemKey => $thisSkillObj) {
            $disallow = FALSE;
            foreach ($collectionArray as $thisSkillCollection) {
                # echo var_export($thisSkillCollection, true) . "<br><br><hr><br><br>";
                $skillArray = $thisSkillCollection->getFullCollection();

                foreach ($skillArray as $testKey => $testSkillObj) {
                    if ($testSkillObj->getId() == $thisSkillObj->getId()) {
                        if (!$thisSkillObj->isSpecAllowed()) {
                            $disallow = TRUE;
                            break 2;
                        } else if (($testSkillObj->getSpec() != "") && ($testSkillObj->getSpec() == $thisSkillObj->getSpec())) {
                            $disallow = TRUE;
                            break 2;
                        }
                    }
                }
            }
            if (!$disallow) {
                $returnArray[$itemKey] = $thisSkillObj;
            }
        }

        return $returnArray;

    }

}
?>
