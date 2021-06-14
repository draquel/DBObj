# DBObj

## Basic Usage
```php
	//Create New DBObj
	$obj = new DBObj(0,"Posts");
	$obj->initMysql(array('Created'=>time(),'Updated'=>time()));
	$obj->dbWrite($mysqli_db_connection);

	//Edit existing DBObj
	$obj = new DBObj($id,"Posts");
	$obj->dbRead($mysqli_db_connection);
	$a = $obj->toArray();
	
	echo $a['ID'].", ".date("Y-m-d h:i:s",$a['Created']).", ".date("Y-m-d h:i:s",$a['Updated']);
	$a['Updated'] = time();
	
	$obj->initMysql($a);
	$obj->dbWrite($mysqli_db_connection);
```
