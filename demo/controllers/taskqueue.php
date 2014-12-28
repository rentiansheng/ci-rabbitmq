<?php
/**
这是一个命令行的程序，可以通过在ci的根目录下

php ./index.php taskqueue start 

来启动

*/
class TaskQueue extends MY_Controller 
{
    function declareQueue()
    {
        $this->load->library("rabbitmq");
        $this->rabbitmq->queue("test", true);
    }

    function sendMsg()
    {
        $this->load->library("rabbitmq");
        $this->rabbitmq->sendMsg("{test}", "PHPDEFAULT", "test", true);

    }


    function getMsg() 
    {
        $this->load->library("rabbitmq");
        $msg =  $this->rabbitmq->getMsg("test", false);
        echo "msg\n{$msg}\n";
        $this->rabbitmq->ack();
    }


    function consume() 
    {
        $this->load->library("rabbitmq");
        $this->rabbitmq->consume("test", "test", false);
    }

}
