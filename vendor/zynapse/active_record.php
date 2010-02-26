<?php
/*

   Zynapse ActiveRecord - lightweight active record implementation


   http://www.zynapse.org/
   Copyright (c) 2009 Jim Myhrberg.

   ----------
   Permission is hereby granted, free of charge, to any person obtaining
   a copy of this software and associated documentation files (the
   "Software"), to deal in the Software without restriction, including
   without limitation the rights to use, copy, modify, merge, publish,
   distribute, sublicense, and/or sell copies of the Software, and to
   permit persons to whom the Software is furnished to do so, subject to
   the following conditions:

   The above copyright notice and this permission notice shall be
   included in all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
   NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
   LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
   OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
   WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
   ----------

*/


class ActiveRecord {
	
	
	public static
		$settings = array(),
		$db = array(),
		$object_settings = array(),
		$table_info = array(),
		$query_log = array();
		
	private
		$_db = null,
		$_connection_id,
		$_is_child_object = false,
		$_errors = array(),
		$_new_record = true,
		$_init_value_hashes = array(),
		$_track_modifications = true,
		$_unset_in_child = array(),
		
		// default settings
		$_settings = array(
			#'type' => 'mysql', // not used in current version
			'host' => null,
			'database' => null,
			'username' => null,
			'password' => null,
			'persistent' => null,
			'table_prefix' => null,
		),
		$_settings_id,
		
		$_columns = null,
		
		$_sub_class_config_values = array(
			'table_name',
			'primary_key',
			'counter_cache', //TODO impliment counter_cache
		),
		$_table_name,
		$_primary_key,
		$_counter_cache,
		
		$_class_name,
		
		$_created_at_column = 'created_at',
		$_modified_at_column = 'modified_at',
		
		$_any_find_returns_array = false,
		$_find_returns_array = false,
		$_find_all_returns_array = true,
		
		// table associations
		$_associations = array(),
		$_association_types = array(
			'has_one',
			'has_many',
			'belongs_to',
			'has_and_belongs_to_many',
		),
		
		$_auto_save_associations = true,
		$_save_associations = array();
	
	
/*

   Core Init functions
    - the heart

*/

	function __construct ($attributes = null, $options = array()) {
		if ( empty($options['child_object']) ) {
			
			// parse settings
			$this->parse_settings( (isset($options['settings'])) ? $options['settings'] : null );
			
			// start or reuse connection
			$this->establish_connection();
			
			// set table name
			$this->_class_name = get_class($this);
			if ( $this->_table_name === null ) {
				$this->_table_name = $this->_settings['table_prefix'].Inflector::tableize($this->_class_name);
			}
			
			// set primary key
			if ( $this->_primary_key === null ) {
				$this->_primary_key = 'id';
			}
			
			$this->_settings_id = md5(implode('', $this->_settings).$this->_table_name);
			self::$object_settings[$this->_settings_id] = &$this->_settings;
			
			// table info
			self::$table_info[$this->_connection_id][$this->_table_name] = &$this->_columns;	
			
		} else {
			if ( isset($options['new_record']) ) $this->_new_record = $options['new_record'];
		}
		$this->update_attributes($attributes);
		$this->set_associations();
	}
	
	function __get ($key) {
		if ( $key == '_columns' && !is_array($this->_columns) ) {
			$this->set_column_info();
			return $this->_columns;
		} elseif ( !property_exists($this, $key) && array_key_exists($key, $this->_associations) ) {
			return $this->$key = $this->get_association($key);
		} else {
			return $this->$key;
		}
	}
	
