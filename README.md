
# nake_api_protect
 
 `接口请求保护器`，根据频次+次数的自由组合，来限制api的恶意请求。
 
####  参数

| params    | 取值           | 默认值 |  备注 | 
| :------------: |:-------------:|:-------------:|:-------------:|
| project_name      | [string] | default |  | 
| identity      | session / ip | session | ip用redis做持久化 | 
| frequency     |  见下表   |   无限制 |   | 
| redis   |   见下表  |     |  可选项 | 

`frequency`：

| frequency    | 取值类型           | 单位 |  默认值 | 备注 | 
| :------------: |:-------------:|:-------------:|:-------------:|:-------------:|
| during      | 整数 | 秒 | 0 | >0，-1为无限 | 
| times     |   整数  | 次数 | 0 | >0，-1为无限 | 

`redis`：
若 identity = ip，则需要redis

| params    | 参考值 | 
| :------------: |:-------------:| 
| address      | 127.0.0.1 |   
| port     |  6379  |  

####  调用

```
$options = array(
    'identity' => 'session',
    'frequency' =>
        [
            array("during" => 60, 'times' => 1),
            array("during" => 60 * 60, 'times' => 3),
        ],
    'redis' => array("address" => '127.0.0.1', 'port' => 6379),
);
$nake_api_protect = new Nake_api_protect($options); //创建实例对象
$nake_api_protect->active();
```

####  方法

| function    | 功能          | 返回值 | 
| :------------: |:-------------:|:-------------:| 
| active     | 记录接口请求 | void | 
| valid      | 判断此次请求是否安全 | true／false | 
| clear     |  清除以往接口请求的记录  |  void  | 
| destory  |   销毁接口请求保护器  |   void  | 


####  实例

###### 1、获取手机号

方案：

1、每个用户每分钟只能获取一次

'frequency' => 
[
    array("during" => 60, 'times' => 1), 
]


###### 2、获取手机号

方案：

1、每个用户每分钟只能获取一次
2、每个用户每小时只能获取三次

'frequency' => 
[
    array("during" => 60, 'times' => 1), 
    array("during" => 60 * 60, 'times' => 3), 
]


 
 