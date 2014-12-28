codeigniter rabbitmq library
===============

代码结构
---------------------
1. 文件介绍
```
    1. src/src/libraries/rabbitmq.php  rabbitmq操作代码
    2. src/src/config/rabbitmq.php     rabbitmq配置文件
    3. src/src/models/rabbitmqmodel.php   consume回调函数代码
```

2. 将需要的PHP代码文件放到制定位置

```
1. cp src/src/libraries/rabbitmq.php   项目代码/application/libraries/
2. cp src/src/config/rabbitmq.php   项目代码/application/config/
3. cp src/src/models/rabbitmqmodel.php   项目代码/application/models/
```

函数
---------------------

1. queue  declare一个queue
```
   参数
       name 队列的名字
       durable queue是否持久化，true，是，false 否 
```

2. sendMsg 向队列中add内容
```
    参数
        msg 消息的内容
        exchangeName 发送消息使用的exchangeName
        queueName 接受消息的queue
        durable   queueName队列是否持久话，要与declare queue保持一直，负责回出错的
        exchangeType exchage type 我一般用直接写入，
                      你可以选择一下模式， AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, 
                      AMQP_EX_TYPE_HEADER or AMQP_EX_TYPE_TOPIC
```

3. getMsg 获取消息
```
   参数 
        queueName 获取内容的queue的name
        autoack   是否自动ack，autoack ＝ true，消息将从队列删除，
                  autoack ＝ false；时，需要用ack或者nack来回应给rabbitmq，否则，队列将无法工作
        durable   queueName队列是否持久话，要与declare queue保持一直，负责回出错的
    备注
        从队列中的下一个可用的消息。如果没有消息存在于队列中，该函数将立即返回FALSE，
        这种方式比较消耗CPU,不建议使用的。
```

3. consume 获取消息，推荐
```
    参数
        queueName 获取内容的queue的name
        callback  回调函数的名字，注意这个是函数名字的，对应名字的函数必须在models/rabbitmqmodel.php中实现的
        autoack   是否自动ack，autoack ＝ true，消息将从队列删除，
                  autoack ＝ false；时，需要用ack或者nack来回应给rabbitmq，否则，队列将无法工作
        durable   queueName队列是否持久话，要与declare queue保持一直，负责回出错的
    备注
        callback回调函数的名字，注意这个是函数名字的，对应名字的函数必须在models/rabbitmqmodel.php中实现的
        回调函数将会有两个参数，
```

4. consume回调函数格式
```  

   function test(envelope, $queue) {}
    参数
       envelope  与消息相关的对象，具体的查看<a href="http://php.net/manual/pl/class.amqpenvelope.php">http://php.net/manual/pl/class.amqpenvelope.php</a>
       queue  queue的对象
```

demo 目录下是示例代码
-----------------------
```
1. 将demo/controllers/taskqueue.php 项目代码/application/controllers/taskqueue.php
2.  运行例子，最好可以terminal运行,首先转到项目文件所在的路径
    已发送消息作为例子
    sudo [可选择加上php env]  php所在位置 index.php taskqueue   sendMsg 
```

我遇到的问题
----------------------
1. 在执行一段时间后无法从redis获取内容
```
    我使用的是ci的cache库操作redis，由于cache 初始化是在controller开始的位置，时间久了，会自动断开链接请注意。
```
2. 请不要在浏览器中运行consume， php-fpm有可会出现问题，

