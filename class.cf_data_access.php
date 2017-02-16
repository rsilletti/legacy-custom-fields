<?php

/**
* Returns custom field names and data by textpattern table column relationship to name set in preferences.
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
* 02110-1301 USA
*
* rsilletti@gmail.com
*
* @package txpcustom_fields
*/

/**
*
* @package txpcustom_fields
* @author Rick Silletti
* @copyright 2010 Rick Silletti
*/

/**
* Class constructor is passed a comma delimited list of field names as text 
* and returns, field names, the textpattern table column name that
* is associated with them, and the where clause for inclusion in the MySql query.
* @param text custom field names.
* @return array ($name -> field name, $field -> textpattern column name, $where where clause for MySql). 
*/

class cfByName {

private $name;
private $debug;
public $where_null;

		function __construct($names, $debug=null)
		{
		
			foreach(do_list($names) as $name)
 			{
			$where = "val='".doSlash($name)."'";
			$rs = safe_row('name', 'txp_prefs', $where);
			
				if($rs) 
				{

				$field = rtrim($rs['name'], '_set');
				$where_null = " `".$field."` != '' ";
				
				} else { trigger_error(gTxt('name_not_found')); }
			
			$this->data[] = array('name' => $name , 'field' => $field, 'where' => $where_null );
			}
			
			if($debug) 
			{

				dmp($this);
				$multi = safe_rows('name', 'txp_prefs', $where);
				(sizeof($multi) > 1) ? trigger_error(gTxt('duplicate_fieldname')) : '';

			}
		return $this;
		}

/**
* function call obj->fieldsData()
* @return array Indexed array of values set in custom fields named
*/

		function fieldsData()
		{
		
		$this->result['all'] = null;
		
			foreach( $this->data as $data ) {
		
			$this->result[$data['name']] = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");
			
			}
			
		return $this->result;
		}

/**
* Returns textpattern table data indexed by custom field named (comma delimited list) with active data.
* function call obj->articlesData()
* @param string $col textpattern table field name/names as comma delimited list, default is '', all.
* @return array Article data as set in column selection per named field.
*/

		function articlesData($col="*")
		{

		$this->result['all'] = null;
				
		foreach( $this->data as $data) {
		$col_list = $col;
		$txp_col = getThings("SHOW FIELDS FROM ".safe_pfx('textpattern')."");
		$txp_col[] = "*";

		if(array_intersect(do_list($col_list), $txp_col) === do_list($col_list)) {
			$this->result[$data['name']] = getRows("SELECT ".$col_list." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");
		} else { trigger_error(gTxt('column_not_found')); }

		}
		return $this->result;
		}


/**
* function call obj->fieldData()
* @global $thisarticle
* @return array of Text Values set in custom fields named per article from inside article form.
*/

		function fieldData() 
		{
		global $thisarticle;
		$this->result['all'] = null;
		assert_article();
		
			foreach($this->data as $data) 
			{
				$this->result[$data['name']] = $thisarticle[strtolower($data['name'])];
			}
		return $this->result;
		}

/**
* Returns textpattern table data indexed by custom field named 
* with active data per article from inside article form. 
* function call obj->articleData()
* @global $thisarticle
* @param string $col textpattern table field name/names as comma delimited list, default is '', all.
* @return array Article data as set in column selection.
*/

