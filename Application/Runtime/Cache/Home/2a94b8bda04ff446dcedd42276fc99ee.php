<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<textarea type="text" id="clock" ></textarea>
<button onclick="int=window.clearInterval(int)">停止</button>
</body>
<script type="text/javascript" src="/gra/Public/assets/js/jquery-1.9.1.js"></script>
<script type="text/javascript">
    var int=self.setInterval("clock()",15000);
    function clock()
    {
        var d=new Date();
        var t=d.toLocaleTimeString();
        $.get('/gra/index.php/Home/Main/route2log','',function(json){
            document.getElementById("clock").value=t+':'+json.attend_class;
        });
    }
</script>
</html>