<?php

class User extends Model {

    protected $user_data = array();

    public function __construct(){
        parent::__construct();
        $user_id = Session::get('current_user');
        if( isset($user_id) ){
            $sql = "select * from userauth where id = '{$user_id}' limit 1";
            $user = $this->db->query($sql)[0];

            $this->user_data = $user;
        }
    }

    public function getUserData(){
        return $this->user_data;
    }

    public function getByLogin($login){
        $login = $this->db->escape($login);
        $sql = "select * from userauth where login = '{$login}' limit 1";
        $result = $this->db->query($sql);
        if ( isset($result[0]) ){
            $this->user = $result[0];
            return $result[0];
        }
        return false;
    }

    public function getById($id){
        $login_id = $this->db->escape($id);
        $sql = "select * from userauth where id = '{$login_id}' limit 1";
        $result = $this->db->query($sql);
        if ( isset($result[0]) ){
            $this->user = $result[0];
            return $result[0];
        }
        return false;
    }

    public function generateSalt(){
        $salt = sha1( (string)rand() );
        $login_id = $this->user['id'];
        $sql = "update userauth set salt = '{$salt}' where id ='{$login_id}'";
        $result = $this->db->query($sql);
        if ( $result ){
            return $salt;
        }
        return false;
    }

    public function getAuthHash(){
        $login_id = $this->user['id'];
        $sql = "select pass_hash, salt from userauth where id ='{$login_id}'";
        $result = $this->db->query($sql);
        if ( $result[0] ){
            return sha1( $result[0]['salt'].$result[0]['pass_hash']); //$result[0]['salt'] +
        }
        return false;
    }

    public function generateAuthCookie(){
        $login_id = $this->user['id'];
        $client_ip = $this->db->escape($_SERVER['REMOTE_ADDR']);
        $sql = "update userauth set client_ip = '{$client_ip}' where id ='{$login_id}'";
        $this->db->query($sql);
        $tmp = $this->user['pass_hash'].$this->user['salt'].$this->user['client_ip'];
        $cookie_data = $login_id.'_'.sha1($this->user['pass_hash'].$this->user['salt'].$this->user['client_ip']);
        setcookie('DOCbase_auth', $cookie_data, time()+60*30);
    }

    public static function isAuthorised(){
        $current_user_id = Session::get('current_user');
        if( !$current_user_id ){
            return false;
        }
        $sql = "select * from userauth where id = '{$current_user_id}' limit 1";
        $user = App::$db->query($sql)[0];
        if( isset($_COOKIE['DOCbase_auth']) ){
            $auth_cookie = $_COOKIE['DOCbase_auth'];
            $auth_data_parts = explode('_', $auth_cookie);
            $user_id = $auth_data_parts[0];
            $remote_data_sha1 = $auth_data_parts[1];
        }else{
            $user_id = '';
            $remote_data_sha1 = '';
        }
        //$tmp=$user['pass_hash'].$user['salt'].$_SERVER['REMOTE_ADDR'];
        $local_data_sha1 = sha1($user['pass_hash'].$user['salt'].$_SERVER['REMOTE_ADDR']);
        if($user['id'] == $user_id && $remote_data_sha1 == $local_data_sha1){
            return true;
        }
        return false;
    }

    public function getList(){
        $users_sql = "select userauth.id, userauth.login,
                        employees_personal_data.name, employees_personal_data.soname, employees_personal_data.surname
                        from userauth, employees_personal_data
                        where userauth.employee_id=employees_personal_data.id";
        $result = $this->db->query($users_sql);
        if(!count($result)){
            throw new Exception('It is unable to execute query in function '.__METHOD__);
        }
        return $result;
    }

}