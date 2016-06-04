<?php
/**
 * 数据收集类 Abo 2016/03/26
 * Time: 15:45
 */
namespace Home\Controller;
set_time_limit(0);
use Think\Controller;
use Think\Exception;

class DataCollectController extends Controller{
    /**
     * 从第三巴士公司获取信息
     * http://local.com/gra/index.php/Home/Data/get_busLine_info
     * @param $flag 线路标记数
     * @param null $url 信息访问路径
     * @return mixed
     */
    public function get_thirth_info($flag,$url=null){
        $ch=curl_init();
        $cookie=";WBSRV='s3';route='fe9b13b33d88398957ee445b97555283'";
        $referer='http://wxbus.gzyyjt.net/wei-bus-app/route/monitor/'.$flag.'/0';
        empty($url) && $url ='http://wxbus.gzyyjt.net/wei-bus-app/routeStation/getByRouteAndDirection/'.$flag.'/0';

        $setting=array(
            CURLOPT_URL=>$url,
            CURLOPT_REFERER=>$referer,
            CURLOPT_COOKIE=>$cookie,
            CURLOPT_RETURNTRANSFER=>true    //以文件流形式返回而非直接打印
        );

        try {
            if (!curl_setopt_array($ch, $setting)) throw new Exception('请求'.$url.'设置出错');
            $data = curl_exec($ch);  //$info  =  curl_getinfo ( $ch );//获取执行信息，可用于调试
            if (curl_errno($ch)) throw new Exception('发送请求到 '.$url.'出错');// 检查是否有错误发生
        }catch (Exception $e){
            $err=['error_code'=>1,'msg'=>$e->getMessage(),'add_time'=>date('Y-m-d H-i-s',time())];
            M('error_log')->add($err);
        }
        curl_close($ch);
        return $data;
    }

    /**
     * 获取所有可行线路2数据库
     */
    public function get_all_line_info(){
        $add_num=1;
        for($i=9831;$i<30000;$i++){
            $data=self::get_thirth_info($i);
            //当获取内容为数组是
            $line=json_decode($data,true);
            is_array($line) && $res=self::handle_lineInfo2base($line,$i);
            $res>0 && $add_num=$add_num++;
            echo '已增加 ',$add_num,' 条数据<br/>';
        }
    }

    /**
     * 线路数据存储到数据库
     * @param $data 数据
     * @param $i 线路标记
     */
    public function handle_lineInfo2base($data,$i){
        $temp_line=array();
        foreach($data['l'] as $v){
            $temp_line[]=$v['n'];
        }
        stripos($data['rn'],'B')===0 ? $is_b=1:$is_b=0;//是否死brt

        $line=[
            'name'=>$data['rn'],
            'stime'=>$data['ft'],
            'etime'=>$data['lt'],
            'stations'=>implode ( ',',$temp_line),
            'line_info_url'=>'http://wxbus.gzyyjt.net/wei-bus-app/routeStation/getByRouteAndDirection/'.$i.'/0',
            'route_bus_url'=>'http://wxbus.gzyyjt.net/wei-bus-app/runBus/getByRouteAndDirection/'.$i.'/0',
            'is_brt'=>$is_b,
            'add_time'=>time()
        ];

        M('line')->add($line);
    }
/*
    public function test(){
        $testData='{"rn":"B25路","d":"0","c":"80250","ft":"0700","lt":"2200","l":[{"i":"136255","n":"大学城(中部枢纽)总站","sni":"1840","si":"2349"},{"i":"136263","n":"广大公寓站","sni":"2498","si":"2948"},{"i":"136264","n":"广大生活区站","sni":"2501","si":"2950"},{"i":"136256","n":"广大站","sni":"2502","si":"2951"},{"i":"136265","n":"华师站","sni":"1132","si":"5133"},{"i":"136266","n":"星海学院站","sni":"3094","si":"975"},{"i":"205910","n":"地铁大学城北站","sni":"1615","si":"12580"},{"i":"136257","n":"仑头立交站","sni":"714","si":"3363"},{"i":"136267","n":"琶洲大桥北站","sni":"4156","si":"3813"},{"i":"136258","n":"科韵路站","sni":"4660","si":"6127"},{"i":"136268","n":"学院站","sni":"2143","si":"1019"},{"i":"136269","n":"上社站","sni":"157","si":"4300"},{"i":"136270","n":"华景新城站","sni":"1140","si":"5081"},{"i":"136271","n":"师大暨大站","sni":"2403","si":"4385"},{"i":"136272","n":"岗顶站","sni":"2333","si":"2865"},{"i":"136259","n":"石牌桥站","sni":"4511","si":"4498"},{"i":"136260","n":"体育中心站","sni":"743","si":"266"},{"i":"136261","n":"体育中心总站","sni":"742","si":"6359"}]}';
        $data=json_decode($testData,true);
        stripos('B25','B')===0 ? $is_b=1:$is_b=0;//是否死brt
        echo stripos('B25'),'||',$is_b;
        //self::handle_lineInfo2base($data,2);
    }
*/
}