	function __set ($key, $value) {
		if ( $key == 'error' ) {
			$this->add_error($value);
		} elseif ( strlen($key) > 6 && substr($key, 0, 6) == 'error_' ) {
			$this->add_error($value, substr($key, 6));
		} else {
			$this->$key = $value;
		}
	}
	
	
	function parse_settings ($input_settings) {
		if ( array_key_exists(ZNAP_ENV, self::$settings) ) {

			if ( !is_array($input_settings) ) {
				if (
					array_key_exists('use', self::$settings[ZNAP_ENV])
					&& array_key_exists(self::$settings[ZNAP_ENV]['use'], self::$settings)
				) {
					$settings = self::$settings[self::$settings[ZNAP_ENV]['use']];
				} else {
					$settings = self::$settings[ZNAP_ENV];
				}
				foreach( $this->_settings as $key => $value ) {
					if ( property_exists($this, $key) ) {
						$this->_settings[$key] = &$this->$key;
						unset($this->$key);
						$this->_unset_in_child[] = $key;
					} elseif ( isset($settings[$key]) ) {
						$this->_settings[$key] = $settings[$key];
					}
				}
			} else {
				foreach( $this->_settings as $key => $value ) {
					if ( isset($input_settings[$key]) ) {
						$this->_settings[$key] = $input_settings[$key];
					} elseif ( property_exists($this, $key) ) {
						$this->_settings[$key] = &$this->$key;
						unset($this->$key);
						$this->_unset_in_child[] = $key;
					}
				}
			}

			foreach( $this->_sub_class_config_values as $key => $value ) {
				if ( property_exists($this, $value) ) {
					$this->{'_'.$value} = &$this->$value;
					unset($this->$value);
					$this->_unset_in_child[] = $key;
				}
			}
		}
	}
	
	function establish_connection () {
		$settings = &$this->_settings;
		if ( isset($settings['host']) && isset($settings['database']) ) {

			$this->_connection_id = $settings['username'].':'.$settings['password'].'@'.$settings['host'];
			if ( $settings['persistent'] ) {
				$this->_connection_id .= '?persist';
			}

			if ( !array_key_exists($this->_connection_id, self::$db) ) {
				
				$connect_function = ( !empty($settings['persistent']) ) ? 'mysql_pconnect' : 'mysql_connect' ;
				if ( !($this->_db = @$connect_function($settings['host'], $settings['username'], $settings['password'])) ) {
				   $this->raise('Could not connect to database server.', mysql_error(), 500);
				}
				if ( !@mysql_select_db($settings['database']) ) {
					$this->raise('Could not select database.', 'There was an error selecting database "'.$settings['database'].'".', 500);
				}

				self::$db[$this->_connection_id] = &$this->_db;

			} else {
				$this->_db = &self::$db[$this->_connection_id];
			}
		} else {
			$this->raise('Invalid ActiveRecord configuration', null, 500);
		}
	}
	
	
	
	
/*

   Main Public Functions
    - main functions for accessing and manipulating data

*/
	
	
	/*
	   Find / Fetch Functions
	*/

	function find ($conditions = null, $options = array(), $return_as_array = null) {
		$options['limit'] = 1;
		$sql = $this->build_find_query($conditions, $options);
		$result = $this->query($sql);
		if ( $row = mysql_fetch_assoc($result) ) {
			if ( $return_as_array === true || ($this->_any_find_returns_array || $this->_find_returns_array) ) {
				return $row;
			} else {
				return $this->create_child_object($row);
			}
		}
		return false;
	}
	
	function find_all ($conditions = null, $options = array(), $return_as_array = null) {
		$sql = $this->build_find_query($conditions, $options);

		$result = $this->query($sql);
		if ( mysql_num_rows($result) > 0 ) {
			$return = array();
			while ($row = mysql_fetch_assoc($result)) {
				if ( $return_as_array === true || ($this->_any_find_returns_array || $this->_find_all_returns_array) ) {
					$return[] = $row;
				} else {
					$return[] = $this->create_child_object($row);
				}
			}
			if ( !empty($return) ) {
				return $return;
			}
		}

		return false;
	}
	

	/*
	   Save / Insert Functions
	*/

	function save ($attributes = array(), $dont_validate = false) {
		$this->update_attributes($attributes);
		
		if ( $dont_validate || $this->valid() ) {
			return $this->add_or_update_record();
		}
		return false;
	}
	
	function save_without_validation ($attributes = null) {
		return $this->save($attributes, true);
	}
	
	function insert ($input = null) {
		$sql = $this->build_insert_query($input);

		if ( $sql !== false ) {
			return $this->query($sql);
		}
		return false;
	}
	
	
	/*
	   Delete / Remove Functions
	*/
	
	function delete ($conditions = null, $options = array()) {
		$options['limit'] = 1;
		return $this->delete_all($conditions, $options);
	}
	
