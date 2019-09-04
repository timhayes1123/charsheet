<?php

### Build a drop down list of sub races based on the race selection. Not all races have subraces available.

include_once $controller->getController($pageToLoad);

## populates $outputHTML string
## populates $jsArray string

?>
<form action="charcreate.php" method="post">
	<input type="hidden" name="page" value="<?php echo $nextPage;?>" />
	<input type="hidden" name="race" value="<?php echo $raceId;?>" />
  <?php echo $outputHTML;?>
  <br>
	<input type="submit" value="Save" />
</form>
<script>
	<?php echo $jsArray;?>
</script>
