<?php
/**
 *+--------------------------------------------------
 *          图表管理类
 * xuanfeng lixuanfeng1990@163.com
 * 2015-01-31 21:04
 *+--------------------------------------------------
 * 
 */

class Chart{
 /**
  * @@生成三维饼图
  * @param string $title
  * @param array $data
  * @param int $size
  * @param int $height
  * @param int $width
  * @param array $legend
  */
  static function create3dpie($title,$data=array(),$size=40,$height=100,$width=80,$legend=array(),$slice=0){
      //包含相关的文件
		//echo dirname(__FILE__);exit;
  	  require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
      require (dirname(__FILE__)."/Jpgraph/jpgraph_pie.php");
      require (dirname(__FILE__)."/Jpgraph/jpgraph_pie3d.php");
      // 创建图表
	  $graph = new PieGraph($width,$height,"auto");
	  $graph->SetShadow();
	  // 设置标题
	  $graph->title->Set(iconv("utf-8","gb2312",$title));
	  //$graph->title->SetFont(FF_VERDANA,FS_BOLD,18); 
	  $graph->title->SetFont(FF_SIMSUN,FS_BOLD,18); 
	  $graph->title->SetColor("darkblue");
	  $graph->legend->Pos(0.1,0.2);
	  $graph->SetFrame(false,'#ffffff',0);//去掉周围的边框
	  // Create 3D pie plot
	  $p1 = new PiePlot3d($data);
	  $p1->SetTheme("sand");
	  $p1->SetCenter(0.4);
	  $p1->SetSize($size);
	  // Adjust projection angle
	  $p1->SetAngle(70);
	  // As a shortcut you can easily explode one numbered slice with
	  if($slice==0)
	     $p1->ExplodeSlice(3);
	  // Setup the slice values
	  $p1->value->SetFont(FF_ARIAL,FS_BOLD,10);
	  //$p1->value->SetFont(FF_SIMSUN,FS_BOLD,11);
	  $p1->value->SetColor("navy"); 
	  $graph->legend->SetFont(FF_SIMSUN,FS_BOLD,8);
      //编码转化
	  foreach ($legend as $k => $v) {
          $legend[$k] = iconv('utf-8', 'gb2312', $v);
      }
	  $p1->SetLegends($legend);
	  $graph->Add($p1);
	  $graph->Stroke();
  }	
 
