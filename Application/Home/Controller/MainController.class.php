<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\ClassController;
class MainController extends ClassController {

    public $obj=array();

    public function __construct(){
        $attend_line=C('ATTEND_LINE');
        foreach($attend_line as $v) {
            $this->obj[] = new ClassController($v);//新增关注班次
        }
    }

    /**
     * www.local.com/gra/index.php/Home/main/route2log
     */
    public function route2log(){
        $obj=$this->obj;
        foreach($obj as $v){
            $v->route_get_class();
        };
    }

}
$main=new MainController();