<?php
if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.5                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2014                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/
function getCoutry($id) {
    if (isset($GLOBALS['COUTRY'][$id])) {
        $name = $GLOBALS['COUTRY'][$id];
    } else {
        $name = cmsDatabase::getInstance()->get_field('cms_geo_countries', "id = '". $id ."'", 'name');
        $GLOBALS['COUTRY'][$id] = $name;
    }

    if ($name) {
        return '<a href="index.php?view=components&do=config&link=geo&opt=edit&sub_opt=country&item_id='. $id .'">'. $name .'</a>';
    } else {
        return '--';
    }
}
function getRegion($id) {
    if (isset($GLOBALS['REGION'][$id])) {
        $name = $GLOBALS['REGION'][$id];
    } else {
        $name = cmsDatabase::getInstance()->get_field('cms_geo_regions', "id = '". $id ."'", 'name');
        $GLOBALS['REGION'][$id] = $name;
    }

    if ($name) {
        return '<a href="index.php?view=components&do=config&link=geo&opt=edit&sub_opt=region&item_id='. $id .'">'. $name .'</a>';
    } else {
        return '--';
    }
}

$opt = cmsCore::request('opt', 'str', 'countries');
$cfg = $inCore->loadComponentConfig('geo');

$toolmenu = array(
    array( 'icon' => 'geo/country.png', 'title' => $_LANG['AD_COUNTRIES'], 'link' => '?view=components&do=config&id='. $id ),
    array( 'icon' => 'geo/region.png', 'title' => $_LANG['AD_REGIONS'], 'link' => '?view=components&do=config&id='. $id .'&opt=regions' ),
    array( 'icon' => 'geo/city.png', 'title' => $_LANG['AD_CITIES'], 'link' => '?view=components&do=config&id='. $id .'&opt=cities' ),
    array( 'icon' => 'new.gif', 'title' => $_LANG['ADD'], 'link' => '?view=components&do=config&id='. $id .'&opt=add' )
);


if ($opt == 'countries'){
    $toolmenu[] = array( 'icon' => 'reorder.gif', 'title' => $_LANG['AD_SAVE_ORDER'], 'link' => "javascript:checkSel('?view=components&do=config&id=". $id ."&opt=saveorder');" );
}

$toolmenu[] = array( 'icon' => 'config.gif', 'title' => $_LANG['AD_SETTINGS'], 'link' => '?view=components&do=config&id='. $id .'&opt=config' );

cpToolMenu($toolmenu);


if ($opt == 'move_up'){
    dbMoveUp('cms_geo_countries', cmsCore::request('item_id', 'int', 0), cmsCore::request('co', 'int', 0));
    cmsCore::redirectBack();
}

if ($opt == 'move_down'){
    dbMoveDown('cms_geo_countries', cmsCore::request('item_id', 'int', 0), cmsCore::request('co', 'int', 0));
    cmsCore::redirectBack();
}

