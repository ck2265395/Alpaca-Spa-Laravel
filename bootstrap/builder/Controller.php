<?php

namespace Builder;

use Builder\View\View;
use Illuminate\Support\Facades\DB;

class Controller
{
    public $ignoreField = ['status','is_del', 'update_time', 'audit_time', 'create_time', 'delete_time', 'creator', 'updater', 'ip', 'level'];

    public function index()
    {
        $view         = View::tbl('index', ['name' => 'test']);
        $view->layout = false;
        echo $view->html();
        die();
    }

    public function builder()
    {

        $table_name           = request()->input('table_name');
        $table_prefix         = request()->input('table_prefix','');
        $table_name_cn        = request()->input('table_name_cn');
        $sub_module_name      = request()->input('sub_module_name');
        $b_module_name        = request()->input('b_module_name');
        $f_module_name        = request()->input('f_module_name');
        $is_init_model        = request()->input('is_init_model');
        $is_init_b_controller = request()->input('is_init_b_controller');
        $is_init_b_router     = request()->input('is_init_b_router');
        $is_init_list         = request()->input('is_init_list');
        $is_init_edit         = request()->input('is_init_edit');
        $is_init_f_controller = request()->input('is_init_f_controller');
        $is_init_inter        = request()->input('is_init_inter');

        if(empty($table_name)){
            die('Table Name Is Null!');
        }


        $orgName = $table_name;
        $table   = str_replace(' ', '', $orgName);

        $className    = ucwords(str_replace(array('.', '-', '_'), ' ', $table));
        $moduleName   = explode(',', $className)[0];
        $className    = str_replace(' ', '', $className);
        $classNameLc  = lcfirst($className);
        $moduleNameLc = lcfirst($moduleName);
        $result       = DB::select("SHOW FULL COLUMNS FROM " . $table_prefix . $table);

        $fields = [];
        foreach ($result as $r) {

            if (in_array($r->Field, $this->ignoreField)) {
                continue;
            }

            $field            = [];
            $field['db_type'] = $r->Type;
            $field['field']   = $r->Field;
            $field['comment'] = $r->Comment;
            $field['in_type'] = 'string';
            $field['in_name'] = $r->Comment;

            $comment = str_replace('，', ',', $r->Comment);
            if (strstr($comment, '枚举-')) {
                $field['in_type'] = 'enum';
                $enums            = explode(',', $comment);
                $field_enum       = [];
                foreach ($enums as $index => $en) {
                    if ($index == 0) {
                        $field['in_name'] = explode('-', $en)[1];
                        continue;
                    }
                    $key            = explode('=', $en);
                    $enum           = [];
                    $enum['value']  = $key[0];
                    $enum['label']  = (isset($key[1]) ? $key[1] : $key[0]);
                    $enum['key']    = $r->Field . "_" . (isset($key[2]) ? $key[2] : (isset($key[1]) ? $key[1] : $key[0]));
                    $enum['common'] = '//' . $enums[0] . ":" . (isset($key[1]) ? $key[1] : $key[0]);
                    $enum['const']  = 'const ' . strtoupper($enum['key']) . ' = ' . $key[0] . ';    ' . $enum['common'];
                    array_push($field_enum, $enum);
                }
                $field['in_enum'] = $field_enum;
            }
            array_push($fields, $field);
        }

        //创建目录
        $filePath = base_path() . "\\bootstrap\\builder\\output\\{$table}\\";
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $data                 = [];
        $data['tableName']    = "tb_" . $table;
        $data['fields']       = $fields;
        $data['className']    = $className;
        $data['moduleName']   = $moduleName;
        $data['classNameLc']  = $classNameLc;
        $data['moduleNameLc'] = $moduleNameLc;
        $data['orgName']      = $orgName;


        //创建 - model
        if(!empty($is_init_model)){
            $view         = View::tbl('model', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_M';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*后端模型，目录*/
            $models_dir       = base_path() . "\\app\\Models\\";
            $models_file_name = $className;
            if (!file_exists($models_dir . $models_file_name . '.php')) {
                copy($fileName, $models_dir . $models_file_name . '.php');
            } else {
                copy($fileName, $models_dir . $models_file_name . '');
            }
        }


        //创建 - controller
        if(!empty($is_init_b_controller)){
            $view         = View::tbl('controller', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_Controller';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*后端Controller，目录*/
            $controller_dir       = base_path() . "\\app\\Modules\\Manage\\Controllers\\";
            $controller_file_name = $className . 'Controller';
            if (!file_exists($controller_dir . $controller_file_name . '.php')) {
                copy($fileName, $controller_dir . $controller_file_name . '.php');
            } else {
                copy($fileName, $controller_dir . $controller_file_name . '');
            }

        }

        //创建 - list
        if(!empty($is_init_list)){
            //创建 - list_view
            $view         = View::tbl('list_view', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_ListView';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*前端Controller，目录*/
            $display_dir       = base_path() . "\\public\\admin\\main\\view\\" . $classNameLc . "\\";
            if (!is_dir($display_dir)) {
                mkdir($display_dir, 0777, true);
            }
            $display_file_name = $classNameLc . 'ListView';
            if (!file_exists($display_dir . $display_file_name . '.html')) {
                copy($fileName, $display_dir . $display_file_name . '.html');
            } else {
                copy($fileName, $display_dir . $display_file_name . '');
            }

            //创建 - list_display
            $view         = View::tbl('list_display', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_ListDisplay';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*前端Controller，目录*/
            $display_dir       = base_path() . "\\public\\admin\\main\\view\\" . $classNameLc . "\\";
            if (!is_dir($display_dir)) {
                mkdir($display_dir, 0777, true);
            }
            $display_file_name = $classNameLc . 'ListDisplay';
            if (!file_exists($display_dir . $display_file_name . '.html')) {
                copy($fileName, $display_dir . $display_file_name . '.html');
            } else {
                copy($fileName, $display_dir . $display_file_name . '');
            }
        }

        //创建 - Edit-html
        if(!empty($is_init_edit)){
            $view         = View::tbl('edit_html', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_EditView';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*前端Controller，目录*/
            $edit_dir       = base_path() . "\\public\\admin\\main\\view\\" . $classNameLc . "\\";
            if (!is_dir($edit_dir)) {
                mkdir($edit_dir, 0777, true);
            }
            $edit_file_name = $classNameLc . 'EditView';
            if (!file_exists($edit_dir . $edit_file_name . '.html')) {
                copy($fileName, $edit_dir . $edit_file_name . '.html');
            } else {
                copy($fileName, $edit_dir . $edit_file_name . '');
            }
        }


        //创建 - Controller - JS
        if(!empty($is_init_f_controller)){
            $view         = View::tbl('controller_js', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_Controller_JS';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*前端Controller，目录*/
            $controller_js_dir       = base_path() . "\\public\\admin\\main\\controller\\";
            $controller_js_file_name = $classNameLc . '';
            if (!file_exists($controller_js_dir . $controller_js_file_name . '.js')) {
                copy($fileName, $controller_js_dir . $controller_js_file_name . '.js');
            } else {
                copy($fileName, $controller_js_dir . $controller_js_file_name . '');
            }

        }
        //创建 - 后台路由
        if(!empty($is_init_b_router)){
            //创建 - Router
            $view         = View::tbl('router', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_Router';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
            /*拷贝到指定目录*/
            /*前端Controller，目录*/
            $router_name = base_path() . "\\app\\Modules\\Manage\\router.php";;
            if (!file_exists($router_name)) {

            } else {
                file_put_contents($router_name, $html, FILE_APPEND | LOCK_EX);
            }
        }

        //创建 - config_js
        if(!empty($is_init_inter)){
            $view         = View::tbl('config_js', $data);
            $view->layout = false;
            $html         = $view->html();
            $fileName     = $filePath . $className . '_Config_js';
            echo $fileName;
            var_dump($html);
            file_put_contents($fileName, $html, LOCK_EX);
        }


        //设置菜单
        die();
    }
}