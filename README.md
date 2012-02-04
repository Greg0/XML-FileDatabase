File Database based on XML 
=============

PHP Class to use XML file(s) like a FlatFileDatabase


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

### Select

#### Multiple select

    $rows = Database::factory('news');
    $news = $rows->select()->order_by_desc()->find_all();
    
    foreach($news as $post)
    {
      echo '<h1>'.$post->title.'</h1>';
      echo '<small>author: '.$post->author.', ID: '.$post->id.'</small>';
      echo '<p>'.$post->content.'</p>';
    }
No need to use `order_by_desc()`

#### Single record select

    $row = Database::factory('news', 0);
    $news = $row->select();

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

- `find_all()` - returns array of rows.  `required`
- `order_by_desc()` - sort rows from oldest to newest.  `optional`
- `limit()` - return specified number of rows. Second parameter (optional) it's offset.  `optional`

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

    $row = Database::factory('news',1); //Will remove row with ID 1
    $row->delete();


Description
------

That's all for now, I think it is possible to write something else in future ;)

My homepage: <http://greg0.ovh.org>

