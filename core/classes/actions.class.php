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

class cmsActions {
    private static $instance;

    private static $defaultLogArray = array('pubdate'=>'','user_id'=>'','object'=>'','object_url'=>'','object_id'=>'','target'=>'','target_url'=>'','target_id'=>'','description'=>'','is_friends_only'=>'','is_users_only'=>'');

    private $show_targets = true;
    private $only_friends = false;

// ============================================================================ //
// ============================================================================ //

    private function __construct() {}

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

// ============================================================================ //
// ============================================================================ //

    public static function checkLogArrayValues($input_array = array()) {
        if(!$input_array || !is_array($input_array)) { return array(); }

        // убираем ненужные ячейки массива
        foreach($input_array as $k=>$v){
            if (!isset(self::$defaultLogArray[$k])) { unset($input_array[$k]); continue; }
            $input_array[$k] =  preg_replace('/\[hide(.*?)\](.*?)\[\/hide\]/sui', '', $input_array[$k]);
            $input_array[$k] =  preg_replace('/\[hide(.*?)\](.*?)$/sui', '', $input_array[$k]);
            $input_array[$k] =  cmsCore::c('db')->escape_string(str_replace(array('\r', '\n'), ' ', $input_array[$k]));
        }

        return $input_array;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Регистрирует новый тип действия
     * @param str $component
     * @param array $action (name, title, message)
     * @return bool
     */
    public static function registerAction($component, $action){
        if (self::getAction($action['name'], false)) { return true; }
        
        $action['is_tracked'] = 1;
        $action['is_visible'] = 1;
        $action['component']  = $component;

        cmsCore::c('db')->insert('cms_actions', $action);

        return true;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Находит описание действия по его названию
     * @param str $action_name
     * @param bool $only_tracked
     * @return array | false
     */
    public static function getAction($action_name, $only_tracked=true){
        $tracked = $only_tracked ? 'AND is_tracked=1' : '';

        $action = cmsCore::c('db')->get_fields('cms_actions', "name='{$action_name}' {$tracked}", '*');

        return is_array($action) ? cmsCore::callEvent('GET_ACTION', $action) : false;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Добавляет действие в ленту активности
     * @param str $action_name
     * @param array $params (object, object_url, target, target_url, description)
     * @return bool
     */
    public static function log($action_name, $params){
        $params = self::checkLogArrayValues($params);
        if(!$params) { return false; }

        if (!cmsCore::c('user')->id && $action_name != 'add_user'){ return false; }

        $action = self::getAction($action_name);
        if (!$action) { return false; }

        $params['user_id']   =  !empty($params['user_id']) ? $params['user_id'] : cmsCore::c('user')->id;
        $params['action_id'] =  $action['id'];
        $params['pubdate']   =  date("Y-m-d H:i:s");

        cmsCore::c('db')->insert('cms_actions_log', cmsCore::callEvent('LOG_ACTION', $params));

        return true;
    }

    /**
     * Удаляет из ленты активности все события определенного типа для указанного объекта
     * @param string $action_name Тип события
     * @param int $object_id Идентификатор объекта
     * @return bool
     */
    public static function removeObjectLog($action_name, $object_id, $user_id = false){
        $arg = func_get_args();

        cmsCore::callEvent('DELETE_OBJECT_LOG', $arg);

        $action = self::getAction($action_name);

        $usr_sql = $user_id ? "AND user_id = {$user_id}" : '';

        cmsCore::c('db')->delete('cms_actions_log', "action_id = '{$action['id']}' AND object_id = '{$object_id}' $usr_sql");

        return true;
    }

    /**
     * Удаляет из ленты активности все события определенного типа для указанной цели
     * @param string $action_name Тип события
     * @param int $target_id Идентификатор цели
     * @return bool
     */
    public static function removeTargetLog($action_name, $target_id, $user_id = false){
        $arg = func_get_args();

        cmsCore::callEvent('DELETE_TARGET_LOG', $arg);

        $action = self::getAction($action_name);

        $usr_sql = $user_id ? "AND user_id = {$user_id}" : '';

        cmsCore::c('db')->delete('cms_actions_log', "action_id = '{$action['id']}' AND target_id = '{$target_id}' $usr_sql");

        return true;
    }

    public static function removeLogById($id){
        cmsCore::callEvent('DELETE_LOG', $id);

        return cmsCore::c('db')->delete('cms_actions_log', "id = '{$id}'");
    }

    /**
     * Обновляет запись ленты
     * @return bool
     */
    public static function updateLog($action_name, $params, $object_id=0, $target_id=0){
        $params = self::checkLogArrayValues($params);

        if(!$params) { return false; }
        if(!$object_id && !$target_id) { return false; }

        $arg = func_get_args();

        cmsCore::callEvent('UPDATE_LOG', $arg);

        // Получаем id записи
        $action = self::getAction($action_name);
        if (!$action) { return false; }
        $set = '';
        
        // формируем запрос на вставку в базу
        foreach($params as $field=>$value){
            $set .= "{$field} = '{$value}',";
        }
        $set = rtrim($set, ',');

        // если обновляем сам объект
        if($object_id){
            cmsCore::c('db')->query("UPDATE cms_actions_log SET {$set} WHERE action_id='{$action['id']}' AND object_id='{$object_id}' LIMIT 1");
        }
        // если обновляем все место назначения
        if($target_id){
            cmsCore::c('db')->query("UPDATE cms_actions_log SET {$set} WHERE action_id='{$action['id']}' AND target_id='{$target_id}'");
        }

        return true;
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Управляет показом категорий, в которых находятся объекты вызвавшие события
     * @param bool $show Показывать категории?
     *
     */
    public function showTargets($show) {
        $this->show_targets = $show;
        return;
    }

    /**
     * Включает режим показа только событий друзей
     *
     */
    public function onlyMyFriends(){
        $friends = cmsUser::getFriends(cmsCore::c('user')->id);
        if (!$friends){ cmsCore::c('db')->where('1=0'); return; }
        
        $f_list = array();

        foreach($friends as $friend){
            $f_list[] = $friend['id'];
        }
        
        $f_list = rtrim(implode(',', $f_list), ',');

        if ($f_list){
            cmsCore::c('db')->where("log.user_id IN ({$f_list})");
        } else {
            cmsCore::c('db')->where('1=0');
        }

        $this->only_friends = true;
        
        return;
    }

    /**
     * Показывает события определенного юзера
     *
     */
    public function whereUserIs($user_id){
        if ($user_id){
            cmsCore::c('db')->where("log.user_id = '$user_id'");
        } else {
            cmsCore::c('db')->where('1=0');
        }
        
        return;
    }

    public function onlySelectedTypes($types) {
        if (!is_array($types)){ cmsCore::c('db')->where('1=0'); return; }

        $t_list = array();

        foreach($types as $type){
            $t_list[] = $type;
        }
        
        $t_list = rtrim(implode(',', $t_list), ',');

        cmsCore::c('db')->where("a.id IN ({$t_list})");

        return;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает количество записей по условиям
     * @return int
     */
    public function getCountActions() {
        if (!$this->only_friends){ cmsCore::c('db')->where('log.is_friends_only = 0'); }
        if (!cmsCore::c('user')->id) { cmsCore::c('db')->where('log.is_users_only = 0'); }

        $sql = "SELECT 1
                    FROM cms_actions_log log
                    LEFT JOIN cms_actions a ON a.id = log.action_id AND a.is_visible = 1
                    WHERE 1=1 ". cmsCore::c('db')->where ."
                ";

        $result = cmsCore::c('db')->query($sql);

        return cmsCore::c('db')->num_rows($result);
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив событий для ленты активности
     * @return array
     */
    public function getActionsLog(){
        if (!$this->only_friends){ cmsCore::c('db')->where('log.is_friends_only = 0'); }
        if (!cmsCore::c('user')->id) { cmsCore::c('db')->where('log.is_users_only = 0'); }

        $sql = "SELECT log.id as id,
                       log.user_id,
                       log.object,
                       log.object_url,
                       log.target,
                       log.target_url,
                       log.pubdate,
                       log.description,
                       a.message,
                       a.name,
                       u.nickname as user_nickname,
                       u.login as user_login
                FROM cms_actions_log log
                LEFT JOIN cms_actions a ON a.id = log.action_id AND a.is_visible = 1
                LEFT JOIN cms_users u ON u.id = log.user_id
                WHERE 1=1 ". cmsCore::c('db')->where ."
                ORDER BY log.id DESC
				";

        if (cmsCore::c('db')->limit){
            $sql .= "LIMIT ". cmsCore::c('db')->limit;
        }

        $result = cmsCore::c('db')->query($sql);

		// Сбрасываем условия
        cmsCore::c('db')->resetConditions();

        if (!cmsCore::c('db')->num_rows($result)){ return false; }

        $actions = array();

        while($action = cmsCore::c('db')->fetch_assoc($result)){

            $action['object_link'] = $action['target_link'] = '';

            if ($action['object']){
                $action['object_link'] = $action['object_url'] ? '<a href="'.$action['object_url'].'" class="act_obj_'.$action['name'].'">'.$action['object'].'</a>' : $action['object'];
            }
            if ($action['target']){
                $action['target_link'] = '<a href="'.$action['target_url'].'" class="act_tgt_'.$action['name'].'">'.$action['target'].'</a>';
            }

            if($action['message']){

                $target_pos = mb_strpos($action['message'], '|');

                if($target_pos !== false){

                    if (!$this->show_targets || !$action['target']){
                        $action['message'] = mb_substr($action['message'], 0, $target_pos);
                    } else {
                        $action['message'] = str_replace('|', '', $action['message']);
                    }

                }

                $action['message'] = sprintf($action['message'], $action['object_link'], $action['target_link']);

            }

            $action['is_new']   = (bool) (strtotime($action['pubdate']) > strtotime(cmsCore::c('user')->logdate));
            $action['user_url'] = cmsUser::getProfileURL($action['user_login']);
            $action['pubdate']  = cmsCore::dateDiffNow($action['pubdate']);

            $actions[] = $action;

        }

        return cmsCore::callEvent('GET_ACTIONS', $actions);
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Удаляет старые записи ленты
     * @param int $pubdays
     * @return bool
     */
    public static function removeOldLog($pubdays = 60){
        return cmsCore::c('db')->delete('cms_actions_log', "DATEDIFF(NOW(), pubdate) > '{$pubdays}'");
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Удаляет из ленты записи одного пользователя
     * @param int $user_id
     * @return bool
     */
    public static function removeUserLog($user_id){
        if (!$user_id) { return false; }
        return cmsCore::c('db')->delete('cms_actions_log', "user_id = '$user_id'");
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Получает массив компонентов, зарегистрированных в ленте активности
     * @return array
     */
    public static function getActionsComponents(){
        return cmsCore::c('db')->get_table('cms_components com INNER JOIN cms_actions act ON act.component = com.link', 'com.internal=0 AND com.published=1 GROUP BY com.link', 'com.title, com.link');
    }

}