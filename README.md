File Database based on XML 
=============

PHP Class to use XML file(s) like a FlatFileDatabase

Requirements
------

- PHP 5 +
- SimpleXML
- DOMXPath

XML file database schema
-------

Example of table `news` with few records.

news.xml:

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
    </table>


Usage
------

First of all you should define constant `DB_PATH` containing absolute path to folder with XML files and include class file:

     define('DB_PATH', __DIR__.'/db/');
     require 'xmlDB.php';

I assume that our XML path looks like `db/news.xml`.

### Create database

    Database::create('name_of_database');
	
### Remove database

    Database::remove('name_of_database');

### Select

#### Multiple select

    $news = Database::factory('news')
              ->select()->order_by('id', 'DESC')->find_all();
    
    foreach($news as $post)
    {
      echo '<h1>'.$post->title.'</h1>';
      echo '<small>author: '.$post->author.', ID: '.$post->id.'</small>';
      echo '<p>'.$post->content.'</p>';
    }
No need to use `order_by_desc()`

#### Single record select

    $news = Database::factory('news', 1)
              ->select();

    echo '<h1>'.$news->title.'</h1>';
    echo '<small>author: '.$news->author.', ID: '.$news->id.'</small>';
    echo '<p>'.$news->content.'</p>';
Type ID of record after file name in `factory()` method.
Don't use any methods after `select()`

##### Get few fields

You haven't to load all fields from record. Possible to specify names of fields you want to get in `select()` method by typing them in array:

    select(array('title', 'content'))
Will return only fields `title` and `content`

##### Chain methods for `select()` in multiple mode

- `find_all()` - returns array of rows. 
- `count()` - return count of rows. 
- `order_by()` - sort rows by key and in order. First parameter is `key` second (optional) `order`. 
- `limit()` - return specified number of rows. Second parameter (optional) it's offset.
- `where()` - return row(s) with specified field value - (column, operator, value). 
- `or_where()` - alias for where(). Other type of sorting results. Example:

    $row = Database::factory('news')->select()->where('author', '=', 'admin')->or_where('author', '=', 'me')->find_all();
    //will return all news where author is Admin and Me

### Insert

It's pretty simple:

    $row = Database::factory('news');
    
    $row->title = 'Inserted news';
    $row->author = 'me';
    $row->content = 'Some content to my inserted news';

    $row->save();
It will add new row on the end of DOM.

### Update

It's very smilar to `Inserting`, you must set ID of row you want to edit in `factory()` second parameter.

    $row = Database::factory('news',1); //Will edit row with ID 1
    $row->select();

    $row->title = 'Edited news';
    $row->author = 'me';
    $row->content = 'Some edited content to my inserted news';

    $row->save();
Don't forget about to call `select()` method

### Remove

#### Single record deleting
<<<<<<< HEAD

    $row = Database::factory('news',1); //Will remove row with ID 1
    $row->delete();

#### Multiple records deleting

=======

    $row = Database::factory('news',1); //Will remove row with ID 1
    $row->delete();

#### Multiple records deleting

>>>>>>> 19f679e75219963f9dc6a50f33f49950c6c9f294
    Database::factory('news')->select()->where('author', '=', 'admin')->delete();

Description
------

That's all for now, I think it is possible to write something else in future ;)

My homepage: <http://greg0.ovh.org>

If you like and using/want to use my repo send few words to my e-mail ;)
<<<<<<< HEAD
=======

>>>>>>> 19f679e75219963f9dc6a50f33f49950c6c9f294
