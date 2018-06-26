
# nake_api_protect
 
 `接口请求保护器`，根据 **频率** + **次数** 的自由组合，来限制 api 受到的恶意请求。
 
####  一、参数

| params    | 取值           | 默认值 |  备注 | 
| :------------: |:-------------:|:-------------:|:-------------:|
| project_name      | [string] | "default" |  | 
| identity      | "session" / "ip" | "session" | "ip" 则用 redis 做持久化 | 
| frequency     |  见下表   |   无限制 |   | 
| redis   |   见下表  |     |  当 identity="ip" 时必填 | 

`frequency`：

| frequency    | 取值类型           | 单位 |  默认值 | 备注 | 
| :------------: |:-------------:|:-------------:|:-------------:|:-------------:|
| during      | [integer] | 秒 | 0 | >=0 | 
| times     |   [integer]  | 次数 | 0 | >=0 | 

> 注：若 during 和 times 同时为 0，则视为无限制。

`redis`：

若 identity = ip，则需要 redis 参数

| params    | 取值类型           | 参考值 | 
| :------------: |:-------------:|:-------------:| 
| address      | [string]] |   "127.0.0.1" |   
| port     |  [integer]  |  6379 |   

####  二、方法

| function    | 功能          | 返回值 | 
| :------------: |:-------------:|:-------------:| 
| active     | 记录接口请求 | void | 
| valid      | 判断此次请求是否安全 | true／false | 
| debug      | 输出请求记录详情，方便 debug | array | 
| clear     |  清除接口请求记录的次数  |  void  | 
| destory  |   销毁接口请求保护器  |   void  | 

####  三、ERROR

| error    | 原因          |
| :------------: |:-------------:|
| InvalidArgumentException     | 不合法的参数 |
| RuntimeException      | 运行时错误 |

####  四、调用

```php
//init nake_api_protect
$nake_api_protect_options = array(
    'project_name' => 'mobile_project',
    'identity' => 'ip',
    'frequency' =>
    [
        array("during" => 1 * 60, 'times' => 3),
    ],
    'redis' => [
        "address" => "127.0.0.1",
        "port" => 6379,
    ],
);

try {
    $nake_api_protect = new Nake_api_protect($nake_api_protect_options); //创建实例对象

    //use
    if (!$nake_api_protect->valid()) {
        echo var_dump($nake_api_protect->debug());
        echo "Your request is too frequent.";
        return;
    }
    $nake_api_protect->active();
    //……

} catch (InvalidArgumentException $e) {
    throw new InvalidArgumentException($e);
} catch (RuntimeException $e) {
    throw new RuntimeException($e);
} catch (Exception $e) {
    throw new Exception($e);
} 

```



####  四、实例 - (获取手机号）

##### 方案1:

1、每个用户每分钟只能获取一次

```
'frequency' => 
[
    array("during" => 60, 'times' => 1), 
]
```


##### 方案2:

1、每个用户每分钟只能获取一次

2、每个用户每小时只能获取三次

```
'frequency' => 
[
    array("during" => 60, 'times' => 1), 
    array("during" => 60 * 60, 'times' => 3), 
]
```

 
 