<?php
namespace Home\Controller;
use Think\Controller;
use Think\Exception;

/**
 * 班次控制类 Abo 2016/3/26
 * Time: 18:58
 */
class ClassController extends  DataCollectController{
    private $attend_line=array();//关注线路
    private $attend_class=array();//关注班次

    public function __construct($line_name,$db_id=0){
        if (empty($line_name)) return false;
        $param['name'] = ['like', ["$line_name%"]];
        $line_name=='B1' && $param['id'] = 418;
        $route_bus_url = M('line')->where($param)->field('id,route_bus_url,name')->find();

        preg_match('/\d{1,4}/', $route_bus_url['route_bus_url'], $matches, PREG_OFFSET_CAPTURE);//获取巴士公司的该线路标识
        $this->attend_line = [
            'bus_company_flag' => $matches[0][0],
            'route_bus_url' => $route_bus_url['route_bus_url'],
            'id' => $route_bus_url['id'],
            'name' => $route_bus_url['name']
        ];
    }

    /**
     * 存入关注班次
     * @param $id 巴士公司该班次标识
     * @param $key  巴士所在站点标识（实时数据数组下标）
     */
    private function set_attend_class($id,$key){
        $_SESSION['attend_class'][$id]=$key;
    }
    //移除关注班次
    private function remove_attend_class($id){
        unset($_SESSION['attend_class'][$id]);
        echo 'unset',$id;
    }
    //得到关注列表
    private function get_attend_class(){
        return $_SESSION['attend_class'];
    }

    /**
     * 定时获取实时数据
     */
    public function route_get_class(){
        //取得实时公交数据
        $temp=$this->get_thirth_info($this->attend_line['bus_company_flag'],$this->attend_line['route_bus_url']);
        $route_data=json_decode($temp,true);
        $len=count($route_data)-2;

        $attend_class=$this->get_attend_class();
dump($attend_class);
        foreach ($attend_class as $key => &$val) {  //$key:巴士公司识别id,$val:班次巴士上次位置
            foreach ($route_data as $k => $v) {     //$k:实时站点位置,$v['bl']['0']['i'] 或 $v['bbl']['0']['i']:巴士公司识别id
                //停靠或驶离站点但未到达下一站，班次一样但位置不同了
                $flag1=($key==$v['bl']['0']['i'] && $val!=$k);
                $flag2=($key==$v['bbl']['0']['i'] && $val!=$k);

                if($flag1 || $flag2) {
                    self::updata_attend_class($key, $k, $len);
                    !empty($v['bl']['0']['i']) && $ci=$v['bl']['0']['i'];
                    !empty($v['bbl']['0']['i']) && $ci=$v['bbl']['0']['i'];
                    M('metadata')->add(['class_id'=>$key,'attend_class'=>implode(',',array_keys($attend_class)),'arr_data'=>$temp,'add_time'=>time()]);
                }
            }
        }
        $this->first_add_attend($route_data);
    }

    /**
     * 班次位置变动，修改关注班次&数据库
     * @param $class_id 班次id
     * @param $real_time_position 班次实时位置
     * @param $len 线路站点总长
     */
    public function updata_attend_class($class_id,$real_time_position,$len){
        //更换对象班次位置数据，当到达重点终点站时退出关注班次序列
        $len==$real_time_position ? $this->remove_attend_class($class_id) : $_SESSION['attend_class'][$class_id]=$real_time_position;
dump($len,'||',$real_time_position);
        try {
            //查询数据库班次位置记录数据
            $param = ['class_id' => $class_id];
            $arrive_times = M('class')->where($param)->field('id,arrive_times')->order('add_time desc')->find();

            //更新数据库班次位置记录数据
            $saveInfo = ['arrive_times' => $arrive_times['arrive_times'] . ','.time(), 'last_time' => time()];
            $res=M('class')->where(['id'=>$arrive_times['id']])->save($saveInfo);
echo '<br/>更新:',M()->getLastSql();
            if(!$res) throw new Exception('更新gra_class.arrive_times失败'.M()->getLastSql());
        } catch (Exception $e) {
            $error = ['error_code' => 10, 'msg' => $e->getMessage(),'add_time'=>date('Y-m-d H-i-s',time())];
            M('error_log')->add($error);
        }
    }

    /**
     * 把刚起点上的巴士存入关注列表
     * @param $route_data 实时公交数据
     */
    public function first_add_attend($route_data){
        //取得在首发站或首发站路上班次id
        !empty($route_data[0]['bl'][0]['i']) && $id[] = $route_data[0]['bl'][0]['i'];
        !empty($route_data[0]['bbl'][0]['i']) && $id[] = $route_data[0]['bbl'][0]['i'];
        try {
            if (empty(array_filter($id))) return false;
            foreach($id as $v) {
                if(!array_key_exists($v,$_SESSION['attend_class'])){
                    $this->set_attend_class($v, 0);//添加到关注班次队列
                    //添加到数据库班次表
                    $time = time();
                    $addData = [
                        'line_id' => $this->attend_line['id'],
                        'class_id' => $v,
                        'class_name' => date('H', time()) . '-' . $this->attend_line['name'],
                        'arrive_times' => $time,
                        'last_time' => $time,
                        'add_time' => $time
                    ];
                    $res = M('class')->add($addData);
echo '<br/>插入:',M()->getLastSql();
                    if (!$res) throw new Exception('班次id：' . $v . '班次名：' . date('H', time()) . $this->attend_line['name'] . '添加数据库gra_class表失败');
                }
            }
        } catch (Exception $e) {
            $error = ['error_code' => 10, 'msg' => $e->getMessage(), 'add_time' => time()];
            M('error_log')->add($error);
        }
    }

    //除去数组空值之后的判断
    public function test(){
        $str1='[{"bl":[{"i":"1933147","si":"784171","t":"1"}],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[{"i":"1933149","si":"784171","t":"1"}],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[{"i":"1933151","si":"784171","t":"1"},{"i":"1933148","si":"784171","t":"1"}]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[{"i":"1933152","si":"784171","t":"1"}],"bbl":[{"i":"1933154","si":"784171","t":"1"}]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]}]';
        $str2='[{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[{"i":"1933149","si":"784171","t":"1"}],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[{"i":"1933151","si":"784171","t":"1"},{"i":"1933148","si":"784171","t":"1"}]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[{"i":"1933152","si":"784171","t":"1"}],"bbl":[{"i":"1933154","si":"784171","t":"1"}]},{"bl":[],"bbl":[]},{"bl":[],"bbl":[]},{"bl":[{"i":"1933147","si":"784171","t":"1"}],"bbl":[]}]';

        self::route_get_class($str1);
        self::route_get_class($str2);
        echo 'session';
        dump($_SESSION);
    }
}
