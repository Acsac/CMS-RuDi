<form id="mod_usr_search_form" method="post" action="/users">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
            <td valign="middle">
                <select name="gender" id="gender" style="width:260px" class="text-input">
                    <option value="f"><?php echo $_LANG['FIND']; ?> <?php echo $_LANG['FIND_FEMALE']; ?></option>
                    <option value="m"><?php echo $_LANG['FIND']; ?> <?php echo $_LANG['FIND_MALE']; ?></option>
                    <option value="0" selected><?php echo $_LANG['FIND']; ?> <?php echo $_LANG['FIND_ALL']; ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input style="width:120px" name="agefrom" type="text" id="agefrom" value="" placeholder="<?php echo $_LANG['AGE_FROM']; ?>" class="text-input" />
                <input style="width:125px" name="ageto" type="text" id="ageto" value="" placeholder="<?php echo $_LANG['TO']; ?>" class="text-input" />
            </td>
        </tr>
        <tr>
            <td>
                <input style="width:255px" id="name" name="name" type="text" value="" placeholder="<?php echo $_LANG['NAME']; ?>" class="text-input" />
            </td>
        </tr>
        <tr>
            <td>
                <?php echo cmsCore::city_input(array('name' => 'city', 'width' => '255px', 'input_width' => '120px', 'placeholder' => $_LANG['CITY'])) ?>
            </td>
        </tr>
        <tr>
            <td>
                <input style="width:255px" id="hobby" name="hobby" type="text" value="" placeholder="<?php echo $_LANG['HOBBY']; ?>" class="text-input" />
            </td>
        </tr>
        <tr>
            <td align="center">
                <input name="gosearch" type="submit" id="gosearch" value="<?php echo $_LANG['SEARCH']; ?>" />
            </td>
        </tr>
    </table>
</form>