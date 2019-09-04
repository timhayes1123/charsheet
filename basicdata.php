<?php
include_once $controller->getController($pageToLoad);
?>
<form action="charcreate.php" name="progressskills" id="progressskills" onsubmit="return dataValidate()" novalidate method="post">
<input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
<input type="hidden" id="page" name="page" value="cybernetics" />
<table width="100%">
  <tr class="oddrow">
      <td>Name:</td><td><input type="text" name="chdataname" id="chdataname"></td>
      <td>Alias</td><td><input type="text" name="chdataalias" id="chdataalias"></td>
      <td>Height:</td><td><input type="number" name="chdatafeet" id="chdatafeet" min="1" max="9" size="2">ft. <input type="number" name="chdatainches" id="chdatainches" min="0" max="11" size="2">in.</td>
      <td>Weight:</td><td><input type="number" name="chdataweight" id="chdataweight" min="10" max="1000" size="4"> Lbs</td>
  </tr>
  <tr class="evenrow">
      <td>Gender:</td><td><input type="text" name="chdatagender" id="chdatagender"></td>
      <td>Age</td><td><input type="number" min="18" max="500" name="chdataage" id="chdataage"></td>
      <td colspan="2">&nbsp;</td>
  </tr>
  <tr class="oddrow">
    <td colspan="2">Eye Color:</td><td colspan="2"><input type="text" name="chdataeyes" id="chdataeyes"></td>
    <td colspan="2">Hair Color:</td><td colspan="2"><input type="text" name="chdatahair" id="chdatahair"></td>
  </tr>
  <tr class="evenrow">
      <td colspan="2">Planet of Birth:</td><td colspan="2"><input type="text" name="chdatabirth" id="chdatabirth"></td>
      <td colspan="2">Native Language</td><td colspan="2"><input type="text" name="chdatalanguage" id="chdatalanguage"></td>
  </tr>
  <tr class="oddrow">
      <td colspan="2">Alternate Native Language:</td><td colspan="2"><input type="text" name="chdataaltlanguage" id="chdataaltlanguage"></td>
      <td colspan="2">Alignment</td><td colspan="2"><select name="chdataalignment" id="chdataalignment"><option value="Neutral">Neutral</option><option value="Light">Light</option><option value="Dark">Dark</option></select></td>
  </tr>
