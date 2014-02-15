RethinkDB Active Record for PHP
================
http://php-rql.dnsalias.net/wiki/index.php/HomePage

Product.php
```
<?php
namespace Test;
class Product extends \RDB {
	public static $table = 'Product';
	public $categoryId;
	public $id;
	public $name;
}
Product::initialize();
?>
```

http://www.rethinkdb.com/docs/security/

```
<?php
// create database first !!
// r.dbCreate('databaseName').run(conn, callback)
RDB::connect('localhost', 28015, 'databaseName', 'mypassword');

// first time only, create the table
\Test\Product::createTable();

$product = new \Test\Product();
$product->name = 'product '.rand();
$product->save();

$product->name = 'change name';
$product->save();

$product = \Test\Product::get('the key');
$product->delete();
?>
```
