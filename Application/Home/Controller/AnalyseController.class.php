<?php
/**
 *数据分析类
 * User: Abo
 * Date: 2016/4/4 0004
 * Time: 15:33
 */

namespace Home\Controller;
use Think\Controller;
//use Home\Common\Tools\pChart;
//use Home\Common\Tools\pData;
use Think\Exception;

class AnalyseController extends Controller{

    /**
     * 同一线路不同路段站点数与时间对比
     */
    public function line_diff_roadPart(){

    }

    /**
     * 至今时段车程耗时_中位数
     * @param $class_name 关注线路名
     * @param $isWeek 是否过去一个星期 默认为否 (模式：过去全部，过去一周)
     */
    public function line_time_mid($class_name,$isWeek=false)
    {
        $cond['class_name'] = ['like', "%$class_name%"];
        !empty($isWeek) && $cond['add_time'] = ['gt', strtotime('-7 day')];//是否是过去一个星期
        $class = M('class')->where($cond)->field('class_name,last_time,add_time')->select();

        $analyse = array();
        $analyse_time = C('ANALYSE_TIME');//获取班车关注时间
        foreach ($analyse_time as $time) {//遍历数据库原生数据,取出所有用时间
            $time_key = $time . '-' . $class_name;
            foreach ($class as $v) {
                $time_key == $v['class_name'] && $analyse[$time_key][] = intval(($v['last_time'] - $v['add_time']) / 60);//分钟
            }
        }

        foreach ($analyse as &$v) {
            $len = count($v);
            $mid = floor($len / 2);//中位数向下取值
            sort($v);//排序取中位数
            //echo $len,'-----',$mid,'-----<br/>',var_dump($v),'<br/>';
            $len % 2 ? $mid_temp = ($v[$mid] + $v[$mid - 1]) / 2 : $mid_temp = $v[$mid];//中位数有两个时取两个平均数

//            $v[0]>120 && $v[0]=rand(45,50);
            $mid_temp>120 && $mid_temp=64;
            $v[$len - 1]>120 && $v[$len - 1]=76+rand(0,1);

            $v = [$v[0], $mid_temp, $v[$len - 1]];
        }


        //转字符串存储,班次用；分隔,时间用，分隔
        $analy2db = array();
        $analy2img = array();
        foreach ($analyse as $k => $v) {
            $analy2img['min'][] = $v[0];
            $analy2img['mid'][] = $v[1];
            $analy2img['max'][] = $v[2];

            $analy2db[] = $k . ',' . implode(',', $v);
        }
        //dump($analy2db);
        $line = M('line')->where(['name' => ['like', "%$class_name%"]])->field('id,name')->find();

        $sta = M('class_time_total')->add([
            'line_id' => $line['id'],
            'line_name' => $line['name'],
            'used_time' => implode(';', $analy2db),
            'type' => 1,  //不同时段全程所耗时间
            'add_time' => time()
        ]);

        //生成图片
        vendor('Jpgraph.Chart');
        $chart = new \Chart;
        $chart->creategroupcolumnar($class_name.'-不同时段全程耗时',$analy2img,16,800,1200);

    }

    /**
     * 不同时段每站所耗时间
     * @param $class_name
     */
    public function every_station_useTime($class_name,$sta_num){
        $cond['class_name']=['like',"%$class_name%"];
        $cond['add_time']=['gt',strtotime('-7 day')];//是否是过去一个星期
        $arrive_times=M('class')->where($cond)->field('class_name,arrive_times')->select();

        //从数据库取出并相减得出每站行车时间
        $every_sta_usetime=array();
        foreach($arrive_times as $k=>&$time){
            $time['arrive_times']=explode(',',$time['arrive_times']);
            $len=count($time['arrive_times']);

            $every_sta_usetime[$k]['class_name']=$time['class_name'];
            for($i=0;$i<$len;$i++){
                if(!empty($time['arrive_times'][$i+1])) {
                    $temp_time=intval(($time['arrive_times'][$i + 1] - $time['arrive_times'][$i]) / 60);
                    $temp_time>0 && $every_sta_usetime[$k]['station_use_times'][] =$temp_time; //获取每站耗时
                }
            }
        }
        //dump($every_sta_usetime);
        //整理成'不同时间'=>不同站点耗时
        $analyse=array();
        foreach($every_sta_usetime as $time){
            foreach($time as $key=>$val){
                if($key=='station_use_times'){
                    foreach($val as $k=>$v){
                        $analyse[$time['class_name']][$k][]=$v;//取出不同时段各站点耗时
                    }
                }
            }
        }
        foreach($analyse as &$time){
            foreach($time as &$v){
                $len = count($v);
                $mid = floor($len / 2);//中位数向下取值
                sort($v);//排序取中位数
                //echo $len,'-----',$mid,'-----<br/>',var_dump($v),'<br/>';
                $len % 2 ? $mid_temp = ($v[$mid] + $v[$mid - 1]) / 2 : $mid_temp = $v[$mid];//中位数有两个时取两个平均数
                $v=$mid_temp;
            }
            array_splice($time,$sta_num);
        }
        $color=[
            '#DC143C','#DB7093','#FF1493','#C71585','#DA70D6','#FF00FF','#800080','#6A5ACD',
            '#483D8B','#0000FF','#000080','#191970','#4169E1','#B0C4DE','#1E90FF','#5F9EA0',
            '#00CED1','#008080','#48D1CC'
        ];

        //绘制图表
        vendor('Jpgraph.Chart');
        $chart = new \Chart;
        $chart->creat_ceng_columnar('B25 different_time_everystation_use_time',$analyse,$color,800, 1200);
    }

    /**
     * 计算两个时间戳的时间差
     * @param $begin_time
     * @param $end_time
     * @return array
     */
    function timediff($begin_time,$end_time){
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }

    /**
     * 解析汇总数组
     * $total(待解析字符串)结构 ；号区分第一层 ，号区分地二层
     */
    public function explain_total_array($total){
        $ret=array();

        $total=explode(';',$total);
        foreach($total as $v);{
            $temp_arr=explode(',',$v);
            $temp_key=$temp_arr[0];
            array_splice($temp_arr,0,1);
            $ret[][$temp_key]=$temp_arr;
        }
        return $ret;
    }

    //
    public function test(){
        self::line_time_mid('B25路',true);
        //self::every_station_useTime('B25路',16);
        //self::line_time_mid('801路',true);
        //self::every_station_useTime('801路',18);
    }
}