if ($opt == 'saveorder') {
    $ord = cmsCore::request('ordering', 'array_int', array());
    $ids = cmsCore::request('ids', 'array_int', array());

    foreach ($ord as $key => $ordering) {
        cmsCore::c('db')->query("UPDATE cms_geo_countries SET ordering = '". $ordering ."' WHERE id = '". $ids[$key] ."'");
    }

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

if ($opt == 'countries'){
    echo '<h3>'. $_LANG['AD_COUNTRIES'] .'</h3>';
    
    $fields = array(
        array( 'title' => 'id', 'field' => 'id', 'width' => '40' ),
        array( 'title' => $_LANG['TITLE'], 'field' => 'name', 'width' => '', 'filter' => '20', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=country&item_id=%id%' ),
        array( 'title' => 'alpha2', 'field' => 'alpha2', 'width' => '65' ),
        array( 'title' => 'alpha3', 'field' => 'alpha3', 'width' => '65' ),
        array( 'title' => 'iso', 'field' => 'iso', 'width' => '50' ),
        array( 'title' => $_LANG['AD_ORDER'], 'field' => 'ordering', 'width' => '100', 'do_suffix' => '', 'do' => 'opt' )
    );

    $actions = array(
        array( 'title' => $_LANG['EDIT'], 'icon' => 'edit.gif', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=country&item_id=%id%' ),
        array( 'title' => $_LANG['DELETE'], 'icon' => 'delete.gif', 'confirm' => $_LANG['AD_DELETE_COUNTRY'], 'link' => '?view=components&do=config&id='. $id .'&opt=delete&sub_opt=country&item_id=%id%' )
    );

    cpListTable('cms_geo_countries', $fields, $actions, '', 'ordering, name');
}

if ($opt == 'regions'){
    cpAddPathway($_LANG['AD_REGIONS']);
    echo '<h3>'. $_LANG['AD_REGIONS'] .'</h3>';

    $fields = array(
        array( 'title' => 'id', 'field' => 'id', 'width' => '40' ),
        array( 'title' => $_LANG['TITLE'], 'field' => 'name', 'width' => '', 'filter' => '20', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=region&item_id=%id%' ),
        array( 'title' => $_LANG['AD_COUNTRY1'], 'field' => 'country_id', 'width' => '120', 'filter' => '1', 'prc' => 'getCoutry', 'filterlist' => cpGetList('cms_geo_countries', 'name') )
    );
    
    $actions = array(
        array( 'title' => $_LANG['EDIT'], 'icon' => 'edit.gif', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=region&item_id=%id%' ),
        array( 'title' => $_LANG['DELETE'], 'icon' => 'delete.gif', 'confirm' => $_LANG['AD_DELETE_REGION'], 'link' => '?view=components&do=config&id='. $id .'&opt=delete&sub_opt=region&item_id=%id%' )
    );

    cpListTable('cms_geo_regions', $fields, $actions, '', 'name');
}

if ($opt == 'cities'){
    cpAddPathway($_LANG['AD_CITIES']);
    echo '<h3>'. $_LANG['AD_CITIES'] .'</h3>';

    $fields = array(
        array( 'title' => 'id', 'field' => 'id', 'width' => '40' ),
        array( 'title' => $_LANG['TITLE'], 'field' => 'name', 'width' => '', 'filter' => '20', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=city&item_id=%id%' ),
        array( 'title' => $_LANG['AD_COUNTRY1'], 'field' => 'country_id', 'width' => '130', 'filter' => '1', 'prc' => 'getCoutry', 'filterlist' => cpGetList('cms_geo_countries', 'name') ),
        array( 'title' => $_LANG['AD_REGION'], 'field' => 'region_id', 'width' => '160', 'filter' => '1', 'prc' => 'getRegion', 'filterlist' => cpGetList('cms_geo_regions', 'name') )
    );

    $actions = array(
        array( 'title' => $_LANG['EDIT'], 'icon' => 'edit.gif', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&sub_opt=city&item_id=%id%' ),
        array( 'title' => $_LANG['DELETE'], 'icon' => 'delete.gif', 'confirm' => $_LANG['AD_DELETE_CITY'], 'link' => '?view=components&do=config&id='. $id .'&opt=delete&sub_opt=city&item_id=%id%' )
    );

    cpListTable('cms_geo_cities', $fields, $actions, '', 'name');
}

if ($opt == 'config') {
    cpAddPathway($_LANG['AD_SETTINGS']);
?>
<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="optform">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    
    <div style="width:500px;">
        <div class="form-group">
            <label><?php echo $_LANG['AD_AUTODETECT']; ?></label>
            <div class="btn-group" data-toggle="buttons" style="float:right;">
                <label class="btn btn-default <?php if(cmsCore::getArrVal($cfg, 'autodetect', false)) { echo 'active'; } ?>">
                    <input type="radio" name="autodetect" <?php if(cmsCore::getArrVal($cfg, 'autodetect', false)) { echo 'checked="checked"'; } ?> value="1" /> <?php echo $_LANG['YES']; ?>
                </label>
                <label class="btn btn-default <?php if (!cmsCore::getArrVal($cfg, 'autodetect', false)) { echo 'active'; } ?>">
                    <input type="radio" name="autodetect" <?php if (!cmsCore::getArrVal($cfg, 'autodetect', false)) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label><?php echo $_LANG['AD_CLASS']; ?>:</label>
            <input type="text" id="maxitems" class="form-control" name="class" size="20" value="<?php echo $cfg['class']; ?>" />
            <div class="help-block"><?php echo $_LANG['AD_CLASS_HINT']; ?></div>
        </div>
    </div>
    
    <div>
        <input type="hidden" name="opt" value="saveconfig" />
        
        <input type="submit" class="btn btn-primary" name="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input type="button" class="btn btn-default" name="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>';"/>
    </div>
</form>
<?php
}

if ($opt == 'saveconfig') {
    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg = array();
    $cfg['autodetect'] = cmsCore::request('autodetect', 'int', 0);
    $cfg['class']      = cmsCore::request('class', 'str', 'geo');

    $inCore->saveComponentConfig('geo', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

//=================================================================================================//

if (mb_strstr($opt, 'do_')) {
    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $sub_opt = cmsCore::request('sub_opt', 'str', '');
    $item_id = cmsCore::request('item_id', 'int', 0);

    $table = ($sub_opt == 'country') ? 'cms_geo_countries' : ($sub_opt == 'region' ? 'cms_geo_regions' : 'cms_geo_cities');
    $redirect = ($sub_opt == 'country') ? 'countries' : ($sub_opt == 'region' ? 'regions' : 'cities');

    $types = array(
        'name' => array('name', 'str', ''),
        'alpha2' => array('alpha2', 'str', ''),
        'alpha3' => array('alpha3', 'str', ''),
        'iso' => array('iso', 'str', ''),
        'ordering' => array('ordering', 'int', 0),
        'region_id' => array('region_id', 'int', 0),
        'country_id' => array('country_id', 'int', 0)
    );

    $items = cmsCore::getArrayFromRequest($types);

    if ($opt == 'do_add') {
        cmsCore::c('db')->insert($table, $items);
    } else {
        cmsCore::c('db')->update($table, $items, $item_id);
    }

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='. $id .'&opt='. $redirect);
}

//=================================================================================================//

if ($opt == 'delete') {
    $sub_opt = cmsCore::request('sub_opt', 'str', '');
    $item_id = cmsCore::request('item_id', 'int', 0);

    if (!$sub_opt || !$item_id) { cmsCore::error404(); }

    $table = ($sub_opt == 'country') ? 'cms_geo_countries' : ($sub_opt == 'region' ? 'cms_geo_regions' : 'cms_geo_cities');
    $redirect = ($sub_opt == 'country') ? 'countries' : ($sub_opt == 'region' ? 'regions' : 'cities');

    cmsCore::c('db')->delete($table, "id='". $item_id ."'", 1);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='. $id .'&opt='. $redirect);
}

if ($opt=='add' || $opt == 'edit'){
    $sub_opt = cmsCore::request('sub_opt', 'str', '');
    $item_id = cmsCore::request('item_id', 'int', 0);

    if ($item_id) {
        $table = ($sub_opt == 'country') ? 'cms_geo_countries' : ($sub_opt == 'region' ? 'cms_geo_regions' : 'cms_geo_cities');

        $item = cmsCore::c('db')->get_fields($table, "id='". $item_id ."'", '*');
        if (!$item) { cmsCore::error404(); }
        cpAddPathway($_LANG['EDIT'] .' '. mb_strtolower($_LANG['AD_'. mb_strtoupper($sub_opt)]));
    } else if ($sub_opt) {
        cpAddPathway($_LANG['ADD'] .' '. mb_strtolower($_LANG['AD_'. mb_strtoupper($sub_opt)]));
    } else {
        cpAddPathway($_LANG['ADD']);
    }

    if(!$sub_opt && !$item_id){
?>
        <h3><?php echo $_LANG['AD_WHAT_ADD']; ?></h3>
        <ul style="font-size: 14px;">
            <li><a href="?view=components&do=config&id=<?php echo $id; ?>&opt=add&sub_opt=country"><?php echo $_LANG['AD_COUNTRY']; ?></a></li>
            <li><a href="?view=components&do=config&id=<?php echo $id; ?>&opt=add&sub_opt=region"><?php echo $_LANG['AD_REGION']; ?></a></li>
            <li><a href="?view=components&do=config&id=<?php echo $id; ?>&opt=add&sub_opt=city"><?php echo $_LANG['AD_CITY']; ?></a></li>
        </ul>
<?php
        return;
    }
?>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>" method="post" name="optform">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    
    <div style="width:400px;">
        <div class="form-group">
            <label><?php echo $_LANG['TITLE']; ?></label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars(cmsCore::getArrVal($item, 'name', '')); ?>" />
        </div>
        
        <?php if ($sub_opt == 'country') {
            if (!isset($item['ordering'])) {
                $item['ordering'] = 1 + cmsCore::c('db')->get_field('cms_geo_countries', "1=1 ORDER BY ordering DESC", 'ordering');
            }
        ?>
        
            <div class="form-group">
                <label>alpha2</label>
                <input type="text" class="form-control" name="alpha2" value="<?php echo htmlspecialchars(cmsCore::getArrVal($item, 'alpha2', '')); ?>" />
            </div>
        
            <div class="form-group">
                <label>alpha3</label>
                <input type="text" class="form-control" name="alpha3" value="<?php echo htmlspecialchars(cmsCore::getArrVal($item, 'alpha3', '')); ?>" />
            </div>
        
            <div class="form-group">
                <label>alpha3</label>
                <input type="text" class="form-control" name="iso" value="<?php echo htmlspecialchars(cmsCore::getArrVal($item, 'iso', '')); ?>" />
            </div>
        
            <div class="form-group">
                <label><?php echo $_LANG['AD_ORDER']; ?></label>
                <input type="text" class="form-control" name="ordering" value="<?php echo cmsCore::getArrVal($item, 'ordering', ''); ?>" />
            </div>
        <?php } else if($sub_opt == 'region') { ?>
            <div class="form-group">
                <label><?php echo $_LANG['AD_COUNTRY1']; ?></label>
                <select class="form-control" name="country_id">
                    <?php echo cmsCore::getListItems('cms_geo_countries', cmsCore::getArrVal($item, 'country_id', 0), 'name', 'ASC', '', 'id', 'name'); ?>
                </select>
            </div>
        <?php } else { ?>
            <div class="form-group">
                <label><?php echo $_LANG['AD_COUNTRY1']; ?></label>
                <select class="form-control" name="country_id">
                    <?php echo cmsCore::getListItems('cms_geo_countries', cmsCore::getArrVal($item, 'country_id', 0), 'name', 'ASC', '', 'id', 'name'); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label><?php echo $_LANG['AD_REGION']; ?></label>
                <select class="form-control" name="region_id">
                    <?php echo cmsCore::getListItems('cms_geo_regions', cmsCore::getArrVal($item, 'region_id', 0), 'name', 'ASC', (isset($item['country_id']) ? "country_id = '". $item['country_id'] ."'" : ''), 'id', 'name'); ?>
                </select>
            </div>
        <?php } ?>
    </div>

    <div>
        <input type="hidden" name="opt" value="do_<?php echo $opt; ?>" />
        <input type="hidden" name="sub_opt" value="<?php echo $sub_opt; ?>" />
        <input type="hidden" name="item_id" value="<?php echo cmsCore::getArrVal($item, 'id', ''); ?>" />
        
        <input type="submit" class="btn btn-primary" name="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input type="button" class="btn btn-default" name="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>';"/>
    </div>
</form>
<?php
}