1）开发内容：
数据爬取模块：包括curl请求获取数据、第三巴士公司巴士线路爬取、爬取数据解析并存储到数据库。
线路班次跟踪模块：包括设置关注线路、设置正在行驶关注班次、移除关注班次、得到关注列表、定时获取实时数据、记录关注班次位置变得时间、把刚起点上的巴士存入关注列表。
数据分析模块： 包括线路全程耗时_中位数，不同时段每站所耗时间，时间戳差值计算。
主控制模块：包括实例化关注线路Class对象，逐个取出关注线路的行驶班次定时实时数据。
定时任务模块：定时访问主控制模块route2log()方法，

1.3开发工具的概述
    该项目的数据接口采用php语言来开发，环境：php5.6+mysql5.5+apache2.4,开发使用了phpstorm,,使用了THINKPHP轻量级框架，图表处理使用了jpgphe，架构采用传统的mvc模式，可以在数据收集的同时查看数据收集情况，访问特定路线路径能对数据进行分析汇总并以多样图表等直观形式展示了各线路班次的行驶耗时时间。

    2.2.功能需求
    2.2.1定时任务模块
    在Linux的contab，Window的系统定时，第三方的定时访问，php的死循环+sleep中再三比对，基于可实时观察数据读取，系统运行情况的考虑选定了使用javascript的setinterval方法，定时发起目标网络请求，并展示返回其爬取的数据、当前的关注线路班次列表、线路基本信息、访问路径、系统运行情况等各项内容。
    2.2.2主控制模块
    接收定时模块请求并开始读取预算config文件中的关注线路信息，并实例化各关注线路的班次对象，检查出行在起点班次级以存在于关注班次队列中班车的实时位置变化做出相应记录并储存在数据库。在班车到底终点站时，将该班车及时移出班车关注队，等待下一步对象析构。
    2.2.3数据爬取模块
    实例化curl对象，并初始化，被主控制模块调用时，结合其线路班车信息，生成如下规格的“第三巴士公司”合法接口请求，请求获取实时公交行驶导致数据、并兼顾着“第三巴士公司”旗下巴士线路爬取、线路数据解析、线路数据存储到数据库。
    2.2.4线路班次跟踪模块
    根据每个实例化并储存在注册表内的班次对象调用线路班次跟踪模块内的Class::route_get_class()方法定时启动网络爬虫数据爬取方法，爬取该关注线路内班车的行驶到站情况。并于关注列表中班车的之前到达位置进行比对，当不对等的时候意味着班车的行进。此时需要解析此次爬取线路信息并将变化时间储存到数据库内以作记录。同时还兼具着设置关注线路、设置正在行驶关注班次、移除关注班次、得到关注列表、定时获取实时数据、记录关注班次位置变得时间、把刚起点上的巴士存入关注列表。
    2.2.5数据分析模块
    根据数据库内之前爬取的班次行驶到站信息，以中位数，平均数汇总方式；以同出发地、同目的地的线路与线路间对比；线路中BRT路段与普通路段对比；BRT线路与普通线路对比等对比方式，三种不同的角度对比得出数据汇总图表等直观，容易理解的表达方式。供人们参考，得出自己的结论。    

3.1数据库设计

3.1.3班次行驶到站_原始数据表
   从班次出现在线路起点、被添加到班次关注队列的时候，该班次每个十五/三十秒定时任务访问“第三巴士公司实时公交接口”的返回json数据都将被作为记录都添加该表，如图下所示：

gra_metadata：班次行驶到站原始数据表
字段	类型	为空(是：否)	约束	描述
id	int(8)	否	PRIMARY	主键
class_id	int(8)	否	INDEX	巴士id，gra_class.class_id
attend_class	varchar(800)	否	无	关注班车队列
arr_date	text	否	无	行驶数组
add_time	varchar(10)	否	无	添加时间

3.1.3班次行驶到站_情况表
   从班次出现在起点和每一次到展示，程序解析从“第三巴士公司实时公交接口”的返回原生json数据，处理返回以字符串格式的数组，记录班次出发时间，并且在班次每次到站的时候会将当时到站时刻逐个添加到数据库表表内，如图下所示：

gra_class_time_total：班次行驶到站_情况表
字段	类型	为空(是：否)	约束	描述
id	int(8)	否	PRIMARY	主键
line_id	int(5)	否	无	巴士线路id gra_line.id
class_id	int（8）	否	无	巴士公司标识id（巴士班次id）
class_name	varchar(10)	否	无	班次名(当天时间+巴士名)
arrive_times	varchar(550)	是	无	班次所有站点到站时刻，用逗号分隔，
last_time	varchar(10)	否	无	最后更新时间（到站时间）
add_time	varchar(10)	否	无	添加时间（发车时间）

3.1.4班次行驶到站_数据汇总表
   当数据分析模块启动对某线路的数据分析时，gra_class中该线路的一段时间段内的数据都将被取出整合处理，按求平均值，中位数的方式按特定目标归类数据，如图下所示：

