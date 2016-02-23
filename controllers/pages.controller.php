<?php

class PagesController extends Controller{

    public function __construct($data = array()){
        parent::__construct($data);
        $this->model = new Page();
    }

    public function index(){
        if(!User::isAuthorised()){
            Router::redirect('/');
            exit;
        }
        $this->data['enterprises'] = $this->model->getList('enterprises');
        $this->data['department_struct'] = $this->model->getList('department_struct');
        $this->data['enterprise_info'] = $this->model->getEnterpriseInfo('akkalita');
        $this->data['holdings'] = $this->model->getList('holding_struct');
    }

    public function view(){
        $params = App::getRouter()->getParams();

        if ( isset($params[0]) ){
            $alias = strtolower($params[0]);
            $this->data['page'] = $this->model->getByAlias($alias);
        }
    }

    public function admin_add(){
        if ( $_POST ){
            $result = $this->model->save($_POST);
            if ( $result ){
                Session::setFlash('Page was saved.');
            } else {
                Session::setFlash('Error.');
            }
            Router::redirect('/admin/pages/');
        }
    }

    public function admin_edit(){

        if ( $_POST ){
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $result = $this->model->save($_POST, $id);
            if ( $result ){
                Session::setFlash('Page was saved.');
            } else {
                Session::setFlash('Error.');
            }
            Router::redirect('/admin/pages/');
        }

        if ( isset($this->params[0]) ){
            $this->data['page'] = $this->model->getById($this->params[0]);
        } else {
            Session::setFlash('Wrong page id.');
            Router::redirect('/admin/pages/');
        }
    }

    public function admin_delete(){
        if ( isset($this->params[0]) ){
            $result = $this->model->delete($this->params[0]);
            if ( $result ){
                Session::setFlash('Page was deleted.');
            } else {
                Session::setFlash('Error.');
            }
        }
        Router::redirect('/admin/pages/');
    }


    public function ajax_getstructure($params){
        $params_parts = explode('-', $params[0]);
        if(count($params_parts) == 1){
            $result = $this->model->getList('meta_structure');
            foreach($result as $key => $val){
                $result[$key]['current_table'] = 'meta_structure';
            }
        }else{
            $dept_id = $this->model->getList('department_struct', "alias = '$params_parts[0]'")[0]['id'];
            array_shift($params_parts);
            $target_table = array_pop($params_parts);
            $parent_branch_alias = array_pop($params_parts);
            $parent_table = array_pop($params_parts);

            if($target_table == $parent_table){
                $parent_branch_id = $this->model->getList($parent_table, "alias = '$parent_branch_alias'");
                $result = $this->model->getList($target_table, "dept_id = $dept_id and parent_id = {$parent_branch_id[0]['id']}");
            }else{
                $result = $this->model->getList($target_table, "parent_id is null and dept_id = $dept_id");
            }

        }

        $this->data['content'] = json_encode($result);
    }

    public function ajax_getdata($params){
        $params_parts = explode('-', $params[0]);
        $enterprise_title = $this->model->getList('enterprises', 'alias = "'.$params_parts[0].'"');
        array_shift($params_parts);
        $action_title = $this->model->getList('activities', 'successor_table = "'.implode('_', $params_parts).'"');
        $table_header = $action_title[0]['title'].': '.$enterprise_title[0]['organization_form'].' '.$enterprise_title[0]['title'];
        $result = $this->model->getList( implode($params) );
        foreach($result as $key => $value){
            $contractor = $this->model->getList('contractors', "id={$result[$key]['contractor_number']}");
            $result[$key]['contractor_title'] = $contractor[0]['organization_form'].' '.$contractor[0]['title'].' (ЄДРПОУ '.$contractor[0]['id'].')';
        }
        array_unshift($result, $table_header);
        $this->data['content'] = $result;
    }

    public function ajax_getagrdata($params){
        $params_parts = explode('-', $params[0]);
        $enterprise = $params_parts[0];
        $table_name = $params_parts[0].'-'.$params_parts[1];
        $agr_id = $params_parts[2];

        $enterprise_data = $this->model->getList('enterprises', "alias='{$enterprise}'");
        $agr_data = $this->model->getList($table_name, "id={$agr_id}");
        $contractor_data = $this->model->getList('contractors', "id={$agr_data[0]['contractor_number']}");
        $result = array('enterprise_data'=>$enterprise_data[0],
                        'agr_data'=>$agr_data[0],
                        'contractor_data'=>$contractor_data[0]);
        $this->data['content'] = $result;
    }

    public function img($params){
        //Router::redirect("ajax/img/".$params);
        //$_SERVER['REQUEST_URI'] = "ajax/img/".$params;
        App::run("ajax/pages/img/".$params[0]);
        die;
    }

    public function ajax_img($file){
        $file_path = "img/".$file[0];
        if (file_exists($file_path)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: filename="'.basename($file_path).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        }
    }

    public function ajax_editagrdata($params){
        //echo "operation executed: edition complete.";
        $params_parts = explode('-', $params[0]);
        $enterprise = $params_parts[0];
        $table_name = $params_parts[0].'-'.$params_parts[1];
        $agr_id = $params_parts[2];

        $enterprise_data = $this->model->getList('enterprises', "alias='{$enterprise}'");
        $agr_data = $this->model->getList($table_name, "id={$agr_id}");
        $contractor_data = $this->model->getList('contractors', "id={$agr_data[0]['contractor_number']}");
        $result = array('enterprise_data'=>$enterprise_data[0],
            'agr_data'=>$agr_data[0],
            'contractor_data'=>$contractor_data[0]);
        foreach($result as $key=>$value){
            foreach($value as $inner_key => $inner_value){
                $value[$inner_key] = str_replace("\"", "&quot", $inner_value);
            }
            $result[$key] = $value;
        }
        $this->data['content'] = $result;
    }

    public function ajax_deleteagrdata($params){
        echo "operation executed: data deleted.";
    }
}