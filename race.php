<?php

include_once $controller->getController($pageToLoad);

## populates $outputHTML string
## populates $jsArray string

?>
<form action="charcreate.php" method="post">
	<input type="hidden" name="page" value="subrace" />

	<select class="select-css" id="race" name="race" size="1" onchange="updateHelp('race')">
		<?php echo $outputHTML;?>
	</select>
	<br>
	<input type="submit" value="Save" />
</form>
<script>
<?php echo $jsArray;?>
</script>
