<?php
include "menu.php";
$ns=$_GET['ns'];
//include "func.php";



if ( $_GET['ns'] ){

$cm=$_GET['cm'];
$lev=$_GET['lev'];

include "../config/".$ns.".conf.php";
include "sqvar.php";

echo "<h1>".$namesys;
echo "</h1>";
echo $comment;
echo "<br>";
echo "<br>";
echo "<h2>Калибровка уровня</h2>";

echo "Таблица калибровки (RAW уровня, Объем в литрах)<br>";

$tb="level";



$link=mysqli_connect("$dbhost", "$login", "$password", "$my_db");

// Добавляем
if ( $_GET['add'] == 'add' ) {
mysqli_query($link, "CREATE DATABASE $my_db");
mysqli_query($link, "create table $tb (cm double PRIMARY KEY)");
mysqli_query($link, "alter table $tb add column lev double");
mysqli_query($link, "insert into $tb (cm, lev) values ( $cm, $lev )");
}

// Удаляем
if ( $_GET['del'] == 'del' ) {
mysqli_query($link, "delete from $tb where cm=$cm");
}

// Редактируем
if ( $_GET['edit'] == 'edit' ) {
mysqli_query($link, "update $tb set lev=$lev where cm=$cm");
}



$strSQL ="select * from level order by cm";


// Выполняем запрос
$rs=mysqli_query($link, $strSQL);



echo "<table border='1'>";




// Извлекаем значения и формируем таблицу результатов
while($id=mysqli_fetch_row($rs))
        {echo " 
           <form>
              <form action='' method='get'>
                   <input type='text' name='cm' value=".$id[0].">
                   <input type='text' name='lev' value=".$id[1].">
                   <input type='hidden' name='ns' value=$ns>
              <input type='submit' value='edit' name='edit'>
              <input type='submit' value='del' name='del'>
           </form>
          <br>";


        }

// Параметры RAW
echo "<br>";
$id=mysqli_fetch_row(mysqli_query($link, "select $p_Dst, intpl($p_Dst) from sens order by dt desc limit 1"));
$raw=$id[0];
$intpl_raw=$id[1];
echo "Текущее значение RAW: ".$p_dist." = ".$raw;
echo "<br>";
echo "Интерполированное текущее значение объема: ".round($intpl_raw,3)." Литр.";
echo "<br>";
//pedit("Dist_min_k1",$ns,1.7,"Значение k1 для фильтрации выброса Dst");




// Форма добавления
echo "<br>Добавить точку интерполяции<br>
            <form action='' method='get'>
                   <input type='text' name='cm' value=$raw>
                   <input type='text' name='lev' value=''>
                   <input type='hidden' name='ns' value=$ns>
              <input type='submit' value='add' name='add'>
            </form>
      <br>
";



mysqli_close($link);

include "sqfunc.php";


// Рисуем калибровочный график
// составление csv калибровки
// Процедурв фильтрации мини
$link=mysqli_connect("$dbhost", "$login", "$password", "$my_db");
$rs=mysqli_query($link, "select cm, lev from $tb order by cm");

$filename=$csv;
$handler = fopen($filename, "w");

while($id=mysqli_fetch_row($rs))
        { 
        for ($x=0; $x<=count($id)-1; $x++) 
                {
		$text= $id[$x].";";
		fwrite($handler, $text);
                }
	fwrite($handler, "\n");
        }

fclose($handler);
$filename=$gnups;
$handler = fopen($filename, "w");

include "sqvar.php";


// составление csv уровня
$rs=mysqli_query($link, "select dt,$p_Dst,@a:=intpl($p_Dst),levmin(@a) from sens order by dt desc limit 100");

$csv2="tmp/lev.csv";
$filename=$csv2;
$handler = fopen($filename, "w");

while($id=mysqli_fetch_row($rs))
        { 
        for ($x=0; $x<=count($id)-1; $x++) 
                {
		$text= $id[$x].";";
		fwrite($handler, $text);
                }
	fwrite($handler, "\n");
        }

fclose($handler);
$filename=$gnups;
$handler = fopen($filename, "w");


// рисование gnuplot

$text='
set terminal png size 1400,800
set output "'.$gimg.'"
set datafile separator ";"
set grid
set xlabel "RAW"
set ylabel "Объем в литрах"
set multiplot layout 2,2

set label "Текущий уровень" at '.$raw.','.$intpl_raw.' point pointtype 7

plot    \
	"'.$csv.'" using 1:2 w l title "", \
	"'.$csv.'" using 1:2 w p pt 6 title "", \

set xdata time
set format x "%d.%m\n%H:%M"
set timefmt "%Y-%m-%d %H:%M:%S"
set xlabel "Дата/Время"
set ylabel "RAW"

plot    \
	"'.$csv2.'" using 1:2 w l title "", \

set grid ytics mytics
set mytics 2

set ylabel "Объем в литрах"
plot    \
	"'.$csv2.'" using 1:3 w l title "", \
	"'.$csv2.'" using 1:4 w l title "", \

set grid ytics mytics
set mytics 2

set ylabel "Объем в литрах"
plot    \
	"'.$csv2.'" using 1:4 w l title "", \




';

fwrite($handler, $text);
fclose($handler);

$err=shell_exec('cat '.$gnups.'|gnuplot');
echo $err;

echo '<img src="'.$img.'" alt="график">';



mysqli_close($link);



}
else
{
echo "Не выбрана система";
}


?>


