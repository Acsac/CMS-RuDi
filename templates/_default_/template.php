<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.4                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2014                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

    /*
     * Доступны объекты $inCore $inUser $inPage($this) $inConf $inDB
     */

    // Получаем количество модулей на нужные позиции
    $mod_count['top']     = $this->countModules('top');
    $mod_count['topmenu'] = $this->countModules('topmenu');
    $mod_count['sidebar'] = $this->countModules('sidebar');

    // Подключаем стили шаблона
    $this->addHeadCSS('templates/'. TEMPLATE .'/css/reset.css');
    $this->addHeadCSS('templates/'. TEMPLATE .'/css/text.css');
    $this->addHeadCSS('templates/'. TEMPLATE .'/css/960.css');
    $this->addHeadCSS('templates/'. TEMPLATE .'/css/styles.css');
    // Подключаем colorbox (просмотр фото)
    $this->addHeadJS('includes/jquery/colorbox/jquery.colorbox.js');
    $this->addHeadCSS('includes/jquery/colorbox/colorbox.css');
    $this->addHeadJS('includes/jquery/colorbox/init_colorbox.js');
    // LANG фразы для colorbox
    $this->addHeadJsLang(array('CBOX_IMAGE','CBOX_FROM','CBOX_PREVIOUS','CBOX_NEXT','CBOX_CLOSE','CBOX_XHR_ERROR','CBOX_IMG_ERROR', 'CBOX_SLIDESHOWSTOP', 'CBOX_SLIDESHOWSTART'));
    
    if (cmsCore::c('user')->is_admin){
        $this->addHeadJS('admin/js/modconfig.js');
        $this->addHeadJS('templates/'. TEMPLATE .'/js/nyromodal.js');
        $this->addHeadCSS('templates/'. TEMPLATE .'/css/modconfig.css');
        $this->addHeadCSS('templates/'. TEMPLATE .'/css/nyromodal.css');
    }
    
    // подключаем jQuery и js ядра в самое начало
    $this->prependHeadJS('core/js/common.js');
    $this->prependHeadJS('includes/jquery/jquery.js');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" prefix="og: http://ogp.me/ns# video: http://ogp.me/ns/video# ya: http://webmaster.yandex.ru/vocabularies/">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php $this->printHead('    '); ?>
</head>

<body>
<?php if ($inConf->siteoff && $inUser->is_admin) { ?>
<div style="margin:4px; padding:5px; border:solid 1px red; background:#FFF; position: fixed;opacity: 0.8; z-index:999"><?php echo $_LANG['SITE_IS_DISABLE']; ?></div>
<?php } ?>
    <div id="wrapper">

        <div id="header">
            <div class="container_12">
                <div class="grid_2">
                    <div id="sitename"><a href="/"></a></div>
                </div>
                <div class="grid_10">
                    <?php if ($inConf->is_change_lang){

                        $langs = cmsCore::getDirsList('/languages'); ?>
                        <div onclick="$('#langs-select').toggle().toggleClass('active_lang');$(this).toggleClass('active_lang'); return false;" title="<?php echo $_LANG['TEMPLATE_INTERFACE_LANG']; ?>" id="langs" style="background-image:  url(/templates/<?php echo TEMPLATE; ?>/images/icons/langs/<?php echo $inConf->lang; ?>.png);">
                            <span>&#9660;</span>
                            <ul id="langs-select">
                                <?php foreach ($langs as $lng) { ?>
                                <li onclick="setLang('<?php echo $lng; ?>'); return false;" style="background-image:  url(/templates/<?php echo TEMPLATE; ?>/images/icons/langs/<?php echo $lng; ?>.png);"><?php echo $lng; ?></li>
                                <?php } ?>
                            </ul>
                        </div>

                    <?php } ?>
                    <?php $this->printModules('header'); ?>
                </div>
            </div>
        </div>

        <div id="page">

            <?php if($mod_count['topmenu']) { ?>
            <div class="container_12" id="topmenu">
                <div class="grid_12">
                    <?php $this->printModules('topmenu'); ?>
                </div>
            </div>
            <?php } ?>

            <?php if ($mod_count['top']){ ?>
            <div class="clear"></div>

            <div id="topwide" class="container_12">
                <div class="grid_12" id="topmod"><?php $this->printModules('top'); ?></div>
            </div>
            <?php } ?>

            <div id="pathway" class="container_12">
                <div class="grid_12"><?php $this->printPathway('&rarr;'); ?></div>
            </div>

            <div class="clear"></div>

            <div id="mainbody" class="container_12">
                <div id="main" class="<?php if ($mod_count['sidebar']) { ?>grid_8<?php } else { ?>grid_12<?php } ?>">
                    <?php $this->printModules('maintop'); ?>

                    <?php $messages = cmsCore::getSessionMessages(); ?>
                    <?php if ($messages) { ?>
                    <div class="sess_messages" id="sess_messages">
                        <?php foreach($messages as $message){ ?>
                            <?php echo $message; ?>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if($this->page_body){ ?>
                        <div class="component">
                             <?php $this->printBody(); ?>
                        </div>
                    <?php } ?>
                    <?php $this->printModules('mainbottom'); ?>
                </div>
                <?php if ($mod_count['sidebar']) { ?>
                    <div class="grid_4" id="sidebar"><?php $this->printModules('sidebar'); ?></div>
                <?php } ?>
            </div>

        </div>

    </div>

    <div id="footer">
        <div class="container_12">
            <div class="grid_8">
                <div id="copyright"><?php $this->printSitename(); ?> &copy; <?php echo date('Y'); ?></div>
            </div>
            <div class="grid_4 foot_right">
                <a href="http://www.instantcms.ru/" title="<?php echo $_LANG['POWERED_BY_INSTANTCMS']; ?>" target="_blank">
                    <img src="/templates/<?php echo TEMPLATE; ?>/images/b88x31.gif" border="0"/>
                </a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function(){
            $('#sess_messages').hide().fadeIn();
            $('#topmenu .menu li').hover(
                function() {
                    $(this).find('ul:first').fadeIn('fast');
                    $(this).find('a:first').addClass("hover");
                },
                function() {
                    $(this).find('ul:first').hide();
                    $(this).find('a:first').removeClass("hover");
                }
            );
        });
    </script>
    <?php if($inConf->debug && $inUser->is_admin){
        $time = $inCore->getGenTime(); ?>
        <div class="debug_info">
            <div class="debug_time">
                <?php echo $_LANG['DEBUG_TIME_GEN_PAGE'].' '.number_format($time, 4).' '.$_LANG['DEBUG_SEC']; ?>
            </div>
            <div class="debug_memory">
                <?php echo $_LANG['DEBUG_MEMORY'].' '.round(@memory_get_usage()/1024/1024, 2).' '.$_LANG['SIZE_MB']; ?>
            </div>
            <div class="debug_query_count">
                <a href="#debug_query_dump" class="ajaxlink" onclick="$('#debug_query_dump').toggle();return false;"><?php echo $_LANG['DEBUG_QUERY_DB'] .' '. $inDB->q_count; ?></a>
            </div>
            <div id="debug_query_dump">
                <?php echo $inDB->q_dump; ?>
            </div>
        </div>
    <?php } ?>
</body>
</html>