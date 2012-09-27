<?php

 defined('DB_PATH') or die('Define your DB_PATH constance');

 /**
  * PHP Class to use XML file like a FlatFileDatabase
  *
  * @author Grego http://greg0.ovh.org
  * @copyright 2012 Grzegorz K.
  */
 class Data {

     private static $_instance;

     public static function getInstance()
     {
         if (!self::$_instance)
         {
             self::$_instance = new self();
         }

         return self::$_instance;
     }

     public static function newInstance()
     {
         self::$_instance = new self();
         return self::$_instance;
     }

 }

 class Database {

     /**
      * Path to folder with XML files. Must be DB_PATH defined.
      * @var string 
      */
     public $file_path = DB_PATH;

     /**
      * Instance of SimpleXML object with our xml file
      * @var object 
      */
     public $xml;

     /**
      * Name of XML file
      * @var string 
      */
     private $_file_name;

     /**
      * Content of XML file
      * @var string
      */
     private $_file_content;

     /**
      * Full path to file with his name and extension
      * @var string
      */
     public $file;

     /**
      * Contain id of selected ROW if is set.
      * @var int
      */
     private $_row_id = null;

     /**
      * \Data Objects array
      * @var array 
      */
     private $_datas;

     /**
      * Instance of \Data object
      * @var object
      */
     private $_data;

     /**
      * Contain key to order_by() method
      * @var int|string
      */
     private $_key;

     /**
      * Contain order way to order_by() method
      * @var int|string
      */
     private $_order;

     /**
      * 
      * @var array
      */
     private $_where = array();

     /**
      * Where type
      * @var string
      */
     private $_where_type = 'and';

     /**
      * Last ID
      * @var string
      */
     private $_last_id = 0;

     /**
      * Factory pattern to load needed Classes
      * @param string $filename name of file without extension
      * @param int $id optional ID of row we want to select
      * @return \Database This object 
      */
     public static function factory($filename, $id = NULL)
     {
         Data::newInstance();
         $db = new Database();
         $db->_data = new Data();
         $db->set_informations($filename, $id);
         $db->xml = simplexml_load_file($db->file);
         return $db;
     }

     /**
      * Creating new database
      * @param string $filename name of file without extension
      */
     public static function create($filename)
     {
         $content = '<?xml version="1.0" encoding="UTF-8"?>
<table name="'.$filename.'" lastID="0">
</table>';
         $db = new Database();
         if (!file_exists($db->file_path.$filename.'.xml'))
         {
             file_put_contents($db->file_path.$filename.'.xml', $content);
         }
         else
             throw new Exception('Database already exists');
     }

     /**
      * Remove database
      * @param string $filename name of file without extension
      */
     public static function remove($filename)
     {
         $db = new Database();
         if (file_exists($db->file_path.$filename.'.xml'))
         {
             unlink($db->file_path.$filename.'.xml');
         }
         else
         {
             throw new Exception('Database don\'t exists');
         }
     }

     /**
      * Set fields to Data object
      * @param string $name
      * @param string $value
      * @return \Database 
      */
     public function __set($name, $value)
     {
         $this->_data->{$name} = $value;

         return $this;
     }

     /**
      * Getter of file name
      * @return string 
      */
     public function getName()
     {
         return $this->_file_name;
     }

     /**
      * Getter of file content
      * @return string 
      */
     public function getFile()
     {
         return $this->_file_content;
     }

     /**
      * Set needed variables
      * @param string $filename
      * @param int $id
      * @return \Database This object
      */
     private function set_informations($filename = NULL, $id = NULL)
     {
         if ($filename !== NULL)
         {
             $this->file = $this->file_path.$filename.'.xml';
             $this->_file_name = $filename;
             $this->_file_content = file_get_contents($this->file);
             $this->_row_id = ($id !== null) ? (int) $id : null;
         }
         return $this;
     }

     /**
      * Check for records
      * @param mixed $record
      * @throws Exception No record found
      */
     private function check_records($record)
     {
         if (!$record)
         {
             throw new Exception('No data found');
         }
     }

     /**
      * Check type of var
      * @param mixed $record
      * @throws Exception No record found
      */
     private function check_type($value)
     {
         if (is_array($value))
         {
             $type = 'array';
         }
         elseif (is_int($value))
         {
             $type = 'integer';
         }
         elseif (is_bool($value))
         {
             $type = 'boolean';
         }
         elseif (is_string($value))
         {
             $type = 'string';
         }
         elseif (is_float($value))
         {
             $type = 'float';
         }
         elseif (is_double($value))
         {
             $type = 'double';
         }
         return $type;
     }

     /**
      * Return type of var
      * @param mixed $record
      * @throws Exception No record found
      */
     private function return_value($field)
     {
         $type = $field->attributes()->type;
         if ($type == 'array')
         {
             return unserialize($field);
         }
         elseif ($type == 'integer')
         {
             return (integer) $field;
         }
         elseif ($type == 'boolean')
         {
             return (boolean) $field;
         }
         elseif ($type == 'string')
         {
             return (string) $field;
         }
         elseif ($type == 'float')
         {
             return (float) $field;
         }
         elseif ($type == 'double')
         {
             return (double) $field;
         }
     }

     /**
      * get last ID
      * @return int $_last_id 
      */
     public function get_last_id()
     {
         return $this->xml['lastID'];
     }

     /**
      * Insert XMl into Data object and put instance into this->_data
      * @return \Database
      */
     private function _xml_to_object()
     {
         $xml = $this->xml;
         $data = Data::getInstance();
         if (!isset($this->_row_id))
         {
             $this->check_records($xml->row);

             foreach ($xml->row as $row)
             {
                 $id = $row->attributes()->id;
                 $obj = clone $data;
                 $obj->id = (int) $id;
                 foreach ($row->field as $field)
                 {
                     $obj->{$field->attributes()->name} = $this->return_value($field);
                 }
                 $this->_datas[] = $obj;
             }
         }
         else
         {
             $row_id = (int) $this->_row_id;
             $fields = $xml->xpath('/table/row[@id="'.$row_id.'"]/field');

             $this->check_records($fields);

             $obj = $data;

             foreach ($fields as $field)
             {
                 $obj->{$field->attributes()->name} = $this->return_value($field);
             }
             $obj->id = $row_id;

             $this->_data = $obj;
         }

         return $this;
     }

     /**
      * comparison function to usort in order_by() method
      * @param obj $obja
      * @param obj $objb
      * @return int 
      */
     private function cmp($obja, $objb)
     {
         $a = $obja->{$this->_key};
         $b = $objb->{$this->_key};
         if ($a == $b)
         {
             return 0;
         }
         if ($this->_order == 'ASC')
         {
             return ($a < $b) ? -1 : 1;
         }
         elseif ($this->_order == 'DESC')
         {
             return ($a > $b) ? -1 : 1;
         }
     }

     /**
      * Returning data for multi select
      * @return \Data
      */
     public function find_all()
     {
         if (is_array($this->_where) AND !empty($this->_where))
         {
             call_user_func(array($this, '_where'));
         }

         return array_values($this->_datas);
     }

     /**
      * Returning count of rows
      * @return int
      */
     public function count()
     {
         $this->_where();
         return count($this->_datas);
     }

     /**
      * Sort array of objects DESC by ID
      * @return \Database 
      */
     public function order_by($key, $order = 'ASC')
     {
         $this->_key = $key;
         $this->_order = $order;

         if (is_array($this->_datas))
         {
             usort($this->_datas, array($this, "cmp"));
         }

         return $this;
     }

     /**
      * Set limit to array of Data objects
      * @param int $number number of rows
      * @param int $offset offset 
      * @return \Database 
      */
     public function limit($number, $offset = 0)
     {
         $this->_datas = array_slice($this->_datas, $offset, $number);
         return $this;
     }

     /**
      * Getting all conditions to array
      * @param mixed $column
      * @param string $operator
      * @param mixed $value
      * @return $this
      */
     public function where($column, $operator, $value, $type = 0)
     {
         $condition = func_get_args();
         is_array(reset($condition)) and $condition = reset($condition);

         if ($type)
         {
             $this->_where_type = 'or';
         }

         $this->_where[] = $condition;

         return $this;
     }

     /**
      * Alias for where()
      * @param mixed $column
      * @param string $operator
      * @param mixed $value
      * @return $this
      */
     public function and_where($column, $operator, $value)
     {
         return $this->where($column, $operator, $value);
     }

     /**
      * Alias for where()
      * @param mixed $column
      * @param string $operator
      * @param mixed $value
      * @return $this
      */
     public function or_where($column, $operator, $value)
     {
         return $this->where($column, $operator, $value, 1);
     }

     /**
      * Callback function for array_filter() in _where() method
      * @param type $row
      * @return boolean 
      */
     private function _where_filter($row)
     {
         $operator = array(
             '=' => '==',
             '!=' => '!=',
             '>' => '>',
             '<' => '<',
             '>=' => '>=',
             '<=' => '<='
         );

         $result = true;

         foreach ($this->_where as $where)
         {
             $column = $where[0];
             $op = $where[1];
             $value = $where[2];

             eval('$exec = strtolower($row->{$column}) '.$operator[$op].' strtolower($value);');
             if ($exec)
             {
                 $result = true;
                 if ($this->_where_type == 'or')
                     break;
                 else
                     continue;
             }
             else
             {
                 $result = false;
                 if ($this->_where_type == 'or')
                     continue;
                 else
                     break;
             }
         }

         return $result;
     }

     /**
      * Filtering array of results
      * @return \Database 
      */
     private function _where()
     {
         $this->_datas = array_filter($this->_datas, array($this, '_where_filter'));

         return $this;
     }

     /**
      * Run selecting data. Returning specified data if $fields are specified
      * @param array $fields fields you want to retrive
      * @return \Database|\Data depending on multi/single select
      */
     public function select($fields = null)
     {
         if (is_array($fields))
         {
             $xml = $this->xml;

             foreach ($xml->row as $row)
             {
                 foreach ($row->field as $field)
                 {
                     if (!in_array($field->attributes()->name, $fields))
                     {
                         $target = $row->xpath('//field[@name="'.$field->attributes()->name.'"]');

                         foreach ($target as $node)
                         {
                             $domRef = dom_import_simplexml($node);
                             $domRef->parentNode->removeChild($domRef);
                         }
                     }
                 }
             }
         }

         try {
             $this->_xml_to_object();
         }
         catch (Exception $msg) {
             throw $msg;
         }

         return (isset($this->_row_id)) ? Data::getInstance() : $this;
     }

     /**
      * Add/Edit rows
      * @return asXML 
      */
     public function save()
     {
         if (isset($this->_row_id))
         {
             $this->_edit();
         }
         else
         {
             $this->_add();
         }
         return $this->xml->asXML($this->file);
     }

     /**
      * Edit row
      */
     private function _edit()
     {
         $data = $this->_data;

         $row = $this->xml->xpath('/table/row[@id="'.$this->_row_id.'"]');
         $i = 0;
         foreach (get_object_vars($data) as $name => $value)
         {
             $type = $this->check_type($value);
             $value = ($type=='array') ? serialize($value) : $value;

             $row[0]->field['type'] = $type;

             if ($name != 'id')
                 $row[0]->field[$i] = $value;
             $i++;
         }
     }

     /**
      * Add row
      */
     private function _add()
     {

         $data = $this->_data;
         $this->xml['lastID']+=1;

         $row = $this->xml->addChild('row');
         $row->addAttribute('id', $this->get_last_id());
         foreach (get_object_vars($data) as $name => $value)
         {
             $type = $this->check_type($value);
             $value = ($type=='array') ? serialize($value) : $value;

             $field = $row->addChild('field', $value);
             $field->addAttribute('name', $name);
             $field->addAttribute('type', $type);
         }
     }

     /**
      * Delete row
      * @return string
      * @throws Exception row ID not specified
      */
     public function delete()
     {
         if (isset($this->_row_id))
         {
             $row = $this->xml->xpath('/table/row[@id="'.$this->_row_id.'"]');
             unset($row[0][0]);
             return $this->xml->asXML($this->file);
         }
         else
         {
             $this->_where();
             foreach (array_reverse($this->_datas) as $obj)
             {
                 $xml = $this->xml;
                 $row = $xml->xpath('/table/row[@id="'.$obj->id.'"]');
                 unset($row[0][0]);
             }
             return $xml->asXML($this->file);
         }

         throw new Exception('Error with deleting');
     }

     public function update()
     {
         $xml = $this->xml;
         $rows_no = $xml->count();
         $xml->addAttribute('lastID', $rows_no);

         $id = 1;
         foreach ($xml->row as $row)
         {
             $row->addAttribute('id', $id);
             $id++;
         }
         return $xml->asXML($this->file);
     }

 }

?>