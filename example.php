<?php
 define('DB_PATH', __DIR__.'/db/');
 require_once 'xmlDB.php';
 if (isset($_POST['add']))
 {
     unset($_POST['add']);
     $news = Database::factory('news');
     foreach ($_POST as $field => $value)
     {
         $news->{$field} = $value;
     }
     $news->save();
 }
 $count = Database::factory('news')->select()->count();
?>

<form action="" method="post">
    <label>Title:</label>
    <input type="text" name="title" /><br />
    <label>Author:</label>
    <input type="text" name="author" /><br />
    <label>Content:</label>
    <textarea name="content"></textarea><br />
    <input type="submit" name="add" />
</form>

<h1>Newest informations, you have <?php echo $count; ?> news</h1>
<hr />
<?php
 $news = Database::factory('news')->select()->order_by('id', 'DESC')->find_all();
 foreach($news as $post): 
?>
    
<h2><?php echo $post->title; ?></h2>
<small><?php echo $post->author; ?></small>
<p><?php echo $post->content; ?></p>
<hr />
<?php endforeach; ?>