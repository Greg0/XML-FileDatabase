<?php

 /**
  * PHP Class to use XML file like a FlatFileDatabase
  *
<<<<<<< HEAD
  * @author Grego
  * @copyright 2011 http://greg0.ovh.org
=======
  * @author Grego http://greg0.ovh.org
  * @copyright 2012 Grzegorz K.
>>>>>>> cce73d677a891b3559e97981c76f44ef49564e43
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
      * @var type 
      */
     private $_row_id = null;

     /**
      * Instance of Data object or Objects array
      * @var object|array 
      */
     private $_data;

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
<<<<<<< HEAD
     
=======

>>>>>>> cce73d677a891b3559e97981c76f44ef49564e43
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
             $this->_row_id = $id;
         }
         return $this;
     }

     /**
      * Insert XMl into Data object and put instance into this->_data
      * @return \Database
      */
     private function _xml_to_object()
     {
         $xml = $this->xml;
         $data = Data::getInstance();

         if (!is_numeric($this->_row_id))
         {
             foreach ($xml->row as $row)
             {
                 $obj = clone $data;

                 foreach ($row->field as $field)
                 {
                     $obj->{$field->attributes()->name} = $field;
                 }
                 $this->_data[] = $obj;
             }
         }
         else
         {
             $row = (int) $this->_row_id;
             $fields = $xml->row[$row];
             $obj = $data;

             foreach ($fields as $field)
             {
                 $obj->{$field->attributes()->name} = $field;
             }
             $this->_data = $obj;
         }

         return $this;
     }

     /**
<<<<<<< HEAD
=======
      * Returning data for multi select
      * @return \Data
      */
     public function find_all()
     {
         return $this->_data;
     }

     /**
      * Sort array of objects DESC by ID
      * @return \Database 
      */
     public function order_by_desc()
     {
         if (is_array($this->_data))
         {
             $this->_data = array_reverse($this->_data, true);
         }

         return $this;
     }

     /**
>>>>>>> cce73d677a891b3559e97981c76f44ef49564e43
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

<<<<<<< HEAD
         $this->_xml_to_object();
=======
         try {
             $this->_xml_to_object();
         }
         catch (Exception $msg) {
             throw new Exception('Brak rekordu');
         }
>>>>>>> cce73d677a891b3559e97981c76f44ef49564e43

         return (is_numeric($this->_row_id)) ? Data::getInstance() : $this;
     }

     /**
<<<<<<< HEAD
      * Returning data for multi select
      * @return \Data
      */
     public function find_all()
     {
         return $this->_data;
     }

     /**
      * Sort array of objects DESC by ID
      * @return \Database 
      */
     public function order_by_desc()
     {
         if (is_array($this->_data))
         {
             $this->_data = array_reverse($this->_data, true);
         }

         return $this;
     }

     public function save()
     {
         $data = Data::getInstance();
         $row = $this->xml->addChild('row');

         foreach(get_object_vars($data) as $name => $value)
         {
             $field = $row->addChild('field', $value);
             $field->addAttribute('name', $name);
         }
         
         return $this->xml->asXML($this->file);
=======
      * Add/Edit rows
      * @return asXML 
      */
     public function save()
     {
         $data = Data::getInstance();

         if (is_numeric($this->_row_id))
         {
             $row = $this->xml->row[$this->_row_id];
             $i = 0;
             foreach (get_object_vars($data) as $name => $value)
             {
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
      * @throws Exception nie wybrano rekordu
      */
     public function delete()
     {
         if (isset($this->_row_id))
         {
             unset($this->xml->row[$this->_row_id]);
             return $this->xml->asXML($this->file);
         }

         throw new Exception('Nie wybrano rekordu do kasacji');
>>>>>>> cce73d677a891b3559e97981c76f44ef49564e43
     }

 }

?>
