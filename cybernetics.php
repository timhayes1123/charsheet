<?php
include_once $controller->getController($pageToLoad);
?>
<form action="charcreate.php" name="progressskills" id="progressskills" novalidate method="post">
<input type="hidden" name="characterobj" value="<?php echo htmlspecialchars(json_encode($characterObj));?>" />
<input type="hidden" id="page" name="page" value="final" />
<table width="100%">
  <tr class="oddrow"><th colspan="2">Implant 1</th><th colspan="2">Implant 2</th><th colspan="2">Implant 3</th></tr>
  <tr>
    <td>Location:</td><td><input type="text" name="implant1" id="implant1"></td>
    <td>Location:</td><td><input type="text" name="implant2" id="implant2"></td>
    <td>Location:</td><td><input type="text" name="implant3" id="implant3"></td>
  </tr>
  <tr>
    <td>Effect:</td><td><input type="text" name="implant1eff" id="implant1eff"></td>
    <td>Effect:</td><td><input type="text" name="implant2eff" id="implant2eff"></td>
    <td>Effect:</td><td><input type="text" name="implant3eff" id="implant3eff"></td>
  </tr>
  <tr>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant1slots" id="implant1slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant2slots" id="implant2slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant3slots" id="implant3slots"></td>
  </tr>
  <tr>
    <td>Mod 1:</td><td><input type="text" name="implant1mod1" id="implant1mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant2mod1" id="implant2mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant3mod1" id="implant3mod1"></td>
  </tr>
  <tr>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant1mod1eff" id="implant1mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant2mod1eff" id="implant2mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant3mod1eff" id="implant3mod1eff"></td>
  </tr>
  <tr>
    <td>Mod 2:</td><td><input type="text" name="implant1mod2" id="implant1mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant2mod2" id="implant2mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant3mod2" id="implant3mod2"></td>
  </tr>
  <tr>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant1mod2eff" id="implant1mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant2mod2eff" id="implant2mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant3mod2eff" id="implant3mod2eff"></td>
  </tr>
  <tr>
    <td>Mod 3:</td><td><input type="text" name="implant1mod3" id="implant1mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant2mod3" id="implant2mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant3mod3" id="implant3mod3"></td>
  </tr>
  <tr>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant1mod3eff" id="implant1mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant2mod3eff" id="implant2mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant3mod3eff" id="implant3mod3eff"></td>
  </tr>
  <!----              IMPLANT ROW         ------->
  <tr class="oddrow"><th colspan="2">Implant 4</th><th colspan="2">Implant 5</th><th colspan="2">Implant 6</th></tr>
  <tr>
    <td>Location:</td><td><input type="text" name="implant4" id="implant4"></td>
    <td>Location:</td><td><input type="text" name="implant5" id="implant5"></td>
    <td>Location:</td><td><input type="text" name="implant6" id="implant6"></td>
  </tr>
  <tr>
    <td>Effect:</td><td><input type="text" name="implant4eff" id="implant4eff"></td>
    <td>Effect:</td><td><input type="text" name="implant5eff" id="implant5eff"></td>
    <td>Effect:</td><td><input type="text" name="implant6eff" id="implant6eff"></td>
  </tr>
  <tr>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant4slots" id="implant4slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant5slots" id="implant5slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant6slots" id="implant6slots"></td>
  </tr>
  <tr>
    <td>Mod 1:</td><td><input type="text" name="implant4mod1" id="implant4mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant5mod1" id="implant5mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant6mod1" id="implant6mod1"></td>
  </tr>
  <tr>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant4mod1eff" id="implant4mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant5mod1eff" id="implant5mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant6mod1eff" id="implant6mod1eff"></td>
  </tr>
  <tr>
    <td>Mod 2:</td><td><input type="text" name="implant4mod2" id="implant4mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant5mod2" id="implant5mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant6mod2" id="implant6mod2"></td>
  </tr>
  <tr>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant4mod2eff" id="implant4mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant5mod2eff" id="implant5mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant6mod2eff" id="implant6mod2eff"></td>
  </tr>
  <tr>
    <td>Mod 3:</td><td><input type="text" name="implant4mod3" id="implant4mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant5mod3" id="implant5mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant6mod3" id="implant6mod3"></td>
  </tr>
  <tr>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant4mod3eff" id="implant4mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant5mod3eff" id="implant5mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant6mod3eff" id="implant6mod3eff"></td>
  </tr>
<!--      IMPLANT ROW  ------>
  <tr class="oddrow"><th colspan="2">Implant 7</th><th colspan="2">Implant 8</th><th colspan="2">Implant 9</th></tr>
  <tr>
    <td>Location:</td><td><input type="text" name="implant7" id="implant7"></td>
    <td>Location:</td><td><input type="text" name="implant8" id="implant8"></td>
    <td>Location:</td><td><input type="text" name="implant9" id="implant9"></td>
  </tr>
  <tr>
    <td>Effect:</td><td><input type="text" name="implant7eff" id="implant7eff"></td>
    <td>Effect:</td><td><input type="text" name="implant8eff" id="implant8eff"></td>
    <td>Effect:</td><td><input type="text" name="implant9eff" id="implant9eff"></td>
  </tr>
  <tr>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant7slots" id="implant7slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant8slots" id="implant8slots"></td>
    <td>Mod Slots:</td><td><input type="number" min="1" max="3" name="implant9slots" id="implant9slots"></td>
  </tr>
  <tr>
    <td>Mod 1:</td><td><input type="text" name="implant7mod1" id="implant7mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant8mod1" id="implant8mod1"></td>
    <td>Mod 1:</td><td><input type="text" name="implant9mod1" id="implant9mod1"></td>
  </tr>
  <tr>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant7mod1eff" id="implant7mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant8mod1eff" id="implant8mod1eff"></td>
    <td>Mod 1 Effect:</td><td><input type="text" name="implant9mod1eff" id="implant9mod1eff"></td>
  </tr>
  <tr>
    <td>Mod 2:</td><td><input type="text" name="implant7mod2" id="implant7mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant8mod2" id="implant8mod2"></td>
    <td>Mod 2:</td><td><input type="text" name="implant9mod2" id="implant9mod2"></td>
  </tr>
  <tr>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant7mod2eff" id="implant7mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant8mod2eff" id="implant8mod2eff"></td>
    <td>Mod 2 Effect:</td><td><input type="text" name="implant9mod2eff" id="implant9mod2eff"></td>
  </tr>
  <tr>
    <td>Mod 3:</td><td><input type="text" name="implant7mod3" id="implant7mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant8mod3" id="implant8mod3"></td>
    <td>Mod 3:</td><td><input type="text" name="implant9mod3" id="implant9mod3"></td>
  </tr>
  <tr>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant7mod3eff" id="implant7mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant8mod3eff" id="implant8mod3eff"></td>
    <td>Mod 3 Effect:</td><td><input type="text" name="implant9mod3eff" id="implant9mod3eff"></td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" value="YOU MADE IT!"></td>
  </tr>
</table>
</form>
