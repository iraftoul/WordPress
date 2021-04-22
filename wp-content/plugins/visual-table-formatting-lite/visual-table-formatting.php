<?php
/* 
Plugin Name: Visual Table Formatting Lite
Plugin URI: http://www.w3examples.com
Description: Create tables visually 
Author: George Iron
Version: 1.1
Author URI: http://www.w3examples.com
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
 
function w3exvtfGetBetween($str,$pos1,$pos2)
{
	$start = min($pos1, $pos2);
	$length = abs($pos1 - $pos2);
	return substr($str, $start, $length);
}
function w3exvtfFindString($sfrom,$sbegin,$send,$barr = false,$ibegin = 0)
{
	if($barr)
	{//find all rows in array
		$retarr = array();
		$iend = 0;
		$irow = -1;
		$srow = "";
		while(TRUE)
		{
			$ibegin = strpos($sfrom,$sbegin,$ibegin);
	 		if( $ibegin === FALSE) return $retarr;
			$ibegin = $ibegin + strlen($sbegin);
			$iend = strpos($sfrom,$send,$ibegin);
			if( $iend === FALSE) return $retarr;
			$srow = w3exvtfGetBetween($sfrom,$ibegin,$iend);
			$irow = (int)$srow;
			if($irow >= 0 && $irow < 3000)
			{
				$ibegin = $iend + 1;
				$iend = strpos($sfrom,"{/}",$ibegin);
				if( $iend === FALSE) return $retarr;
				$retarr[$irow] = w3exvtfGetBetween($sfrom,$ibegin,$iend);
			}
			$ibegin = $iend + 3;
		}
	}else
	{
		$retstr = "";
		$iend = -1;
		$ibegin = strpos($sfrom,$sbegin,$ibegin);
 		if( $ibegin === FALSE) return $retstr;
		$ibegin = $ibegin + strlen($sbegin);
		$iend = strpos($sfrom,$send,$ibegin);
		if( $iend === FALSE) return $retstr;
		$retstr = w3exvtfGetBetween($sfrom,$ibegin,$iend);
		return $retstr;	
	}
}

function w3exvtfGetColors(&$fols,&$arrFontColors)
{
	if($fols === "") return;
	$arrRows = w3exvtfFindString($fols,"{","}",TRUE,NULL);
	if(is_array($arrRows))
	{
		foreach($arrRows as $i => $Row )
		{
			$arrCols = explode(";",$Row);
			$arrRow = array();
			foreach ($arrCols as $Col)
			{
				if(strpos($Col,":") !== FALSE)
				{
					$cells = w3exvtfGetBetween($Col,0,strpos($Col,":"));
					$cellcolor = w3exvtfGetBetween($Col,strpos($Col,":") + 1,strlen($Col));
					$cellcolor = "#".$cellcolor;
					if(strpos($cells,"-") !== FALSE)
					{//multiple
						$start = w3exvtfGetBetween($cells,0,strpos($cells,"-"));
						$end   =  w3exvtfGetBetween($cells,strpos($cells,"-") + 1,strlen($cells)); 
						$start = (int)$start;
						$end =   (int)$end;
						if($start < $end && ($start >=0 && $end <=200 ))
						{
							while($start <= $end)
							{
								$arrRow[$start] = $cellcolor;
								$start++;
							}	
						}
					}else
					{//single
						$singlecol = (int)$cells;
						$arrRow[$singlecol] = $cellcolor;
					}
				}
			}
			$arrFontColors[$i] = $arrRow;
		}	
	}
}

function w3exvtfGetFormat(&$col,&$style,$inmerge=false)
{
	//get new lines
	if(strpos($col,"{;n}") !== FALSE)
	{
		$col = str_replace("{;n}", "<br/>", $col);
	}
	$format = w3exvtfFindString($col,"{f","}");
	if($format === "" || (strpos($col,"}") >= 6)) return;
	$val = hexdec($format);
	if($val < 0 || $val > 1023) return;
	$col = substr($col,strpos($col,"}") + 1);
	if($val & 1)
		$col = "<strong>".$col."</strong>";
	if($val & 2)
		$col = "<em>".$col."</em>";
	if($val & 4)
		$col = "<u>".$col."</u>";
	if($val & 8)
		$col = "<strike>".$col."</strike>";
	
}
function w3examples_vtftable( $atts,$content=NULL ) {
	extract( shortcode_atts( array(
		'cols' => ''
	), $atts ) );
	$arrColors = array();
	$html = "";
	w3exvtfGetColors($cols,$arrColors);
	$html.= "<table>\n";
	$rows =  array_map('trim', explode(";nn;",$content));
	$rowcounter = 0;
	$arrtable = array();
	foreach ($rows as $row)
	{
		$row = str_replace("<br />","",$row);
		$row = str_replace("<br/>","",$row);
		$row = trim($row);
		if($row == "") continue;
		$cols = explode(";;;",$row);
		if(is_array($cols))
			$arrtable[] = $cols;
	}
	unset($rows);
	for($ir = 0; $ir < count($arrtable); $ir++)
	{
		$arrrow = $arrtable[$ir];
		$hascolorrow = false;
		$arrColRow = array();
		$html.="<tr>\n";
		if(array_key_exists($ir,$arrColors))
		{
			$arrColRow = $arrColors[$ir];
			$hascolorrow = true;
		}
		for($ic = 0; $ic < count($arrrow); $ic++)
		{
			$col = $arrrow[$ic];
			$style = "";
			$tdattr = "";
			if($hascolorrow)
			{
				if(array_key_exists($ic,$arrColRow))
				{
					$color = $arrColRow[$ic];
					$style = "background-color:".$color.";";
				}
			}
			w3exvtfGetFormat($col,$style);
			if($col == "") $col = " ";
			if($style !== "")
				$style = " style=\"".$style."\"";
			$html.= "<td".$style.$tdattr.">".$col."</td>";
		}
		$html.="</tr>\n";
	}
	$html.="</table>\n";
	return $html;
}
add_shortcode( 'vtftable', 'w3examples_vtftable' );


function vtf_admin_scripts_init($hook) {
	if( $hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php' ) 
		return;
 	wp_enqueue_style('thickbox');
 	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-accordion');
//let the browser cache do its thing
	$purl = plugin_dir_url(__FILE__);
//playing nice with others, loading scoped jquery ui styles
	wp_enqueue_style('w3vtf-juscoped',$purl.'css/jquery-ui-scoped.css',false, '1.0', 'all' );
	wp_enqueue_style('w3vtf-handt',$purl.'css/jquery.handsontable.full.css',false, '1.0', 'all' );
	wp_enqueue_style('w3vtf-spect',$purl.'css/spectrum.css',false, '1.0', 'all' );
	wp_enqueue_script('w3vtf-jqhandt',$purl.'js/jquery.handsontable.full.js',array(), '1.0', true );
	wp_enqueue_script('w3vtf-jqspect',$purl.'js/spectrum.js', array(), '1.0', true );
}
add_action('admin_enqueue_scripts', 'vtf_admin_scripts_init'); 

  

function w3exvtables_buttons() {
	add_filter("mce_external_plugins", "w3exvtables_add_buttons");
	add_filter('mce_buttons', 'w3exvtables_register_buttons');
}	
function w3exvtables_add_buttons($plugin_array) {
	$plugin_array['w3vtables'] = plugin_dir_url( __FILE__ ).'vtf-plugin.js';
	return $plugin_array;
}
function w3exvtables_register_buttons($buttons) {
	array_push( $buttons, 'vtables');
	return $buttons;
}
add_action( 'init', 'w3exvtables_buttons' );

function vft_show_tables_func() {
	
	$url = plugin_dir_url( __FILE__ );
//load here to ensure proper override of scoped jqueryui css
	echo '<link href="'.$url.'css/tableslite.css" rel="stylesheet"></link>';
  	require 'tables.php';
//must be loaded here to handle ajax created elements
	echo '<script src="'.$url.'js/mainlite.js"></script>';
	die();
}
add_action('wp_ajax_vft_show_tables', 'vft_show_tables_func');
?>