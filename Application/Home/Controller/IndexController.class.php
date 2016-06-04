<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/7 0007
 * Time: 7:31
 */

namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller{
    public function index(){
        $this->display();
    }
}