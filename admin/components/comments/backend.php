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

function cpStripComment($text) {
    $text = strip_tags($text);

    if (sizeof($text) < 120) { return $text; }

    return mb_substr($text, 0, 120) .'...';
}

function cpCommentAuthor($item) {
    if (!$item['user_id']) {
        $author = $item['guestname'];
    } else {
        $usersql = "SELECT id, nickname, login FROM cms_users WHERE id = ".$item['user_id'];
        $userres = cmsCore::c('db')->query($usersql);
        $u = cmsCore::c('db')->fetch_assoc($userres);
        $author = $u['nickname'].' (<a href="/admin/index.php?view=users&do=edit&id='.$u['id'].'" target="_blank">'.$u['login'].'</a>)';
    }
    
    return $author;
}

function cpCommentTarget($item){
    return '<a target="_blank" href="'.$item['target_link'].'#c'.$item['id'].'">'.$item['target_title'].'</a>';
}


$opt = cmsCore::request('opt', 'str', 'list');

$toolmenu = array(
    array( 'icon' => 'listcomments.gif', 'title' => $_LANG['AD_ALL_COMENTS'], 'link' => '?view=components&do=config&id='. $id .'&opt=list'),
    array( 'icon' => 'config.gif', 'title' => $_LANG['AD_SETTINGS'], 'link' => '?view=components&do=config&id='. $id .'&opt=config')
);

cpToolMenu($toolmenu);

cmsCore::loadModel('comments');
$model = new cms_model_comments();

$cfg = $model->config;

