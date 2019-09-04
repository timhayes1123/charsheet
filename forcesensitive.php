<?php

include_once $controller->getController($pageToLoad);

## populates $outputHTML string

?>
<form action="charcreate.php" method="post">
	<input type="hidden" name="page" value="<?php echo $nextPage?>" />
  <input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
  <?php echo $outputHTML;?>
  <br><br>
	<input type="submit" value="Save" />
</form>