	function delete_all ($conditions = null, $options = array()) {
		if ( $conditions == null || $conditions == '' ) { 
			return null;
		}
		$sql = $this->build_delete_query($conditions, $options);
		return $this->query($sql);
	}
	
	
	/*
	  Count - count table rows
	*/
	function count ($conditions = null, $options = '') {
		$query = 'SELECT ';
		if ( is_array($options) ) {
			if ( !empty($options['select']) ) $query .= $options['select'].', ';
			$query .= 'COUNT(*) FROM `'.$this->_table_name.'`';
			if ( !empty($conditions) ) $query .= $this->build_query_conditions($conditions);
			if ( !empty($options['group_by']) ) $query .= ' GROUP BY '.$options['group_by'];
			$query .= ';';
		} else {	
			$query .= 'COUNT(*) FROM `'.$this->_table_name.'`';
			if ( !empty($conditions) ) {
				$query .= $this->build_query_conditions($conditions);
			}
			$query .= ';';
		}
		if ( $result = $this->query($query) ) {
			$count = mysql_fetch_assoc($result);
			return $count['COUNT(*)'];
		}
		return null;
	}
	
	
	/*
	   Query - send SQL query string
	*/
	function query ($query = null) {
		if ( $query !== null && $query !== false) {
			$result = mysql_query($query, $this->_db);
			if ( ZNAP_ENV == 'development' ){
			  self::$query_log[] = $query;
			  $this->sql_log($query);
			} 
			return $result;
		}
		return false;
	}
	
	
	/*
	   Table Operations - use with caution
	*/
	
	// Optimize table
	function optimize ($no_write_to_binlog = false) {
		$bin = ( $no_write_to_binlog ) ? 'NO_WRITE_TO_BINLOG ' : '';
		return $this->query('OPTIMIZE '.$bin.'TABLE '.$this->_table_name);
	}
	
	// WARNING!!! THIS WILL REMOVE ALL RECORDS IN THE TABLE!!
	function truncate ($are_you_sure = false) {
		if ( $are_you_sure ) {
			return $this->query('TRUNCATE TABLE '.$this->_table_name);
		}
	}
	
	
	
	
/*

   Add/Update Records
    - internal functions used by save()

*/

	function add_or_update_record () {
		$this->before_save();
		if ( $this->_new_record ) {
			$this->before_create();
			$result = $this->add_record();
			$this->after_create();
		} else {
			$this->before_update();
			$result = $this->update_record();
			$this->after_update();
		}
		$this->after_save();
		return $result;
	}
	
	function add_record () {
		$sql = $this->build_insert_query();
		$result = ( $sql !== false ) ? $this->query($sql) : false;
		$this->save_associations();
		return $result;
	}
	
	function update_record ( $value='' ) {
		$sql = $this->build_update_query();
		$result = ( $sql !== false ) ? $this->query($sql) : 1 ;
		$this->save_associations();
		return $result;
	}
	
	function update_attributes ($attributes = null) {
		if ( is_array($attributes) ) {
			foreach( $attributes as $field => $value ) {
				if ( array_key_exists($field, $this->_associations) && $this->_auto_save_associations ) {
					$this->_save_associations[$field] = $value;
				} else {
					if ( !$this->_new_record && $this->_track_modifications && !array_key_exists($field, $this) ) {
						$this->_init_value_hashes[$field] = md5($value);
					}
					$this->$field = $value;
				}
			}
		}
	}
	
	
	

/*

   Build X Query functions
    - build the actual SQL query strings from input data

*/

	function build_find_query ($conditions = null, $options = array()) {

		$select = ( isset($options['select']) && $options['select'] != '' ) ? $options['select'] : '*' ;
		$query = 'SELECT '.$select.' FROM `'.$this->_table_name.'`';
		
		if ( array_key_exists('joins', $options) ) {
			if ( !empty($options['joins']) ) {
				$query .= ' '.$options['joins'];
			}
			unset($options['joins']);
		}
		$query .= $this->build_query_conditions($conditions);
		$query .= $this->build_query_options($options);

		return $query.';';
	}