if ($opt == 'saveconfig') {
    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg['email']          = cmsCore::request('email', 'email', '');
    $cfg['regcap']         = cmsCore::request('regcap', 'int');
    $cfg['subscribe']      = cmsCore::request('subscribe', 'int');
    $cfg['min_karma'] 	   = cmsCore::request('min_karma', 'int');
    $cfg['min_karma_show'] = cmsCore::request('min_karma_show', 'int');
    $cfg['min_karma_add']  = cmsCore::request('min_karma_add', 'int');
    $cfg['perpage'] 	   = cmsCore::request('perpage', 'int');
    $cfg['cmm_ajax'] 	   = cmsCore::request('cmm_ajax', 'int');
    $cfg['cmm_ip']         = cmsCore::request('cmm_ip', 'int');
    $cfg['max_level'] 	   = cmsCore::request('max_level', 'int');
    $cfg['edit_minutes']   = cmsCore::request('edit_minutes', 'int');
    $cfg['watermark'] 	   = cmsCore::request('watermark', 'int');
    $cfg['meta_keys']      = cmsCore::request('meta_keys', 'str', '');
    $cfg['meta_desc']      = cmsCore::request('meta_desc', 'str', '');

    $inCore->saveComponentConfig('comments', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

if ($opt == 'show_comment') {
    $item_id = cmsCore::request('item_id', 'int', 0);
    cmsCore::c('db')->query("UPDATE cms_comments SET published = 1 WHERE id = '$item_id'");
    cmsCore::halt('1');
}

if ($opt == 'hide_comment') {
    $item_id = cmsCore::request('item_id', 'int', 0);
    cmsCore::c('db')->query("UPDATE cms_comments SET published = 0 WHERE id = '$item_id'") ;
    cmsCore::halt('1');
}

if ($opt == 'update') {
    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item_id = cmsCore::request('item_id', 'int', 0);

    $guestname = cmsCore::request('guestname', 'str', '');
    $pubdate   = cmsCore::request('pubdate', 'str');
    $published = cmsCore::request('published', 'int');
    $content   = cmsCore::c('db')->escape_string(cmsCore::request('content', 'html'));

    $sql = "UPDATE cms_comments
            SET guestname = '$guestname',
                pubdate = '$pubdate',
                published=$published,
                content='$content'
            WHERE id = $item_id
            LIMIT 1";
    cmsCore::c('db')->query($sql) ;

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('index.php?view=components&do=config&id='. $id .'&opt=list');
}

if ($opt == 'delete') {
    $model->deleteComment(cmsCore::request('item_id', 'int'));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('index.php?view=components&do=config&id='. $id .'&opt=list');
}

if ($opt == 'list') {
    if (cmsCore::inRequest('show_hidden')) {
        cpAddPathway($_LANG['AD_COMENTS_ON_MODERATE']);
        echo '<h3>'. $_LANG['AD_COMENTS_ON_MODERATE'] .'</h3>';
    } else {
        echo '<h3>'. $_LANG['AD_ALL_COMENTS'] .'</h3>';
    }

    $fields = array(
        array( 'title' => 'id', 'field' => 'id', 'width' => '40' ),
        array( 'title' => $_LANG['DATE'], 'field' => 'pubdate', 'width' => '100' ),
        array( 'title' => $_LANG['AD_TEXT'], 'field' => 'content', 'width' => '', 'prc' => 'cpStripComment' ),
        array( 'title' => $_LANG['AD_IP'], 'field' => 'ip', 'width' => '80' ),
        array( 'title' => $_LANG['AD_IS_PUBLISHED'], 'field' => 'published', 'width' => '70', 'do' => 'opt', 'do_suffix' => '_comment' ),
        array( 'title' => $_LANG['AD_AUTHOR'], 'field' => array('user_id', 'guestname'), 'width' => '180', 'prc' => 'cpCommentAuthor' ),
        array( 'title' => $_LANG['AD_AIM'], 'field' => array('target_title', 'target_link', 'id'), 'width' => '220', 'prc' => 'cpCommentTarget' )
    );
    
    $actions = array(
        array( 'title' => $_LANG['EDIT'], 'icon' => 'edit.gif', 'link' => '?view=components&do=config&id='. $id .'&opt=edit&item_id=%id%' ),
        array( 'title' => $_LANG['DELETE'], 'icon' => 'delete.gif', 'confirm' => $_LANG['AD_IF_COMENT_DELETE'], 'link' => '?view=components&do=config&id='. $id .'&opt=delete&item_id=%id%' )
    );

    $where = cmsCore::inRequest('show_hidden') ? 'published = 0' : '1 = 1';

    cpListTable('cms_comments', $fields, $actions, $where, 'pubdate DESC');
}

if ($opt == 'edit') {
    $mod = $model->getComment(cmsCore::request('item_id', 'int'));
    if (!$mod) { cmsCore::error404(); }

    if ($mod['user_id'] == 0) {
        $author = '<input name="guestname" type="text" id="title" size="30" value="'. $mod['guestname'] .'"/>';
    } else {
        $author = $mod['nickname'] .' (<a target="_blank" href="/admin/index.php?view=users&do=edit&id='. $mod['user_id'] .'">'. $mod['login'] .'</a>)';
    }

    cpAddPathway($_LANG['AD_EDIT_COMENT']);
    echo '<h3>'. $_LANG['AD_EDIT_COMENT'] .'</h3>';
?>

<form id="addform" class="form-horizontal" role="form" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id;?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <div style="width:650px;">
        <div class="form-group">
            <label class="col-sm-5 control-label"><?php echo $_LANG['AD_COMENT_AUTHOR'];?></label>
            <div class="col-sm-7">
                <p class="form-control"><?php echo $author; ?></p>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-5 control-label"><?php echo $_LANG['AD_CALENDAR_DATE'];?></label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="pubdate" size="30" value="<?php echo $mod['pubdate']; ?>" />
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-5 control-label"><?php echo $_LANG['AD_IF_COMENT_PUBLIC'];?></label>
            <div class="col-sm-7 btn-group" data-toggle="buttons">
                <label class="btn btn-default <?php if($mod['published']) { echo 'active'; } ?>">
                    <input type="radio" name="published" <?php if($mod['published']) { echo 'checked="checked"'; } ?> value="1" /> <?php echo $_LANG['YES']; ?>
                </label>
                <label class="btn btn-default <?php if (!$mod['published']) { echo 'active'; } ?>">
                    <input type="radio" name="published" <?php if (!$mod['published']) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                </label>
                <div style="clear:both;"></div>
                <div class="help-block"><?php echo $_LANG['AD_PUBLISH_CLUB_HINT']; ?></div>
            </div>
        </div>
        
        <div class="form-group">
            <?php cmsCore::insertEditor('content', $mod['content'], '250', '100%'); ?>
        </div>
    </div>
    <div>
        <input type="submit" class="btn btn-primary" name="add_mod" value="<?php echo $_LANG['SAVE'];?>" />
        <input type="button" class="btn btn-default" name="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.location.href='index.php?view=components';"/>
        
        <input type="hidden" name="opt" value="update" />
        <input type="hidden" name="item_id" value="<?php echo $mod['id']?>" />
    </div>
</form>
	<?php

}

if ($opt == 'config') {
    cpAddPathway($_LANG['AD_SETTINGS']);
    echo '<h3>'. $_LANG['AD_SETTINGS'] .'</h3>';
?>

<form id="form1" class="form-horizontal" role="form" name="optform" action="index.php?view=components&do=config&id=<?php echo $id; ?>" method="post" target="_self">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <div style="width:650px;" class="uitabs">
        <ul>
            <li><a href="#basic"><span><?php echo $_LANG['AD_OVERALL']; ?></span></a></li>
            <li><a href="#format"><span><?php echo $_LANG['AD_FORMAT']; ?></span></a></li>
            <li><a href="#access"><span><?php echo $_LANG['AD_TAB_ACCESS']; ?></span></a></li>
            <li><a href="#restrict"><span><?php echo $_LANG['AD_LIMIT']; ?></span></a></li>
            <li><a href="#seo"><span><?php echo $_LANG['AD_SEO']; ?></span></a></li>
        </ul>
        
        <div id="seo">
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_ROOT_METAKEYS']; ?>:</label>
                <div class="col-sm-7">
                    <textarea class="form-control" name="meta_keys" rows="2"><?php echo $cfg['meta_keys'] ?></textarea>
                    <div class="help-block"><?php echo $_LANG['AD_FROM_COMMA']; ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_ROOT_METADESC']; ?>:</label>
                <div class="col-sm-7">
                    <textarea class="form-control" name="meta_keys" rows="2"><?php echo $cfg['meta_desc'] ?></textarea>
                    <div class="help-block"><?php echo $_LANG['SEO_METADESCR_HINT']; ?></div>
                </div>
            </div>
        </div>

        <div id="basic">
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_COMENT_EMAIL']; ?></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" name="email" size="30" value="<?php echo $cfg['email']; ?>" />
                    <div class="help-block"><?php echo $_LANG['AD_NO_EMAIL']; ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_SUBSCRIPTION']; ?></label>
                <div class="col-sm-7 btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php if(cmsCore::getArrVal($cfg, 'subscribe', false)) { echo 'active'; } ?>">
                        <input type="radio" name="subscribe" <?php if(cmsCore::getArrVal($cfg, 'subscribe', false)) { echo 'checked="checked"'; } ?> value="1"> <?php echo $_LANG['YES']; ?>
                    </label>
                    <label class="btn btn-default <?php if (!cmsCore::getArrVal($cfg, 'subscribe', false)) { echo 'active'; } ?>">
                        <input type="radio" name="subscribe" <?php if (!cmsCore::getArrVal($cfg, 'subscribe', false)) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                    </label>
                    <div style="clear:both;"></div>
                    <div class="help-block"><?php echo $_LANG['AD_GET_MESSAGE']; ?></div>
                </div>
            </div>
        </div>

        <div id="format">
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_IF_AJAX']; ?></label>
                <div class="col-sm-7 btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php if(cmsCore::getArrVal($cfg, 'cmm_ajax', false)) { echo 'active'; } ?>">
                        <input type="radio" name="cmm_ajax" <?php if(cmsCore::getArrVal($cfg, 'cmm_ajax', false)) { echo 'checked="checked"'; } ?> value="1"> <?php echo $_LANG['YES']; ?>
                    </label>
                    <label class="btn btn-default <?php if (!cmsCore::getArrVal($cfg, 'cmm_ajax', false)) { echo 'active'; } ?>">
                        <input type="radio" name="cmm_ajax" <?php if (!cmsCore::getArrVal($cfg, 'cmm_ajax', false)) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_WATERMARK']; ?></label>
                <div class="col-sm-7 btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php if(cmsCore::getArrVal($cfg, 'watermark', false)) { echo 'active'; } ?>">
                        <input type="radio" name="watermark" <?php if(cmsCore::getArrVal($cfg, 'watermark', false)) { echo 'checked="checked"'; } ?> value="1"> <?php echo $_LANG['YES']; ?>
                    </label>
                    <label class="btn btn-default <?php if (!cmsCore::getArrVal($cfg, 'watermark', false)) { echo 'active'; } ?>">
                        <input type="radio" name="watermark" <?php if (!cmsCore::getArrVal($cfg, 'watermark', false)) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_ABOUT_NEW_COMENT']; ?></label>
                <div class="col-sm-7">
                    <?php echo '/languages/'. cmsConfig::getConfig('lang') .'/letters/newcomment.txt'; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_MAX_LEVEL']; ?></label>
                <div class="col-sm-7">
                    <input type="number" class="form-control" name="max_level" min="0" value="<?php echo $cfg['max_level'];?>" />
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_HOW_MANY_COMENTS']; ?></label>
                <div class="col-sm-7">
                    <input type="number" class="form-control" name="perpage" min="0" value="<?php echo $cfg['perpage'];?>" />
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_SHOW_IP']; ?></label>
                <div class="col-sm-7">
                    <select class="form-control" name="cmm_ip">
                        <option value="0" <?php if ($cfg['cmm_ip'] == 0) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_HIDE_IP'];?></option>
                        <option value="1" <?php if ($cfg['cmm_ip'] == 1) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_ONLY_GUEST_IP'];?></option>
                        <option value="2" <?php if ($cfg['cmm_ip'] == 2) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_ALL_IP'];?></option>
                    </select>
                </div>
            </div>
        </div>

        <div id="access">
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_NEED_CAPCA']; ?></label>
                <div class="col-sm-7">
                    <select class="form-control" name="regcap">
                        <option value="0" <?php if ($cfg['regcap'] == 0) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_FOR_GUEST'];?></option>
                        <option value="1" <?php if ($cfg['regcap'] == 1) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_FOR_ALL'];?></option>
                    </select>
                    <div class="help-block"><?php echo $_LANG['AD_USERS_CAPCA'];?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_DISALLOW_EDIT']; ?></label>
                <div class="col-sm-7">
                    <select class="form-control" name="edit_minutes">
                        <option value="0" <?php if (!$cfg['edit_minutes']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_AT_ONCE'];?></option>
                        <option value="1" <?php if ($cfg['edit_minutes'] == 1) { echo 'selected="selected"'; } ?>>1 <?php echo $_LANG['MINUTU1'];?></option>
                        <option value="5" <?php if ($cfg['edit_minutes'] == 5) { echo 'selected="selected"'; } ?>>5 <?php echo $_LANG['MINUTE10'];?></option>
                        <option value="10" <?php if ($cfg['edit_minutes'] == 10) { echo 'selected="selected"'; } ?>>10 <?php echo $_LANG['MINUTE10'];?></option>
                        <option value="15" <?php if ($cfg['edit_minutes'] == 15) { echo 'selected="selected"'; } ?>>15 <?php echo $_LANG['MINUTE10'];?></option>
                        <option value="30" <?php if ($cfg['edit_minutes'] == 30) { echo 'selected="selected"'; } ?>>30 <?php echo $_LANG['MINUTE10'];?></option>
                        <option value="60" <?php if ($cfg['edit_minutes'] == 60) { echo 'selected="selected"'; } ?>>1 <?php echo $_LANG['HOUR1'];?></option>
                    </select>
                    <div class="help-block"><?php echo $_LANG['AD_DISALLOW_TIMER'];?></div>
                </div>
            </div>
        </div>

        <div id="restrict">
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_USE_LIMIT']; ?></label>
                <div class="col-sm-7 btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php if(cmsCore::getArrVal($cfg, 'min_karma', false)) { echo 'active'; } ?>">
                        <input type="radio" name="min_karma" <?php if(cmsCore::getArrVal($cfg, 'min_karma', false)) { echo 'checked="checked"'; } ?> value="1"> <?php echo $_LANG['YES']; ?>
                    </label>
                    <label class="btn btn-default <?php if (!cmsCore::getArrVal($cfg, 'min_karma', false)) { echo 'active'; } ?>">
                        <input type="radio" name="min_karma" <?php if (!cmsCore::getArrVal($cfg, 'min_karma', false)) { echo 'checked="checked"'; } ?> value="0" /> <?php echo $_LANG['NO']; ?>
                    </label>
                    <div style="clear:both;"></div>
                    <div class="help-block"><?php echo $_LANG['AD_ALLOW_ALL']; ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_COMENT_ADD']; ?></label>
                <div class="col-sm-7">
                    <input type="number" class="form-control" name="min_karma_add" min="0" value="<?php echo $cfg['min_karma_add'];?>" />
                    <div class="help-block"><?php echo $_LANG['AD_HOW_MANY_KARMA'];?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-5 control-label"><?php echo $_LANG['AD_HIDE_COMENT']; ?></label>
                <div class="col-sm-7">
                    <input type="number" class="form-control" name="min_karma_show" value="<?php echo $cfg['min_karma_show'];?>" />
                    <div class="help-block"><?php echo $_LANG['AD_MIN_RATING'];?></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <input type="hidden" name="opt" value="saveconfig" />

        <input type="submit" class="btn btn-primary" name="save" value="<?php echo $_LANG['SAVE'];?>" />
        <input type="button" class="btn btn-default" name="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id; ?>';"/>
    </div>
</form>
<?php
}