gra_class_time_total：班次行驶到站_情况表
字段	类型	为空(是：否)	约束	描述
id	int(8)	否	PRIMARY	主键
line_id	int(5)	否	无	巴士线路id gra_line.id
class_id	int（8）	否	无	巴士公司标识id（巴士班次id）
class_name	varchar(10)	否	无	班次名(当天时间+巴士名)
arrive_times	varchar(550)	是	无	班次所有站点到站时刻，用逗号分隔，
last_time	varchar(10)	否	无	最后更新时间（到站时间）
add_time	varchar(10)	否	无	添加时间（发车时间）

3.1.6公交线路表
   当系统发生需要关注的异常时将被记录存储到该表内，如图下所示：

gra_line：公交线路表
字段	类型	为空(是：否)	约束	描述
id	int(5)	否	PRIMARY	主键
name	varchar（10）	否	无	线路名
stime	varchar(10)	否	无	首发车时间
etime	varchar(10)	否	无	末班车时间
space_time	varchar(10)	否	无	发车间隔时间
stations	varchar（500）	否	无	所有站点字符串[数组]（以逗号，分隔）
lin_info_url	varchar（120）	否	无	获取线路信息url
route_bus_url	varchar（120）	否	无	获取班车到站信息url
is_brt	tinyint	否	无	是否为brt线路 1：是 0：否
add_time	varchar（10）	否	无	线路添加时间

3.1.6系统错误表
   当系统发生需要关注的异常时将被记录存储到该表内，如图下所示：

gra_error_log：系统错误表
字段	类型	为空(是：否)	约束	描述
id	int(8)	否	PRIMARY	主键
error_code	int(3)	否	无	错误编码
1：CURL设置错误
2：CURL执行出错
10：起点插入标识为空
11：更新class_arriver_time失败
20：非可控异常
msg	varchar(150)	否	无	错误概要信息
add_time	varchar(10)	否	无	错误添加时间

3.2项目目录分析
gra  项目部署根目录（或者子目录）
├─index.php       	入口文件
├─README.md     README文件
├─DB.php			数据库连接配置文件
├─Application     	应用目录
│  ├─Common     	应用公共函数目录
│  │  ├─Conf       	应用配置目录
│  │  │  ├─config.php       应用配置文件
│  ├─Home		应用子应用——Home目录
│  │  ├─Common       Home应用公共函数目录
│  │  │  ├─Tools      Home应用自定义第三方插件目录
│  │  │  │  ├─pCache.class.php		图表缓存类文件
│  │  │  │  ├─pChart.class.php		图表生成类文件
│  │  │  │  ├─pData.class.php       	图标数据处理类文件
│  │  ├─Conf         	Home应用配置目录
│  │  │  ├─config.php       Home应用配置文件
│  │  ├─Controller		Home应用控制器目录
│  │  │  ├─AnalyseController.class.php		数据分析模块类文件
│  │  │  ├─ClassController.class.php		线路班次跟踪模块类文件
│  │  │  ├─DataCollectController.class.php	数据爬取模块类文件
│  │  │  ├─IndexController.class.php		定时任务模块类文件
│  │  │  ├─MainController.class.php		主控制模块类文件
│  │  │  ├─index.html       安全入口文件
│  │  ├─Model		Home应用数据模型目录
│  │  ├─View		Home应用视图目录
│  │  │  ├─Index			Home应用Index模块视图目录
│  │  │  │  ├─index.html			定时任务启动&结果反馈页面
│  │  ├─Runtime		Home应用运行缓存目录
├─Public			资源文件目录
├─ThinkPHP 		框架系统目录
│  ├─Common		核心公共函数目录
│  ├─Conf		核心配置目录
│  ├─Lang		核心语言包目录
│  ├─Library      框架类库目录
│  │  ├─Think		核心Think类库包目录
│  │  ├─Behavior  	行为类库目录
│  │  ├─Org       	Org类库包目录
│  │  ├─Vendor    	第三方类库目录
│  │  ├─ ...      	更多类库目录
│  ├─Mode         	框架应用模式目录
│  ├─Tpl          	系统模板目录
│  ├─LICENSE.txt  	框架授权协议文件
│  ├─logo.png     	框架LOGO文件
│  ├─README.txt   	框架README文件
│  ├─ThinkPHP.php   	ThinkPHP核心文件
└─└─index.php    		框架入口文件

主要文件作用简述：
1.gra\ThinkPHP		项目框架
2.gra\DB.php			数据库配置文件，用于配置连接买MySQL数据库的各项配置
3.gra\Application\Common\Conf\config.php	设置关注线路，关注数据等重要信息
4.gra\Application\Home\Controller\AnalyseController.class.php
a)包括线路全程耗时_中位数，不同时段每站所耗时间，时间戳差值计算。
5.gra\Application\Home\Controller\ClassController.class.php
a)包括设置关注线路、设置正在行驶关注班次、移除关注班次、得到关注列表、定时获取实时数据、记录关注班次位置变得时间、把刚起点上的巴士存入关注列表。
6.gra\Application\Home\Controller\DataCollectController.class.php
a)包括curl请求获取数据、第三巴士公司巴士线路爬取、爬取数据解析并存储到数据库。
7.gra\Application\Home\Controller\IndexController.class.php
a)定时访问主控制模块route2log()方法
8.gra\Application\Home\Controller\MainController.class.php
a)包括实例化关注线路Class对象，逐个取出关注线路的行驶班次定时实时数据。
9.gra\Application\Home\View\index.html
a)启动定时访问主控制模块，并接受现实数据爬取时的返回数据
