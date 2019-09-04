<?php
class Controller {
  private $pageToLoad = "";
  private $pageAction = "";

  function getController($page, $action = "") {
    $controllerString = "controller/$page";
    if ($action != "") {
      $controllerString .= "_$action";
    }

    return $controllerString .= "_controller.php";

  }

  function getPageToLoad($page = "race") {
    if (strpos($page, ".php") === FALSE) {
      return $page . ".php";
    } else {
      return $page;
    }
  }
}
 ?>
