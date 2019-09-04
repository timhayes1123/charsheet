<?php

session_start();

$characterObj = array();
if (!isset($_SESSION["characterObj"])) {
  $_SESSION["characterObj"] = $characterObj;
} else {
  $characterObj = $_SESSION["characterObj"];
}

include_once 'common/stdlib.php';
ini_set('display_errors', 1);

$pageObj = new PageHTML();
$controller = new Controller();

isset($_POST['page']) ? $pageToLoad = $_POST['page'] : $pageToLoad = 'race';
# include_once $pageToLoad;

$htmlOut = "";
ob_start();
include_once $controller->getPageToLoad($pageToLoad);
$htmlOut .= ob_get_clean();

?>
<!doctype html>
<meta charset="utf-8">
<html>
<head>
    <title>Character Sheet Generator</title>
    <link rel="stylesheet" href="simplelayout.css">
    <script src="utils.js"></script>
</head>
<body>
    <div id="contentcontainer">
		<table id="maindisplay">
		<tr><td colspan="2"><p class="instructions"><?php echo $pageObj->getInstructions(); ?></p><td></tr>
		<tr><td width="65%"><?php echo $htmlOut?></td><td width="35%"><p class="maintext" id="helptext"><?php echo $pageObj->getHelp();?></p></td></tr>
		</table>
    </div>

</body>
</html>
