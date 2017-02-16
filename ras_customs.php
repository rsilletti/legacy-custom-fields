<?php

//********************************//
// Admin side //
//********************************//

	if (@txpinterface == 'admin') {
		add_privs('fields_info', '1,2,6');
                //-- Event is "poll_prefs"
		register_tab("extensions", "fields_info", "Fields Info");
		register_callback("ras_fields_prefs", "fields_info");
	}

function ras_fields_prefs () {
				pagetop("Fields Info", "Fields Settings");				
		
	switch (gps('step')) {
		case 'fields_display': cf_names_list(); 
   		break;
   		case 'titles_display': cf_titles_list();  
   		break;
   		case 'contents_display': cf_contents_list();  
   		break;
		case '' : stepnav();
	}

}


function cf_names_list()
	{
		stepnav();							  
	    echo n.'<div style="margin: 1.3em auto auto auto; text-align: left;">';
		
			$i = new cfByName(cf_names::names_string());
			$i = $i->countName();
			echo '<h4>&nbsp;  Number of fields currently in system:  &nbsp;'.cf_names::names_many().'</h4>';
			echo '<ul>';
			foreach(cf_names::names_array() as $cf_name)
				{
					echo '<li><strong style="color:brown;"> '.$cf_name.'</strong>&nbsp;'.eLink('fields_info','fields_display','field',$cf_name,' _System Fill').'<br>Number of active fields: &nbsp;'.$i[$cf_name].'</li><hr>';
				}
				echo '</ul>';				
	    echo '</div>';
	    echo '</div>';
fieldform();
	}

function cf_titles_list()
	{
		stepnav();							
	    echo n.'<div style="margin: 1.3em auto auto auto; text-align: left;">';				
			$k = new cfByName(cf_names::names_string());
			$cdl = 'Title,'.cf_names::columns_string();
			$i = $k->articlesData($cdl);
			
			foreach(cf_names::names_array() as $cf_name)
			{
			if($i[$cf_name]) {
				echo '<hr><p>Custom Field:<strong style="color:brown;"> '.$cf_name.'</strong></p>';
				echo '<p>Associated Titles:</p>';
				echo '<ul>';

					foreach ($i[$cf_name] as $value) {
					$v = htmlspecialchars($value['Title']);
					echo '<li><b> '.$v.'</b></li>';
					echo '<p>Active fields per article:</p>';
					echo '<ul>';
						foreach(do_list(cf_names::columns_string()) as $now) {
							if($value[$now]) {
								echo '<li> Field value: '.htmlspecialchars($value[$now]).'<br>&nbsp; -  Field Reference :<b>('.cf_names::column_to_name($now).')</b></li>';
							}
						}
					echo '</ul>';	
					}
				echo '</ul><hr>';
			}
			}
	    echo '</div>';
	    							
	    echo '</div>';
	}
	
function cf_contents_list()
	{
		stepnav();							
	    echo n.'<div style="margin: 1.3em auto auto auto; text-align: left;">';				
			$i = new cfByName(cf_names::names_string());
			$i = $i->fieldsData();
			
			foreach(cf_names::names_array() as $cf_name)
			{
			if($i[$cf_name]) {
				echo '<hr><p>Custom Field:<strong style="color:brown;"> '.$cf_name.'</strong></p>';
				echo '<p>Associated Content:</p>';
				echo '<ul>';
					foreach ($i[$cf_name] as $key => $value) {
					$$key = htmlspecialchars($value);	
					echo '<li> '.$$key.'</li>';	
					}
				echo '</ul><hr>';
				}
			}
	    echo '</div>';
	    							
	    echo '</div>';
	}

function fieldform () {
			echo form(
			startTable('edit').
			tr(
				fLabelCell(cf_gTxt('field_entry')).
				fInputCell('name').
				td().
				td(
					fInput('submit', '', cf_gTxt('set_value'), 'write')
				)
			).
			endTable().

			eInput('fields_info').
			sInput('fields_display')
		); //dmp($_POST); dmp($_GET);
}

function stepnav(){
		(gps('step') == 'fields_display') ? $navbit_f = '&rArr;' : $navbit_f = '';
		(gps('step') == 'titles_display') ? $navbit_t = '&rArr;' : $navbit_t = '';
		(gps('step') == 'contents_display') ? $navbit_c = '&rArr;' : $navbit_c = '';
	    echo n.'<div style="margin: .3em auto auto auto; text-align: center;">';
		echo n.'<h3>Custom Fields. </h3><p>'.$navbit_f.'&nbsp;<a href="?event=fields_info&step=fields_display" >'.cf_gTxt('display_current_fields').'</a>. |'.$navbit_t.'&nbsp; 
		<a href="?event=fields_info&step=titles_display" >'.cf_gTxt('display_title_fields').'</a> |'.$navbit_c.'&nbsp; 
		<a href="?event=fields_info&step=contents_display" >'.cf_gTxt('display_content_fields').'</a>.</p><br />';
}

function cf_gTxt($what, $atts = array()) {

	$lang = array(
		'display_current_fields'   => 'Display Fields',
		'display_title_fields'   => 'Display Titles',
		'display_content_fields'   => 'Display Contents',
		'field_entry'               => 'Field Value:&nbsp;&rArr;&nbsp; ',
		'set_value'               => 'Set Field to Value System Wide',
		'cf_prompt'               => 'Sums',
		'cf_write'               => 'Write Custom Field Data to System',
	);

	return strtr($lang[$what], $atts);
}
function ras_mr_miles($atts){

		extract(lAtts(array(
			'cf' => 'miles',
			'label'    => '',
			'labeltag' => '',
            'wraptag' => 'p',
			'class'    => __FUNCTION__
		), $atts));
		
		$i = new cfByName(cf_names::names_string(),"1");
		dmp(cf_names::names_string());
		dmp(cf_names::names_array());
		dmp(cf_names::columns_string());
		dmp(cf_names::columns_array());
		dmp(cf_names::column_to_name('custom_1'));
		dmp(cf_names::names_many());
		$out = $i->sumName("1")[$cf];
		return doLabel($label, $labeltag).doTag($out, $wraptag, $class);
	}
?>