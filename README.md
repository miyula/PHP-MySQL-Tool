# PHP-MySQL-Tool
A tool for PHP to communicate with MySQL that flex support mysql and mysqli

一个PHP使用MySQL的工具库。兼容mysql和mysqli。默认mysqli，向下兼容mysql。

## 使用方法

1. 引用
```   
require("Database.php");
```
2. 连接数据库
```
$dbConf = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => 'xxxxxx',
    'name' => 'account',
    );
    
$db = connectDB($dbConf);
```
3. 查询表a

```
$list = $db->selectRows('a');   // 返回表a的数据  select * from a;

$row = $db->selectRow('a');     // 返回表a的一条数据 select * from a LIMIT 1;

$list = $db->selectRows(
    'a', 
    'a_id, a_age, a_name, a_state', 
    array(
        'a_state' => 2, 
        'a_age > 20'
        ), 
    array(
        'ORDER BY' => 'a_age DESC', 
        'GROUP BY' => 'a_name'
    );   
// select a_id, a_age, a_name, a_state from a where a_state = 2 and a_age > 20 ORDER BY a_age DESC GROUP BY a_name
```

4. 修改表a
```
$db->update('a', array('a_state'=> 0), array('a_age' => 10));

// updata a set a_state = 0 where a_age = 10
```

5. 插入数据
```
$db->insert('a', array('a_age' => 10, 'a_name' => 'xxxx', 'a_state' => 1));

$insertId = $db->insertId();
```

6. 删除数据
```
$db->delete('a', array('a_id' => 10000));
```

7. 获取上一条执行过的sql
```
$db->getLastQuery ();
```

8. 获取错误信息
```
$db->lastErrorNo();
$db->lastError();
```

9. 统计数量
```
$num = $db->selectCount('a', array('a_age > 10')); 

// select count(1) as num from a where a_age > 10;
```



