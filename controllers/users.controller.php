<?php

class UsersController extends Controller{

    public function __construct($data = array()){
        parent::__construct($data);
        $this->model = new User();
    }

    public function logout(){
        Session::destroy();
        Router::redirect('/');
    }

    public function login(){
        if ( $_POST && isset($_POST['login']) && !isset($_POST['password']) ){
            if(!($user_data = $this->model->getByLogin($_POST['login']))){
                throw new Exception('This login is not registered: '.$_POST['login']);
            }
            $salt = $this->model->generateSalt($_POST['login']);
            Session::set('current_user', $user_data['id']);
            $this->data = $salt;
            echo $salt;
            exit;
        }
        if ( $_POST && isset($_POST['login']) && isset($_POST['password']) ){
            $this->model->getByLogin($_POST['login']);
            $pass_hash = $this->model->getAuthHash();
            if($_POST['password'] != $pass_hash){
                throw new Exception('Your password is wrong! Try once more.');
            }
            $this->model->generateAuthCookie();
            if($_POST['login'] == 'admin'){
                Router::redirect('admin/users/index');
                exit;
            }
            Router::redirect('pages/index');
        }
    }

    public function admin_index(){
        if(!User::isAuthorised()){
            Router::redirect('/');
            exit;
        }
        $this->data['users'] = $this->model->getList();
    }

}