<?php
include __ROOT_PATH__.'/DB.php';
$config = array(
    //样式文件配置
	'__PUBLIC_PATH__'=>__ROOT_PATH__.'/Public',
    '__IMG__'=>__PUBLIC_PATH__.'/images',
    '__CSS__'=>__PUBLIC_PATH__.'/assets/css',
    '__JS__'=>__PUBLIC_PATH__.'/assets/js',

    'SHOW_PAGE_TRACE'=> true,

    /**
     * 分析线路
     * 801vsB25（中部枢纽，体育西）
     * B1 VS 517 (市八十六中，珠江新城)
     */
    'ATTEND_LINE'=>array('801','B25','B1','517'),

    //分析时段
    'ANALYSE_TIME'=>['06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24']

);
return array_merge($config,$db);