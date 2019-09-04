<?php

## Page Created: 9/28/2014
##
## Utility functions and the global database connection are created here.

require_once 'dbconn.php';
require_once 'datamodel.php';
require_once 'controller/Controller.php';

$conn = dbconn::getConnectionBuild()->getConnection();

## Key indices
define("ID", 0);
define("SPEC", 1);
define("LEVEL", 2);
define("STYPE", 3);
define("SEP", "<br><br><hr><br><br>");
define("DEBUG", FALSE);

function debugTrace($outString) {
	if (DEBUG) {
		echo $outString . "<br>";
	}
}

function getController($str) {
	return "controller/$str" . "_controller.php";
}

function displayFriendly($str) {
	$str = str_replace("%%%", "&#39;", $str);
	$str = str_replace("---", " ", $str);
	return $str;
}

/**
 * Break the itemkey into individual tokens. Strip single quotes and replace with HTML escape sequence. Provide default values for
 * any array items not provided in the key. Fallthrough behavior of switch statement is intentional.
 *
 * @param string &$key
 * @return array $keyArray
 */
function parseKey(&$key) {
    $key = str_replace("\\", "", $key);
    $key = str_replace("'", "&#39;", $key);
    $key = str_replace(" ", "---", $key);

    $keyArray = array();
    $keyArray = explode(":", $key);

    if ($keyArray[ID] == "") {
        $keyArray[ID] = -1;
    }

    switch (sizeof($keyArray)) {
        case 1:
            $keyArray[SPEC] = "";
        case 2:
            $keyArray[LEVEL] = 1;
        case 3:
            if ($keyArray[SPEC] == "") {
                $keyArray[STYPE] = "";
            } else {
                $keyArray[STYPE] = "S";
            }
    }

    return $keyArray;
}

/**
 *
 * @param array $itemArray
 * @return string
 */
function makeKey($itemArray) {
    trim($itemKey = join(":", $itemArray), ":");
    return $itemKey;
}

function raceDisplayName($raceId, $subRaceId) {
	if ($subRaceId != -1) {
		$raceId = $subRaceId;
	}
	$sqlStmt = "SELECT `name` FROM `race` WHERE `race_id` = ? LIMIT 1";
	$conn = dbconn::getConnectionBuild()->getConnection();
	$result = $conn->prepare($sqlStmt);
	$result->execute(array($raceId));
	$name = "";
	foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
		$name = $row["name"];
	}

	return $name;
}

function htmlTableRow($fieldArray) {
	# Take an array and return a string as an HTML table row in the format "<tr><td>field 1</td><td>field 2</td><td>...field n</td></tr>"
	if (!is_array($fieldArray)) {
		return "<tr><td>" . $fieldArray . "</td></tr>";
	} else {
		$returnString = "<tr>";
		foreach ($fieldArray as $thisToken) {
			$returnString .= "<td>$thisToken</td>";
		}
		return $returnString . "</tr>";
	}
}

function quoteList($listText) {
	#Accepts a list of items in the form: text1, text2, text3, ... textn
	#Returns the string as 'text1', 'text2', 'text3', ... 'textn'

	$tokenArray = explode(",", $listText);
	for ($tokenIndex = 0; $tokenIndex < sizeof($tokenArray); $tokenIndex++) {
		$tokenArray[$tokenIndex] = "'" . $tokenArray[$tokenIndex] . "'";
	}
	return join(",", $tokenArray);
}

function parseSpecialization($id, $specText) {
	# id is an integer referring to the skill_id.
	# specText is a string expected in the format of id:description or an empty string.
	# return description from text if and only if the id from specText matches the id parameter.

	if ($specText == "") {
		return $specText;
	} else {
		$tokenArray = explode(":", $specText);
		if (is_array($tokenArray) && count($tokenArray) >= 2) {
			if ($tokenArray[0] == $id) {
				return $tokenArray[1];
			} else {
				return "";
			}
		} else {
			# invalid format for string
			return "";
		}
	}
	return "";
}

class PageHTML {
	private $instructions = "";
	private $body = "";
	private $help = "";

	function clearInstructions() {
		$this->instructions = "";
	}

	function clearBody() {
		$this->body = "";
	}

	function clearHelp() {
		$this->help = "";
	}

	function appendHelp($text) {
		$this->help = $text;
	}

	function appendInstructions($text) {
		$this->instructions .= $text;
	}

	function appendBody($text) {
		$this->body .= $text;
	}

	function getInstructions() {
		return $this->instructions;
	}

	function getBody() {
		return $this->body;
	}

	function getHelp() {
		return $this->help;
	}
}

/**
 *
 * Definition of htmlElement class. Class to help keep code free of chunks of HTML.
 *
 * tag: HTML Element tag.
 * attribArray: An associative array of attributes belonging to the element that will be written in the form of attribname="value". The attribute name
 *      is the key of the array.
 * contents: The string that goes between the opening and closing tags.
 * additionalString: For those rare random strings that don't fit the attribname="value" pattern but need to be included in the tag definition.
 *
 * METHODS
 * setContents(string): Sets the value of $this->contents to the string parameter.
 * addString(string): concatenates the string parameter to the value of additionalString. A leading and trailing space is added.
 * addAttribute(string, string): Adds the two parameters, attributename and attributevalue, to the attribArray.
 * string getHtml(): Returns the fully assembled HTML string.
**/

class htmlElement {
    private $tag;
    private $attribArray;
    private $contents;
    private $additionalString;

    public function __construct($tag, $id = "", $class = "") {
        $this->tag = $tag;
        $this->contents = '';
        $this->additionalString = "";
        $this->attribArray = [];
        if ($id) {
            $this->attribArray["id"] = $id;
        }
        if ($class) {
            $this->attribArray["class"] = $class;
        }
    }

    public function setContents($contents) {
        $this->contents = $contents;
    }

    public function addString($extraString) {
        $this->additionalString .= " " . $extraString . " ";
    }

    public function addAttribute($attribName, $attribValue) {
        $this->attribArray[$attribName] = $attribValue;
    }

    public function getHtml() {
        // Tags that don't use a closing tag are added to the noCloseArray. Their tag will end with a /> instead of </tagname>.
        $noCloseArray = array("img", "input");

        $outputHTML = '<' . $this->tag . ' ';
        foreach ($this->attribArray as $attrib => $value) {
            $outputHTML .= $attrib . '="' . $value . '" ';
        }
        if (in_array($this->tag, $noCloseArray)) {
            $outputHTML .= $this->additionalString . ' />';
        } else {
            $outputHTML .= $this->additionalString . ' >';
            $outputHTML .= $this->contents;
            $outputHTML .= '</' . $this->tag . '>';
        }
        return $outputHTML;
    }
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }


    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}