	function build_delete_query ($conditions = null, $options = array()) {

		$query = 'DELETE FROM `'.$this->_table_name.'`';

		$query .= $this->build_query_conditions($conditions, $options);
		$query .= $this->build_query_options($options);

		return $query.';';
	}

	function build_insert_query ($input = null) {

		if ( $input === null ) {
			$input = &$this;
		}

		// initial var setup & input checking
		if ( is_object($input) || (is_array($input) && count($input)) ) {
			if ( $this->_columns === null ) {
				$this->set_column_info();
			}
			if ( !is_array(current($input)) && !is_object(current($input)) ) {
				$input = array($input);
			}
		} else {
			return false;
		}

		// start building the query
		$query = 'INSERT INTO `'.$this->_table_name.'` ( ';

		// field/column definition
		$columns = array_keys($this->_columns);
		//FIXME Enable a way to set a custom primary key when saving a row
		# // if ( $columns[0] == $this->_primary_key ) unset($columns[0]);
		# $query .= '`'.implode('` , `', $columns).'` )';
		if ( $columns[0] == $this->_primary_key ) unset($columns[0]);
		$query .= '`'.$this->_primary_key.'`, `'.implode('` , `', $columns).'` )';

		$query .= ' VALUES ';
		$value_sets = array();
		foreach( $input as $current ) {
			$values = array();
			foreach( $columns as $column ) {
				if (
					$column == $this->_created_at_column 
					&& ( (is_array($current) && !isset($current[$column])) || (is_object($current) && !isset($current->$column)) ) 
				) {
					$values[] = $this->set_time_column($column);
				} elseif ( is_array($current) && isset($current[$column]) && $current[$column] !== null ) {
					$values[] = $this->sql_quote($current[$column]);
				} elseif ( is_object($current) && isset($current->$column) && $current->$column !== null ) {
					$values[] = $this->sql_quote($current->$column);
				} else {
					$values[] = ( $this->_columns[$column]['Null'] == 'YES' ) ? 'NULL' : "''" ;
				}
			}
			//FIXME Enable a way to set a custom primary key when saving a row
			# $value_sets[] = "( ".implode(" , ", $values)." )";
			$value_sets[] = "( NULL, ".implode(" , ", $values)." )";
		}	
		$query .= implode(', ', $value_sets).';';

		return $query;
	}

	function build_update_query ($input = null) {

		if ( $input === null ) {
			$input = &$this;
		}
		
		if ( $this->_columns === null ) {
			$this->set_column_info();
		}
		$columns = array_keys($this->_columns);
		if ( $columns[0] == $this->_primary_key ) unset($columns[0]);
		
		if ( is_object($input) ) {
			
			if ( isset($input->{$this->_primary_key}) && $input->{$this->_primary_key} !='' ) {
				$id = $input->{$this->_primary_key};
			} else {
				return false;
			}

			$values = array();
			foreach( $columns as $key ) {
				if (
					$key != $this->_primary_key && isset($input->$key) && isset($input->_columns[$key])
					&& (isset($input->_init_value_hashes[$key]) && $input->_init_value_hashes[$key] != md5($input->$key)) 
				) {
					if ( $key == $this->_modified_at_column ) {
						$values[] = $this->set_time_column($key);
					} elseif ( $input->$key !== null && $input->$key != '' ) {
						$values[] = '`'.$key."` = ".$this->sql_quote($input->$key);
					} else {
						$empty_value = ( $this->_columns[$key]['Null'] == 'YES' ) ? 'NULL' : "''";
						$values[] = '`'.$key."` = ".$empty_value;
					}
				}
			}

		} elseif ( is_array($input) ) {

			if ( isset($input[$this->_primary_key]) && $input[$this->_primary_key] !='' ) {
				$id = $input[$this->_primary_key];
			} else {
				return false;
			}

			$values = array();
			foreach( $columns as $key ) {
				if ( $key != $this->_primary_key && array_key_exists($key, $input) && isset($this->_columns[$key]) ) {
					if ( $input[$key] !== null ) {
						$values[] = '`'.$key."` = ".$this->sql_quote($input[$key]);
					} else {
						$values[] = '`'.$key."` = " . ( $this->_columns[$key]['Null'] == 'YES' ) ? 'NULL' : "''" ;
					}
				}
			}
		}

		if ( count($values) ) {
			return 'UPDATE `'.$this->_table_name.'` SET '.implode(', ', $values).' WHERE `'.$this->_primary_key."` = '".$id."' LIMIT 1;";
		} else {
			return false;
		}
	}


