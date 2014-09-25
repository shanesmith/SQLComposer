SQLComposer
===========

An object-oriented way to build SQL statements in PHP.

Getting Started
---------------

Create a query object with any of:

```php
SQLComposer::select();
SQLComposer::update();
SQLComposer::insert();
SQLComposer::replace();
SQLComposer::delete();
```

Then build your query:
```php
$sqlc = SQLComposer::select(array("u.id", "u.name", "c.color fav_color"))
	->from("users u")
	->join("JOIN colors c ON c.user_id=u.id")
	->where("u.city=?, array($city))
	->where("u.age between ? and ?", array($min_age, $max_age))
	->order_by("u.name")
	->limit(25);
```
And pass the renderings to your favorite flavor of database:
```php
$db->Execute(
	$sqlc->getQuery(),
	$sqlc->getParams()
);
```
Which would result in:
```php
$db->Execute("
	SELECT u.id, u.name, c.color fav_color
	FROM users u
		JOIN colors c ON c.user_id=u.id
	WHERE (u.city=?) AND (u.age between ? and ?)
	ORDER BY u.name
	LIMIT 25",

	array($city, $min_age, $max_age)
);
```

Dynamic Queries
---------------

SQLComposer is better used when the SQL query needs to be build dynamically
since the clause definition order does not matter:
```php
$sqlc = SQLComposer::update('users u')->limit(1);

if ($_POST['name']) {
	$sqlc->set("u.name=?", $_POST['name']);
}

if ($_POST['color']) {
	$sqlc
		->join("JOIN colors c ON c.user_id=u.id")
		->set("c.color=?", $_POST['color']);
}

$sqlc->where("u.id=?", $_POST['user_id']);

$db->Execute( $sqlc->getQuery(), $sqlc->getParams() );
```

MySQLi
------

SQLComposer also supports MySQLi parameter types by including them as the last argument
to methods that accept parameters:
```php
$sqlc = SQLComposer::select('*')
	->from('users')
	->where("age between ? and ?", array($min, $max), "ii")
	->where("city = ?", array($city), "s");
```
Which will result in the parameter array:
```php
array("iis", $min, $max, $city
```
