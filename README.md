XML File Based Database
=============

PHP Class to use XML file like a FlatFileDatabase


XML file database schema
-------

For example table with few news (news.xml):

    <?xml version="1.0" encoding="UTF-8"?>
    <table name="news">
        <row>
            <field name="title">My first news</field>
            <field name="author">Grego</field>
            <field name="content">
              Lorem ipsum dolor sit amet enim. Etiam ullamcorper. Suspendisse a pellentesque dui, non felis. Maecenas malesuada elit lectus felis, malesuada ultricies. 
            </field>
        </row>
        <row>
            <field name="title">Some second news</field>
            <field name="author">Admin</field>
            <field name="content">
              Curabitur et ligula. Ut molestie a, ultricies porta urna. Vestibulum commodo volutpat a, convallis ac, laoreet enim.
            </field>
        </row>
    </table></code>


Usage
------

First of all you should define constant DB_PATH containing path to folder with XMLs files and include class file:

     define('DB_PATH', 'db/');
     require 'xmlDB.php';

I assume that our XML path looks like `db/news.xml`.

### Select

##### Multiple select

    $db = Database::factory('news');
    $news = $db->select()->order_by_desc()->find_all();
    
    foreach($news as $post)
    {
      echo '<h1>'.$post->title.'</h1>';
      echo '<small>author: '.$post->author.'</small>';
      echo '<p>'.$post->content.'</p>';
    }
No need to use `order_by_desc()`

##### Single record select

    $db = Database::factory('news', 0);
    $news = $db->select();

    echo '<h1>'.$news->title.'</h1>';
    echo '<small>author: '.$news->author.'</small>';
    echo '<p>'.$news->content.'</p>';
Type ID of record after file name in `factory()` method.
Don't use any methods after `select()`

###### Get few fields

No need to get all of fields from record. you can specify names of fields you want to get in `select()` method by typing them in array.

    select(array('title', 'content'))