  /**
   * 设置柱状图
   */
  static function createcolumnar($title,$data=array(),$size=40,$height=100,$width=80,$legend=array()){
  	    //载入文件
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_bar.php");
		// Some data
		// Create the graph and setup the basic parameters 
		$graph = new Graph($width,$height,'auto');	
		$graph->img->SetMargin(40,30,40,40);
		$graph->SetScale("textint");
		$graph->SetShadow();
		$graph->SetFrame(false); // No border around the graph
		
		// Add some grace to the top so that the scale doesn't
		// end exactly at the max value. 
		$graph->yaxis->scale->SetGrace(20);
		
		// Setup X-axis labels
        //编码转化
	    foreach ($legend as $k => $v) {
            $legend[$k] = iconv('utf-8', 'gb2312', $v);
        }
		$graph->xaxis->SetTickLabels($legend);
		$graph->xaxis->SetFont(FF_SIMSUN);
		
		// Setup graph title ands fonts
		$graph->title->Set(iconv("utf-8","gb2312",$title));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,11);
		//$graph->xaxis->title->Set("Year 2002");
		$graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);
		                              
		// Create a bar pot
		$bplot = new BarPlot($data);
		$bplot->SetFillColor("#0080C0");
		$bplot->SetWidth(0.3);
		//$bplot->SetShadow();
		
		// Setup the values that are displayed on top of each bar
		$bplot->value->Show();
		// Must use TTF fonts if we want text at an arbitrary angle
		$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
		$bplot->value->SetAngle(0);
		// Black color for positive values and darkred for negative values
		$bplot->value->SetColor("black","darkred");
		$graph->Add($bplot);
		
		// Finally stroke the graph
		$graph->Stroke();
  }

	/**
	 * 设置 一组多条柱状图
	 */
	static function creategroupcolumnar($title, $data = array(), $size = 40, $height = 100, $width = 80, $legend = array())
	{
		//载入文件
		require(dirname(__FILE__) . "/Jpgraph/jpgraph.php");
		require(dirname(__FILE__) . "/Jpgraph/jpgraph_bar.php");
		// Some data
		// Create the graph and setup the basic parameters
		$graph = new Graph($width, $height, 'auto');
		$graph->img->SetMargin(40, 30, 40, 40);
		$graph->SetScale("textint");
		$graph->SetShadow();
		$graph->SetFrame(false); // No border around the graph

		// Add some grace to the top so that the scale doesn't
		// end exactly at the max value.
		$graph->yaxis->scale->SetGrace(20);

		// Setup X-axis labels
		//编码转化
		foreach ($legend as $k => $v) {
			$legend[$k] = iconv('utf-8', 'gb2312', $v);
		}
		$graph->xaxis->SetTickLabels($legend);
		$graph->xaxis->SetFont(FF_SIMSUN);

		// Setup graph title ands fonts
		$graph->title->Set(iconv("utf-8", "gb2312", $title));
		$graph->title->SetFont(FF_SIMSUN, FS_BOLD, 11);
		//$graph->xaxis->title->Set("Year 2002");
		//$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);

		// Create bar pot
		$bplot1 = new BarPlot($data['min']);
		$bplot2 = new BarPlot($data['mid']);
		$bplot3 = new BarPlot($data['max']);
		// Setup the colors with 40% transparency (alpha channel)
		$bplot1->SetFillColor('orange@0.4');
		$bplot2->SetFillColor('brown@0.4');
		$bplot3->SetFillColor('darkgreen@0.4');

// Setup legends
		$bplot1->SetLegend('Min');
		$bplot2->SetLegend('Mid');
		$bplot3->SetLegend('Max');

// Setup each bar with a shadow of 50% transparency
		$bplot1->SetShadow('black@0.4');
		$bplot2->SetShadow('black@0.4');
		$bplot3->SetShadow('black@0.4');

		$gbarplot = new GroupBarPlot(array($bplot1, $bplot2, $bplot3));
		$gbarplot->SetWidth(0.6);
		$graph->Add($gbarplot);
		$x = array('07','08','09','10','11','12','13','14','15','16','17','18','19','20','21');
		$graph->xaxis->SetTickLabels($x);
		$graph->xaxis->SetFont(FF_SIMSUN,FS_BOLD);
		// Finally stroke the graph
		$graph->Stroke();
	}

	function creat_ceng_columnar($title, $data = array(),$color=array(),$height = 100, $width = 80)
	{
		//载入文件
		require(dirname(__FILE__) . "/Jpgraph/jpgraph.php");
		require(dirname(__FILE__) . "/Jpgraph/jpgraph_bar.php");
		// Some data
		// Create the graph and setup the basic parameters
		$plots=array();
		$i=0;
		foreach($data as $k=>$v){
			// Create the bar plots
			$plot = $plots[] = new BarPlot($v);
			$plot->SetFillColor($color[$i]);
			$plot->value->Show();
			$i++;
		}

// Create the graph. These two calls are always required
		$graph = new Graph($width, $height, 'auto');
		$graph->SetScale("textlin");

		$graph->SetShadow();
		$graph->img->SetMargin(40,30,20,40);

// Create the grouped bar plot
		$gbplot = new AccBarPlot($plots);

// ...and add it to the graPH
		$graph->Add($gbplot);

		$graph->title->Set($title);
		$graph->xaxis->title->Set("Class");
		$graph->yaxis->title->Set("Time");

		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

		$x = array('07-B25','08-B25','09-B25','10-B25','11-B25','12-B25','13-B25','14-B25','15-B25','16-B25','17-B25','18-B25','19-B25','20-B25','21-B25');
		$graph->xaxis->SetTickLabels($x);
		$graph->xaxis->SetFont(FF_SIMSUN,FS_BOLD);
		// Finally stroke the graph
		$graph->Stroke();
	}

 /**
  * 环形图
  */
  function createring($title,$data=array(),$size=40,$height=100,$width=80,$legend=array()){
    // Example of pie with center circle
	require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
	require (dirname(__FILE__)."/Jpgraph/jpgraph_pie.php");
    // Some data
    //$data = array(50,28,25,27,30,30);

	// A new pie graph
	$graph = new PieGraph(700,350,'auto');
	//$graph->SetShadow();
	// Setup title
	$graph->title->Set(iconv("utf-8","gb2312","{$title}"));
	$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
	$graph->title->SetMargin(2); // Add a little bit more margin from the top
	$graph->legend->Pos(0.1,0.1);
	$graph->SetFrame(false,'#ffffff',0);//去掉周围的边框
	// Create the pie plot
	$p1 = new PiePlotC($data);
	
	// Set size of pie
	$p1->SetTheme("sand");
	$p1->SetCenter(0.4);
	$p1->SetSize(0.35);
	
	// Label font and color setup
	$p1->value->SetFont(FF_ARIAL,FS_BOLD,10);
	$p1->value->SetColor('black');
	// Setup the title on the center circle
	$p1->midtitle->Set("");
	$p1->midtitle->SetFont(FF_SIMSUN,FS_NORMAL,10);
	// Set color for mid circle
	$p1->SetMidColor('white');
	// Use percentage values in the legends values (This is also the default)
	$p1->SetLabelType(PIE_VALUE_PER);
	$graph->legend->SetFont(FF_SIMSUN,FS_NORMAL,8);
    //编码转化
    foreach ($legend as $k => $v) {
       $legend[$k] = iconv('utf-8', 'gb2312', $v);
    }
		  $p1->SetLegends($legend);
	// Add plot to pie graph
	$graph->Add($p1);
	
	// .. and send the image on it's marry way to the browser
	$graph->Stroke();
  }
  
  /**
   * 线图
   */
  function createmonthline($title,$data=array(),$size=40,$height=100,$width=80,$legend=array()){
	require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
	require (dirname(__FILE__)."/Jpgraph/jpgraph_line.php");

	$labels = $legend;
	//编码转化
	foreach ($labels as $k => $v) {
	   $labels[$k] = iconv('utf-8', 'gb2312', $v);
	}
	$data = $data;
	$graph = new Graph($width,$height,"auto");
	$graph->img->SetMargin(40,40,40,40);    
	$graph->img->SetAntiAliasing();
	$graph->SetScale("textlin");
	$graph->SetShadow();
	$graph->title->Set(iconv('utf-8', 'gb2312',"{$title}"));
	$graph->title->SetFont(FF_SIMSUN,FS_NORMAL,14);
	$graph->SetFrame(false,'#ffffff',0);//去掉周围的边框
	$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,9);
	$graph->xaxis->SetTickLabels($labels);
	$graph->xaxis->SetLabelAngle(0);
	
	$p1 = new LinePlot($data);
	$p1->mark->SetType(MARK_FILLEDCIRCLE);
	$p1->mark->SetFillColor("#0080C0");
	$p1->mark->SetWidth(4);
	$p1->SetColor("#000000");
	$p1->SetCenter();
	$graph->Add($p1);
	
	$graph->Stroke(); 
  }
  /**
   * 横柱图
   * 
   */
  function createhorizoncolumnar($title,$subtitle,$data=array(),$size=40,$height=100,$width=80,$legend=array()){
	require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
	require (dirname(__FILE__)."/Jpgraph/jpgraph_bar.php");
	$datay = $data;
	$datax = $legend;
    //编码转化
	foreach ($datax as $k => $v) {
	   $datax[$k] = iconv('utf-8', 'gb2312', $v);
	}
	// Size of graph
	$count = count($datay);
	$addheight = 0;
	if($count>10){
		$addheight = ($count-10)*20;
	}
	$height=$height+$addheight;
	
	// Set the basic parameters of the graph 
	$graph = new Graph($width,$height,'auto');
	$graph->SetScale("textlin");
	
	// No frame around the image
	$graph->SetFrame(false);
	$graph->SetFrame(false,'#ffffff',0);//去掉周围的边框
	
	// Rotate graph 90 degrees and set margin
	$graph->Set90AndMargin(70,10,50,30);
	
	// Set white margin color
	$graph->SetMarginColor('white');
	
	// Use a box around the plot area
	$graph->SetBox();
	
	// Use a gradient to fill the plot area
	$graph->SetBackgroundGradient('white','white',GRAD_HOR,BGRAD_PLOT);
	
	// Setup title
	$graph->title->Set(iconv('utf-8', 'gb2312',"{$title}"));
	$graph->title->SetFont(FF_SIMSUN,FS_BOLD,12);
	$graph->subtitle->Set("(".iconv('utf-8', 'gb2312',$subtitle).")");
	$graph->subtitle->SetFont(FF_SIMSUN,FS_NORMAL,10);
	// Setup X-axis
	$graph->xaxis->SetTickLabels($datax);
	$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,10);
	
	// Some extra margin looks nicer
	$graph->xaxis->SetLabelMargin(10);
	
	// Label align for X-axis
	$graph->xaxis->SetLabelAlign('right','center');
	
	// Add some grace to y-axis so the bars doesn't go
	// all the way to the end of the plot area
	$graph->yaxis->scale->SetGrace(10);
	
	// We don't want to display Y-axis
	$graph->yaxis->Hide();
	
	// Now create a bar pot
	$bplot = new BarPlot($datay);
	//$bplot->SetShadow();
	
	//You can change the width of the bars if you like
	//$bplot->SetWidth(0.5);
	
	// Set gradient fill for bars
	$bplot->SetFillGradient('blue','#0080C0',GRAD_HOR);
	
	// We want to display the value of each bar at the top
	$bplot->value->Show();
	$bplot->value->SetFont(FF_ARIAL,FS_NORMAL,7);
	$bplot->value->SetAlign('left','center');
	$bplot->value->SetColor("black");
	$bplot->value->SetFormat('%.0f');
	//$bplot->SetValuePos('max');
	
	// Add the bar to the graph
	$graph->Add($bplot);
	
	// Add some explanation text
	$txt = new Text('');
	$txt->SetPos(130,399,'center','bottom');
	$txt->SetFont(FF_COMIC,FS_NORMAL,8);
	$graph->Add($txt);
	
	// .. and stroke the graph
	$graph->Stroke();
  }
  /*--------这里只是一部分，其他的都在jpgraph中，各位可以根据自己的需求多仿制几个function写图标哦---------*/
}