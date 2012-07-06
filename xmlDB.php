<?php

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
      * Instance of Data object or Objects array
      * @var object|array 
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
      * Factory pattern to load needed Classes
      * @param string $filename name of file without extension
      * @param int $id optional ID of row we want to select
      * @return \Database This object 
      */
     public static function factory($filename, $id = NULL)
     {
         $db = new Database();
         $db->set_filename($filename, $id);
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
<table name="'.$filename.'">
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
         unlink($db->file_path.$filename.'.xml');
     }

     /**
      * Set fields to Data object
      * @param string $name
      * @param string $value
      * @return \Database 
      */
     public function __set($name, $value)
     {
         $this->_data = Data::getInstance();
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
     private function set_filename($filename = NULL, $id = NULL)
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

             $id = 0;
             foreach ($xml->row as $row)
             {
                 $obj = clone $data;
                 $obj->id = $id;
                 foreach ($row->field as $field)
                 {
                     $obj->{$field->attributes()->name} = (string) $field;
                 }

                 $this->_data[] = $obj;
                 $id++;
             }
         }
         else
         {
             $row_id = (int) $this->_row_id;
             $fields = $xml->row[$row_id];

             $this->check_records($fields);

             $obj = $data;

             foreach ($fields as $field)
             {
                 $obj->{$field->attributes()->name} = $field;
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
         return $this->_data;
     }

     /**
      * Returning count of rows
      * @return \Data
      */
     public function count()
     {
         return count($this->_data);
     }

     /**
      * Sort array of objects DESC by ID
      * @return \Database 
      */
     public function order_by($key, $order = 'ASC')
     {
         $this->_key = $key;
         $this->_order = $order;

         if (is_array($this->_data))
         {
             usort($this->_data, array($this, "cmp"));
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
         $this->_data = array_slice($this->_data, $offset, $number);
         return $this;
     }

     /**
      * Returning row(s) with specified field value
      * @param   string   column name
      * @param   string  logic operator
      * @param   string   column value
      * @return  $this
      */
     public function where($column, $op, $value)
     {
         $operator = array(
             '=' => '!=',
             '!=' => '==',
             '>' => '<=',
             '<' => '>=',
             '>=' => '<',
             '<=' => '>'
         );


         foreach ($this->_data as $row)
         {
             eval('$exec = strtolower($row->{$column}) '.$operator[$op].' strtolower($value);');

             if ($exec)
             {
                 unset($this->_data[$row->id]);
             }
         }

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
         $data = Data::getInstance();

         if (isset($this->_row_id))
         {
             $row = $this->xml->row[$this->_row_id];
             $i = 0;
             foreach (get_object_vars($data) as $name => $value)
             {
                 if ($name != 'id')
                     $row->field[$i] = $value;
                 $i++;
             }
         }
         else
         {
             $row = $this->xml->addChild('row');
             foreach (get_object_vars($data) as $name => $value)
             {
                 $field = $row->addChild('field', $value);
                 $field->addAttribute('name', $name);
             }
         }
         return $this->xml->asXML($this->file);
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
             unset($this->xml->row[$this->_row_id]);
             return $this->xml->asXML($this->file);
         }

         throw new Exception('Row ID not specified');
     }

 }

?>