	/*
	   Build Helpers - help the build functions with repeatative tasks
	*/

	function build_query_conditions ($conditions, $options = array()) {
		if ( $conditions !== null && $conditions !== '' ) {
			if ( is_string($conditions) || is_int($conditions) || is_float($conditions) ) {
				if ( preg_match('/^[0-9]+$/', trim($conditions)) ) {
					return ' WHERE `'.$this->_primary_key."` = '".$conditions."'";
				} else {
	 				return ' WHERE '.$conditions;
				}
			} elseif ( is_array($conditions) && !empty($conditions) ) {
				$cond = array();
				foreach( $conditions as $key => $value ) {
					if ( !preg_match('/^[0-9]+$/', $key) && !is_array($value) ) {
						$cond[] = '`'.$key."` = '".$value."'";
					} elseif ( !is_array($value) && preg_match('/^[0-9]+$/', $value) ) {
						$cond[] = '`'.$this->_primary_key."` = '".$value."'";
					} elseif(is_array($value)) {
						$cond[] = '`'.$key."` IN (".implode(",",$this->sql_quote($value)).")";
  					} else {
						$cond[] = $this->sql_quote($value);
					}
				}
				$operator = ( !empty($options['operator']) ) ? $options['operator'] : 'AND' ;
				return ' WHERE '.implode(' '.$operator.' ', $cond);
			}
		}
		return '';
	}

	function build_query_options ($options) {
		if ( $options !== null && $options !== '' ) {
			if ( is_string($options) ) {
				return ' '.$options;
			} elseif ( is_array($options) && count($options) ) {
			
				$query = '';
				$query_end = ' ';
			  if ( !empty($options['group_by']) ){
			    $query .= ' GROUP BY '.$options['group_by'];
			    unset($options['group_by']);
			  } 
  			
				if ( isset($options['order_by']) && $options['order_by'] != '' ) {
					$order_by = trim($options['order_by']);
					$order = '';
					if ( preg_match('/^(.*)\s(ASC|DESC)$/i', $order_by, $capture) ) {
						$order = ' '.$capture[2];
						$order_by = trim($capture[1]);
					}
					unset($options['order_by']);
					if ( $order_by != 'RAND()' && strpos($order_by, '`') === false ) $order_by = '`'.$order_by.'`';
					$query .= ' ORDER BY '.$order_by.$order;
				}
				if ( (isset($options['limit']) && $options['limit'] != '') && (isset($options['offset']) && $options['offset'] != '') ) {
					$query_end .= 'LIMIT '.$options['limit'].' OFFSET '.$options['offset'];
					unset($options['limit'], $options['offset']);
				}
				
				foreach( $options as $key => $value ) {
					if ($key != 'operator' && $key != 'select' && $value != '') {
						$query .= ' '.strtoupper($key).' '.$value;
					}
				}
				return $query.$query_end;
			}
		}	
		return '';
	}




/*

   Table Associations
    - handle table associations

*/

	function set_associations () {
		foreach( $this->_association_types as $type ) {
			if ( !empty($this->$type) ) {
				if ( is_string($this->$type) ) {
					if ( strpos($this->$type, ',') === false ) {
						$this->set_association($this->$type, $type);
					} else {
						$associations = explode(',', $this->$type);
						foreach( $associations as $association ) {
							$this->set_association($association, $type);
						}
					}
				} elseif ( is_array($this->$type) ) {
					foreach( $this->$type as $name => $options ) {
						$this->set_association($name, $type, $options);
					}
				}
				unset($this->$type);
			}
		}
	}
	
	function set_association ($name, $type, $options = null) {
		$name = trim($name);
		if ( $type == 'has_one' || $type == 'belongs_to' ) {
			$name = Inflector::singularize($name);
		}
		$this->_associations[$name] = array('type' => $type);
		if ( is_array($options) ) {
			$this->_associations[$name] = array_merge($this->_associations[$name], $options);
		}
	}
	