</table>
<hr>
<span class="artistic">Inventory</span>
<hr>
<table>
  <tr>
    <td width="50%">
      <table width="100%">
        <tr><th colspan="2">Armor</td></tr>
        <tr><td width="50%">Armor Type</td><td width="50%"><input type="text" name="armor" id="armor"></td></tr>
        <tr><td>Rating vs. Force<td><input type="number" name="armorf" id="armorf" min="0" max="9" size="2"></td></tr>
        <tr><td>Rating vs. Energy<td><input type="number" name="armorn" id="armorn" min="0" max="9" size="2"></td></tr>
        <tr><td>Rating vs. Kinetic<td><input type="number" name="armork" id="armork" min="0" max="9" size="2"></td></tr>
        <tr><td>Rating vs. Environmental<td><input type="number" name="armore" id="armore" min="0" max="9" size="2"></td></tr>
        <tr><td>Penalties</td><td><input type="text" name="armorp" id="armorp"></td></tr>
        <tr><td>Mod Slot 1</td><td><input type="text" name="armor1" id="armor1"></td></tr>
        <tr><td>Mod Slot 2</td><td><input type="text" name="armor2" id="armor2"></td></tr>
        <tr><td>Mod Slot 3</td><td><input type="text" name="armor3" id="armor3"></td></tr>
      </table>
    </td>
    <td width="25%">
      <table width="100%">
        <tr><th colspan="2">Primary Weapon</td></tr>
        <tr><td width="50%">Weapon</td><td width="50%"><input type="text" name="weapon" id="weapon"></td></tr>
        <tr><td>Skill</td><td><input type="text" name="weaponsk" id="weaponsk"></td></tr>
        <tr><td>Damage</td><td><input type="text" name="weapondmg" id="weapondmg"></td></tr>
        <tr><td>Damage Type</td><td><input type="text" name="weapondt" id="weapondt"></td></tr>
        <tr><td>Mod Slot 1</td><td><input type="text" name="weapon1" id="weapon1"></td></tr>
        <tr><td>Mod Slot 2</td><td><input type="text" name="weapon2" id="weapon2"></td></tr>
        <tr><td>Mod Slot 3</td><td><input type="text" name="weapon2" id="weapon3"></td></tr>
      </table>
    </td>
    <td width="25%">
      <table width="100%">
        <tr><th colspan="2">Secondary/Off-hand Weapon</td></tr>
        <tr><td width="50%">Weapon</td><td width="50%"><input type="text" name="ohweapon" id="ohweapon"></td></tr>
        <tr><td>Skill</td><td><input type="text" name="ohweaponsk" id="ohweaponsk"></td></tr>
        <tr><td>Damage</td><td><input type="text" name="ohweapondmg" id="ohweapondmg"></td></tr>
        <tr><td>Damage Type</td><td><input type="text" name="ohweapondt" id="ohweapondt"></td></tr>
        <tr><td>Mod Slot 1</td><td><input type="text" name="ohweapon1" id="ohweapon1"></td></tr>
        <tr><td>Mod Slot 2</td><td><input type="text" name="ohweapon2" id="ohweapon2"></td></tr>
        <tr><td>Mod Slot 3</td><td><input type="text" name="ohweapon2" id="ohweapon3"></td></tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%">
        <tr><th>Readily Accessible Inventory</th></tr>
        <tr><td><input type="text" name="rainventory1" id="rainventory1"></td></tr>
        <tr><td><input type="text" name="rainventory2" id="rainventory2"></td></tr>
        <tr><td><input type="text" name="rainventory3" id="rainventory3"></td></tr>
        <tr><td><input type="text" name="rainventory4" id="rainventory4"></td></tr>
        <tr><td><input type="text" name="rainventory5" id="rainventory5"></td></tr>
        <tr><td><input type="text" name="rainventory6" id="rainventory6"></td></tr>
        <tr><td><input type="text" name="rainventory7" id="rainventory7"></td></tr>
        <tr><td><input type="text" name="rainventory8" id="rainventory8"></td></tr>
      </table>
    </td>
    <td colspan="2">
      <table width="100%">
        <tr><th>Non-Readily Accessible Inventory</th></tr>
        <tr><td><input type="text" name="nrainventory1" id="nrainventory1"></td></tr>
        <tr><td><input type="text" name="nrainventory2" id="nrainventory2"></td></tr>
        <tr><td><input type="text" name="nrainventory3" id="nrainventory3"></td></tr>
        <tr><td><input type="text" name="nrainventory4" id="nrainventory4"></td></tr>
        <tr><td><input type="text" name="nrainventory5" id="nrainventory5"></td></tr>
        <tr><td><input type="text" name="nrainventory6" id="nrainventory6"></td></tr>
        <tr><td><input type="text" name="nrainventory7" id="nrainventory7"></td></tr>
        <tr><td><input type="text" name="nrainventory8" id="nrainventory8"></td></tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" value="Don't give up now, you're almost there!"></td>
  </tr>
</table>
</form>
<table width="100%"><tr><td class="form"><p id="notify" class="errortext"></p></td></tr></table>

<script type="text/javascript">
function dataValidate() {
  var nameStr = document.getElementById("chdataname").value;
  nameStr = nameStr.replace(/[^a-zA-Z0-9' ]+/g,'');
  nameStr = nameStr.replace(/[']+/g,'&#39;');
  document.getElementById("chdataname").value = nameStr;
  if (nameStr.length > 0) {
    return true;
  } else {
    document.getElementById("notify").innerHTML = "Please provide a character name.";
    return false;
  }

}
</script>
