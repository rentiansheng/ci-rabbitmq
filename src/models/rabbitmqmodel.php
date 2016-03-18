<?php
/**
* @file rabbitmqmodel.php
* @Synopsis  
* rabbitmq consume回调函数类
* @author Reage(rentiansheng@163.com)
* @version 1.0.0
* @date 2014-12-28
 */
class rabbitmqModel extends MY_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function test($envelope, $queue)
    {
        echo ("\nbody\n".$envelope->getBody());
        $queue->ack($envelope->getDeliveryTag());
    }

}