	function get_association ($key) {
		if ( array_key_exists($key, $this->_associations) && !empty($this->{$this->_primary_key}) && !empty($this->_table_name) ) {
			$assoc = $this->_associations[$key];
			$class_name = ( !empty($assoc['class_name']) ) ? $assoc['class_name'] : Inflector::camelize($key) ;
			if ( $assoc['type'] == 'has_many' || $assoc['type'] == 'has_and_belongs_to_many' ) {
				$class_name = Inflector::singularize($class_name);
			}
			if ( class_exists($class_name, true) ) {
				
				$object = new $class_name();
				
				$foreign_key = $this->find_foreign_key(
					$key,
					$this->_associations,
					$this->_table_name,
					$object->_table_name
				);
				
				if ( $assoc['type'] != 'has_and_belongs_to_many' ) {
					if ( $assoc['type'] == 'belongs_to' ) {
						$conditions = '`'.$this->_primary_key.'` = '.$this->$foreign_key;
					} else {
						$conditions = '`'.$foreign_key.'` = '.$this->{$this->_primary_key};
					}
					if ( !empty($assoc['conditions']) ) {
						$conditions .= ' AND '.$assoc['conditions'];
					}
					$options = array();
					if ( !empty($assoc['joins']) ) {
						$options['joins'] = $assoc['joins'];
					}
					if ( !empty($assoc['select']) ) {
						$options['select'] = $assoc['select'];
					}
				}
				
				//FIXME return array or object code here is weird and confusing
				$return_array = null;
				if ( !empty($assoc['return_array']) ) {
					$return_array = true;
				} elseif ( !empty($assoc['return_object']) ) {
					$return_array = false;
				}
				
				if ( $assoc['type'] == 'has_one' || $assoc['type'] == 'belongs_to' ) {
					$result = $object->find($conditions, $options, $return_array);
				} elseif ( $assoc['type'] == 'has_many' ) {
					$result = $object->find_all($conditions, $options, $return_array);
				} elseif ( $assoc['type'] == 'has_and_belongs_to_many' ) {
					$join_table = array($this->_table_name, $object->_table_name);
					sort($join_table);
					$join_table = $join_table[0].'_'.$join_table[1];
					$table_key = Inflector::singularize($this->_table_name).'_id';
					$object_key = Inflector::singularize($object->_table_name).'_id';
					$conditions = '`'.$join_table.'`.`'.$table_key.'` = '.$this->{$this->_primary_key};
					$joins = 'LEFT JOIN '.$join_table.' ON `'.$object->_table_name.'`.`'.$object->_primary_key.'` = `'.$join_table.'`.`'.$object_key.'`';
					$result = $object->find_all($conditions, array('joins' => $joins));
				}
				if ( (is_array($result) && count($result) > 0 ) || is_object($result) ) {
					return $result;
				}
			}
		}
		return false;
	}

	function find_foreign_key ($key, $this_assoc, $this_table, $foreign_table) {
		if ( !empty($this_assoc[$key]['foreign_key']) ) {
			return $this_assoc[$key]['foreign_key'];
		} elseif ( $this_assoc[$key]['type'] == 'belongs_to' ) {
			return Inflector::singularize($foreign_table).'_id';
		} else {
			return Inflector::singularize($this_table).'_id';
		}
	}

	function save_associations () {
		if ( count($this->_save_associations) && $this->_auto_save_associations ) {
			foreach( $this->_save_associations as $field => $item ) {
				
				if ( !empty($this->_associations[$field]['Type']) ) {
					$type = $this->_associations[$field]['Type'];
					if ( $type != 'has_and_belongs_to_many' ) {
						if ( is_object($item) ) {
							$this->save_association($item, $type);
						} elseif ( is_array($item) ) {
							if ( is_array(current($item)) && is_array(end($item)) ) {
								$class_name = ( !empty($this->_associations[$field]['class_name']) )
									? $this->_associations[$field]['class_name'] : Inflector::camelize($field) ;
								if ( class_exists($class_name) ) {
									$object = new $class_name();
									$object->insert($array);
								}
							} else {
								foreach( $item as $sub_item ) {
									if ( is_object($sub_item) ) $this->save_association($sub_item, $type);
								}
							}
						}
					} else { // if type is "has_and_belongs_to_many"
						//TODO write habtm association saving code
					}
				}
				
			} // endforeach
		}
	}
	
