<?php
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

function mod_user_stats($mod, $cfg) {
    cmsCore::loadLanguage('components/users');

    global $_LANG;
    
    $cfg = array_merge(array(
        'show_total'  => 1,
        'show_online' => 1,
        'show_gender' => 1,
        'show_city'   => 1
    ), $cfg);

    $total_usr = cmsUser::getCountAllUsers();

    if ($cfg['show_gender']) {
        $gender_stats = array();
        //male
        $gender_stats['male'] = cmsCore::c('db')->rows_count('cms_users u INNER JOIN cms_user_profiles p ON p.user_id = u.id', "u.is_locked = 0 AND u.is_deleted = 0 AND p.gender = 'm'");
        //female
        $gender_stats['female'] = cmsCore::c('db')->rows_count('cms_users u INNER JOIN cms_user_profiles p ON p.user_id = u.id', "u.is_locked = 0 AND u.is_deleted = 0 AND p.gender = 'f'");
        //unknown
        $gender_stats['unknown'] = $total_usr - $gender_stats['male'] - $gender_stats['female'];
    }

    if ($cfg['show_city']) {
        $sql = "SELECT IF (p.city != '', p.city, '". $_LANG['NOT_DECIDE'] ."') city, COUNT( p.user_id ) count
                FROM cms_users u
                LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE u.is_locked =0 AND u.is_deleted =0
                GROUP BY p.city";
        $rs = cmsCore::c('db')->query($sql);

        $city_stats = array();

        if (cmsCore::c('db')->num_rows($rs)) {
            while ($row = cmsCore::c('db')->fetch_assoc($rs)) {
                if ($row['city'] != $_LANG['NOT_DECIDE']) {
                    $row['href'] = '/users/city/'.urlencode($row['city']);
                } else {
                    $row['href'] = '';
                }
                
                $row['city'] = icms_ucfirst(mb_strtolower($row['city']));
                
                $city_stats[] = $row;
            }
        }

    }

    if ($cfg['show_online']) {
        $people = cmsUser::getOnlineCount();
    }

    if ($cfg['show_bday']) {
        $bday = cmsUser::getBirthdayUsers();
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
        assign('cfg', $cfg)->
        assign('total_usr', $total_usr)->
        assign('gender_stats', $gender_stats)->
        assign('city_stats', $city_stats)->
        assign('usr_online', cmsUser::sessionGet('usr_online'))->
        assign('people', $people)->
        assign('bday', $bday)->
        display();

    return true;
}