		function articleData($col="*") 
		{
		global $thisarticle;
		$this->result['all'] = null;
		assert_article(); 

		$col_list = $col;
		$txp_col = getThings("SHOW FIELDS FROM ".safe_pfx('textpattern')."");
		$txp_col[] = "*";

			foreach($this->data as $data) 
			{
		
			if(array_intersect(do_list($col_list), $txp_col) === do_list($col_list)) {
				$this->result[$data['name']] = getRow("SELECT ".$col_list." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']." and ID = ".$thisarticle['thisid']."");
			} else { trigger_error(gTxt('column_not_found')); }
		
			}
		return $this->result;
		}


/**
* Sum of number values in custom field columns and total of all named fields. 
* function call obj->sumName()
* @param boolean $debug return error text for non numeric entry in field if set to 1, default is false.
* @return double
*/        
		function sumName($debug=null) {
		
		$this->num['all'] = null;
		
			foreach($this->data as $data ) {
			
			$nums = array();			
			$rs = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");

				foreach ($rs as $e) {

					if (is_numeric($e)) {
						$nums[] = $e;
					} else if ($debug) { trigger_error(gTxt('non_numeric_entry')); }			
				}
				$this->num[$data['name']] = array_sum($nums);
				$this->num['all'] = array_sum($nums) + $this->num['all'];
			}
			
			return $this->num;
		}

/**
* Count of number values in custom field column. 
* function call obj->countName()
* @param boolean $debug return error text for non numeric entry in field if set to 1, default is false.
* @return integer
*/         
		function countName($debug=null) {
		
		$this->num['all'] = null;
		
			foreach($this->data as $data ) {
			
			$nums = array();
			$rs = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");

				foreach ($rs as $e) {
				
					if (is_numeric($e)) {
						$nums[] = $e;
					} else if ($debug) { trigger_error(gTxt('non_numeric_entry')); }			
				}
				$this->num[$data['name']] = count($nums);
				$this->num['all'] = count($nums) + $this->num['all'];			
			}
			
			return $this->num;
		}

/**
* Minimum value in custom field column. 
* function call obj->minName()
* @param boolean $debug return error text for non numeric entry in field if set to 1, default is false.
* @return double
*/ 
		function minName($debug=null) {
		
		$this->num['all'] = null;

		$index = array();
		
			foreach($this->data as $data ) {
			
			$nums = array();
			$rs = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");

				foreach ($rs as $e) {
					if (is_numeric($e)) {
						$nums[] = $e;
						$index[] = $e;
					} else if ($debug) { trigger_error(gTxt('non_numeric_entry')); }			
				}
				sort($nums);
				$this->num[$data['name']] = $nums[0];
			}
			sort($index);
			$this->num['all'] = $index[0];
			return $this->num;
		}

/**
* Maximum value in custom field column. 
* function call obj->maxName()
* @param boolean $debug return error text for non numeric entry in field if set to 1, default is false.
* @return double
*/         
		function maxName($debug=null) {

		$index = array();
				
		$this->num['all'] = null;
		
			foreach($this->data as $data ) {
			
			$nums = array();
			$rs = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");

				foreach ($rs as $e) {
					if (is_numeric($e)) {
						$nums[] = $e;
						$index[] = $e;
					} else if ($debug) { trigger_error(gTxt('non_numeric_entry')); }			
				}
				rsort($nums);
				$this->num[$data['name']] = $nums[0];
			}
			rsort($index);
			$this->num['all'] = $index[0]; 
			return $this->num;
		}

/**
* Average value in custom field column. 
* function call obj->avgName()
* @param boolean $debug return error text for non numeric entry in field if set to 1, default is false.
* @return double
*/ 
		function avgName($debug=null) {
		
		$this->num['all'] = null;
		$index = 0;
			foreach($this->data as $data ) {
			
			$nums = array();
			$rs = getThings("SELECT ".$data['field']." FROM ".safe_pfx('textpattern')." WHERE ".$data['where']."");

				foreach ($rs as $e) {
					if (is_numeric($e)) {
						$index++;
						$nums[] = $e;
						$this->num['all'] = $e + $this->num['all'];
					} else if ($debug) { trigger_error(gTxt('non_numeric_entry')); }			
				}
				$this->num[$data['name']] = array_sum($nums) / count($nums);
			}
			$this->num['all'] = $this->num['all'] / $index;
			return $this->num;
		}
}

/**
* Provides for static function calls drawn from a list of the custom field names currently in use, helper class for cfByName.
* @param none. 
*/

abstract class cf_names {
       
/**
* Custom field names current in system as an array.
* Static call cf_names::names_array()
* @return array   0 on failure
*/       
		public static function names_array() {

		$result = array();		
			$where = "name rlike '_set' and name rlike 'custom_' and  val != '' ";
				$rs = safe_rows('val', 'txp_prefs', $where);
				
				foreach($rs as $list) {
					$result[] = $list['val'];
				}
						
		return $result;
		}

/**
* Custom field names current in system as a comma delimited list.
* Static call cf_names::names_string()
* @return string   0 on failure
*/
		public static function names_string(){
		
		$result = array();
			$where = "name rlike '_set' and name rlike 'custom_' and  val != '' ";
				$rs = safe_rows('val', 'txp_prefs', $where);
				
				foreach($rs as $list) {
					$result[] = $list['val'];
				}
						
        return implode(',', $result);       
		}

/**
* Custom field columns current in system as a comma delimited list.
* Static call cf_names::columns_string()
* @return string   0 on failure
*/
		public static function columns_string(){
		
		$result = array();
			$where = "name rlike '_set' and name rlike 'custom_' and  val != '' ";
				$rs = safe_rows('name', 'txp_prefs', $where);
				
				foreach($rs as $list) {
					$result[] = rtrim($list['name'], '_set');
				}
						
        return implode(',', $result);        
		}

/**
* Custom field columns current in system as a comma delimited list.
* Static call cf_names::columns_string()
* @return string   0 on failure
*/
		public static function columns_array(){
		
		$result = array();
			$where = "name rlike '_set' and name rlike 'custom_' and  val != '' ";
				$rs = safe_rows('name', 'txp_prefs', $where);
				
				foreach($rs as $list) {
					$result[] = rtrim($list['name'], '_set');
				}
						
        return $result;        
		}

/**
* Custom field name by column name.
* Static call cf_names::column_to_name($c_name)
* @return string   0 on failure
*/
		public static function column_to_name($c_name){
		
		$where = "name='".$c_name."_set'";		
			$rs = safe_row('val', 'txp_prefs', $where);				
        return $rs['val'];        
		}


/**
* Number of currently defined custom field names.
* Static call cf_names::names_many()
* @return integer   0 on failure
*/

		public static function names_many() {
		
		$result = array();
			$where = "name rlike '_set' and name rlike 'custom_' and  val != '' ";
				$rs = safe_rows('val', 'txp_prefs', $where);
				
				foreach($rs as $list) {
					$result[] = $list['val'];
				}
				
        return count($result);       
		}
}

?>