	function save_association ($object, $type) {
		if ( is_object($object) && get_parent_class($object) == __CLASS__ ) {
			if ( $type == 'has_many' || $type == 'has_one' ) {
				$key = Inflector::singularize($this->_table_name).'_id';
				$object->$key = $this->{$this->_primary_key};
			}
			$object->save();
		}
	}



/*

   Error related functions
    - add errors if invalid input attempted

*/

	function add_error ($message = 'UNKNOWN', $field = null) {
		if ( !is_null($field) ) {
			$this->_errors[$field] = $message;
		} else {
			$this->_errors[] = $message;
		}
	}




/*

   Internal Functions
    - not really intended for public use, if you get what i mean ;) :P

*/	
	
	function sql_quote ($input, $field = null) {
		if ( is_array($input) ) {
			$return = array();
			foreach( $input as $key => $value ) {
				$return[$key] = $this->sql_quote($value, $field);
			}
			return $return;
		}
		if ( $field !== null ) {
			$field = $this->get_field_type($field);
		}
		if ( ($field == 'integer' || $field == 'decimal') && preg_match('/^[0-9\-\.]+$/', $input) ) {
			return $input;
		} else {
			return "'".addslashes(urldecode($input))."'";
		}
	}
	
	function get_field_type ($field) {
		if ( $this->_columns === null ) {
			$this->set_column_info();
		}
		if ( isset($this->_columns[$field]['Type']) && $this->_columns[$field]['Type'] != '' ) {
			$type = strtolower($this->_columns[$field]['Type']);
			if ( strstr($type, '(') !== false ) {
				$type = substr($type, 0, strpos($type, '('));
			}
			switch ( $type ) {
				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'int':
				case 'bigint':
					$type = 'integer';
					break;
				
				case 'float':
				case 'double':
					$type = 'decimal';
					break;
					
				default:
					$type = 'string';
			}
			return $type;
		}
		return false;
	}
	
	function set_time_column ($column) {
		// determine column type
		if ( strstr($this->_columns[$column]['Type'], '(') !== false ) {
			$type = substr($this->_columns[$column]['Type'], 0, strpos($this->_columns[$column]['Type'], '('));
		} else {
			$type = $this->_columns[$column]['Type'];
		}
		
		// format and resturn string
		if ( $type == 'int' ) {
			return time();
		} elseif ( $type == 'date' ) {
			return date('Y-m-d');
		} elseif ( $type == 'datetime' ) {
			return date('Y-m-d H:i:s');
		} elseif ( $type == 'timestamp' ) {
			return date('YmdHis');
		} elseif ( $type == 'time' ) {
			return date('H:i:s');
		} else {
			return time();
		}
	}
	
	function set_column_info () {
		if ( self::$table_info[$this->_connection_id][$this->_table_name] === null ) {
			$sql = 'SHOW COLUMNS FROM `'.$this->_table_name.'`;';
			$result = $this->query($sql);
			if ( mysql_num_rows($result) > 0 ) {
				while ($row = mysql_fetch_assoc($result)) {
					$this->_columns[$row['Field']] = $row;
				}
				if ( count($this->_columns) ) {
					foreach( $this->_columns as $column ) {
						$this->_columns[$column['Field']]['HumanName'] = Inflector::humanize($column['Field']);
					}
					return true;
				}
			}
		} else {
			$this->_columns = &self::$table_info[$this->_connection_id][$this->_table_name];
			return true;
		}
		return false;
	}
	
