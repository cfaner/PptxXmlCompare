<?php
$pptx_filename = './file/test.pptx';

require_once "vendor/autoload.php";

if(!is_dir('./temp'))
{
	mkdir('./temp',0777);
}

if(!is_dir('./file'))
{
	mkdir('./file',0777);
}

if(!is_dir('./history'))
{
	mkdir('./history',0777);
}

if(!is_dir('./result'))
{
	mkdir('./result',0777);
}

if(!file_exists('./temp/hash.log'))
{
	file_put_contents('./temp/hash.log','');
}
 
if(!file_exists($pptx_filename))
{
	die('File is not exists');
}

$logData = explode("\n",file_get_contents('./temp/hash.log'));

$hash = md5_file( $pptx_filename );

if(!in_array($hash,$logData))
{
	$oZip = new ZipArchive();
	$oZip->open($pptx_filename);
	$oZip->extractTo('./history/'.$hash);
	file_put_contents('./temp/hash.log',$hash."\n",FILE_APPEND);
	//关闭zip文档 
	$oZip->close(); 
}

$new_file = @$logData[ count($logData) - 2 ];
$old_file = @$logData[ count($logData) - 3 ];

if($new_file && $old_file && !file_exists( './result/'. $new_file.'_'.$old_file .'.html' ))
{
	$path = 'ppt/slides/slide1.xml';
	
	$a = file_get_contents('./history/'.$old_file.'/'.$path);
	$b = file_get_contents('./history/'.$new_file.'/'.$path);
	$a = str_replace(">",">\n",$a);
	$b = str_replace(">",">\n",$b);
 
	$a = explode("\n", $a);
	$b = explode("\n", $b);
 
	$options = array(
		//'ignoreWhitespace' => true,
		//'ignoreCase' => true,
	);
 
	$diff = new Diff($a, $b, $options);
	$renderer = new Diff_Renderer_Html_SideBySide;
	$result = $diff->Render($renderer);
	$tpl = file_get_contents('./tpl.html');
	$result = str_replace('{RESULT}',$result,$tpl);
	file_put_contents('./result/'. $new_file.'_'.$old_file .'.html' , $result);
}

echo "success";