	function create_child_object ($attributes = array()) {
		$class_name = $this->_class_name;
		$object = new $class_name($attributes, array('child_object' => true, 'new_record' => false));
		//FIXME figure out a way to properly unset object vars since a php bug currently prevents it
		foreach( $this->_unset_in_child as $key => $value ) {
			unset($object->$key);
		}
		$object->_settings = &$this->_settings;
		$object->_connection_id = &$this->_connection_id;
		$object->_is_child_object = true;
		$object->_db = &self::$db[$this->_connection_id];
		$object->_columns = &self::$table_info[$this->_connection_id][$this->_table_name];
		$object->_class_name = &$this->_class_name;
		$object->_table_name = &$this->_table_name;
		$object->_primary_key = &$this->_primary_key;
		foreach( $this->_sub_class_config_values as $key => $value ) {
			if ( property_exists($this, '_'.$value) ) {
				$object->{'_'.$value} = $this->{'_'.$value};
			}
		}
		return $object;
	}
	
	function raise ($message, $details, $code) {
		throw new ActiveRecordError($message, $details, $code);
	}
	
	
/*

   Validation
    - validate new data before saving to database

*/

	function valid () {
		// make sure errors are empty
		// $this->_errors = array();

		if( $this->_new_record ) {
			$this->before_validation();
			$this->before_validation_on_create();
			$this->validate();
			$this->validate_model_attributes();
			$this->after_validation();
			$this->validate_on_save();
			$this->validate_on_create();
			$this->after_validation_on_create();
		} else {
			$this->before_validation();
			$this->before_validation_on_update();
			$this->validate();
			$this->validate_model_attributes();
			$this->after_validation();
			$this->validate_on_save();
			$this->validate_on_update();
			$this->after_validation_on_update();
		}
		
		// if validation functions generated errors, flash them
		if ( count($this->_errors) ) {
			$this->flash_errors();
			return false;
		}
		return true;
	}

	function validate_model_attributes () {
		if ( $this->_columns === null ) {
			$this->set_column_info();
		}
		$valid = true;
		foreach( $this->_columns as $column => $column_info ) {
			if ( method_exists($this, 'validate_'.$column) ) {
				$method = 'validate_'.$column;
				$result = $this->$method();
				
				if ( $result !== null ) {
					$error = $result;
				}
				
				if ( isset($error) ) {
					if ( $error === false ) {
						$error = str_replace('%column%', $column, Znap::$strings['_ZNAP_AR_ERROR_ITEM_UKNOWN']);
					}
					$this->add_error($error, $column);
					$valid = false;
				}
			}
			unset($error);
		}
		return $valid;
	}
	
	function flash_errors ($errors = array()) {
		if ( count($errors) == 0 ) {
			$errors = &$this->_errors;
		}
		$list = '';
		foreach( $errors as $key => $value ) {
			$list .= str_replace('%error%', $value, Znap::$strings['_ZNAP_AR_ERROR_ITEM_HTML']);
		}
		$body = str_replace('%errors%', $list, Znap::$strings['_ZNAP_AR_ERROR_BODY_HTML']);
		Session::flash('error', str_replace('%title%', Znap::$strings['_ZNAP_AR_ERROR_TITLE'], $body));
		$this->_errors = array();
	}
	
	function sql_log($query = null, $logfile = null){
		$message = '';
		if ( ZNAP_INTERNAL_LOGGING ) {
			$stamp = date("[j-M-Y G:i:s] ");
			$logfile = Znap::$log_path.'/'.ZNAP_ENV.'_sql.log';
			$message = "/*\n".$stamp."\n*/\n\n".$query."\n\n";
			if ( !(file_put_contents($logfile, $message, FILE_APPEND)) ){
				$details = $logfile . ' cannot be opened.';
				Znap::$current_controller_object->raise('Cannot open the '.ZNAP_ENV.' logfile.', $details, 500 );
			}
		}
	}
	



/*

   Redeclareable functions
    - redeclare any of the functions below for custom validation and checking

*/

	/*
	   before/after save, create and update
	*/
	function before_save () {}
	
	function before_create () {}
	
	function after_create () {}
	
	function before_update () {}
	
	function after_update () {}
	
	function after_save () {}


	/*
	   global validate before/after validation, on create and on update
	*/
	function validate () {}
	
	function validate_on_save () {}
	
	function validate_on_create () {}
	
	function validate_on_update () {}
	
	function before_validation () {}
	
	function before_validation_on_create () {}
	
	function after_validation_on_create () {}

	function before_validation_on_update () {}
	
	function after_validation_on_update () {}

	function after_validation () {}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>