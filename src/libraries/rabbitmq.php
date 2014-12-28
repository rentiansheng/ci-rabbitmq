/**
* @file rabbitmq.php
* @Synopsis  
*   rabbitmq 功能函数
* @author Reage(rentiansheng@163.com)
* @version 1.0.0
* @date 2014-12-28
 */
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rabbitmq 
{
    protected $_config = array('host' => '127.0.0.1', 'port' => '5672', 'vhost' => '/', 'login' => 'guest', 'password' => 'guest');
    protected $con= null;
    protected $channel = null;
    protected $exchange = null;
    protected $queue = null;
    private $deliveryTag = 0;
    protected $obj = null;
   
    function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->config("rabbitmq");
        $config = $this->ci->config->item("rabbitmq")["default"];
        $this->_initialize($config);
        @$this->ci->load->model("rabbitmqModel");
        $this->obj = $this->ci->rabbitmqModel;

    }


    protected function  _initialize($config)
    {
        if(!empty($config)) {
            $this->_config = $config;
        } 
        try {
            $this->con= new AMQPConnection($this->_config);
            $this->con->connect();
            $this->con->setReadTimeout(0);//AMQPConnectionException' with message '(unknown error)
            $this->channel = new AMQPChannel($this->con);
        }catch(AMQPConnectionException $e) {
            $this->channel = null;
            return NULL;
        }
        
    }
    
    function exchange($name = "", $durable = true, $exchangeType = AMQP_EX_TYPE_FANOUT)
    {
        try{
            $this->exchange = new AMQPExchange($this->channel);
            $this->exchange->setName($name);
            if( $durable == true ) {
                $this->exchange->durable = true;
                $this->exchange->setFlags(AMQP_DURABLE);
            } else {
                $this->exchange->durable = false;
            }
            $this->exchange->setType($exchangeType);
            $this->exchange->declareExchange();
        }catch (AMQPConnectionException $e) {
            $this->exchange = null;
            return false;
        }

        return true;
    }

    function queue($name="", $durable = true)
    {
        try {
            $this->queue = new AMQPQueue($this->channel);
            $this->queue->setName($name);
            if($durable == true) {
                $this->queue->durable = true;
                $this->queue->auto_delete = false;
                $this->queue->setFlags(AMQP_DURABLE);
            } else {
                $this->queue->durable = false;
            } 

            $this->queue->declareQueue();

        } catch (AMQPConnectionException $e) {

            $this->queue = null;
            return false;
        }

        return true;
    }

    function bind($exchangeName = "" ) 
    {
        try {
            $this->queue->bind($exchangeName, $this->queue->getName());
        } catch (AMQPConnectionException $e) {
            return false;
        }

        return true;

    }
    
    function send($message = '')
    {
        try {
            $para["delivery_mode"] = 2;
            $this->exchange->publish($message, $this->queue->getName(), AMQP_MANDATORY, $para);
        } catch (AMQPConnectionException $e) {
            return false;
        }

        return true;
    }
    
    function get($autoack = true)
    {
        try {
            $msg = "";
            if($autoack) {
                $msg = $this->queue->get(AMQP_AUTOACK); 
            } else {
                $msg = $this->queue->get(); 
            }
            if(empty($msg)) { return "";}
            $this->deliveryTag = $msg->getDeliveryTag();
           return $msg->getBody();
        } catch (AMQPConnectionException $e) {
            $this->deliveryTag = 0;
            return "";
        }
    }

    function ack() 
    {
        try {
            $this->queue->ack($this->deliveryTag);
        } catch (AMQPConnectionException $e) {
            $this->deliveryTag = 0;
            return false;
        }
        
        return true;
    }

    function nack() 
    {
        try {
            $this->queue->nack($this->deliveryTag);
        } catch (AMQPConnectionException $e) {
            $this->deliveryTag = 0;
            return false;
        }
        
        return true;
    }

    function getMsg($queueName = '', $autoack = true, $durable = true) 
    {
        $this->queue($queueName, $durable);
        return $this->get($autoack);
    }

    function sendMsg($msg = '', $exchangeName = '', $queueName = '', $durable = true, $exchangeType = AMQP_EX_TYPE_DIRECT ) 
    {
        $this->exchange($exchangeName, $durable, $exchangeType);
        $this->queue($queueName);
        $this->bind($exchangeName);

        $this->send($msg);
        // $this->connection->disconnect();
    }

    function consume($queueName="", $callback = "", $autoack = true, $durable = true)
    {
        $this->queue($queueName, $durable);
        $callback = array($this->obj, $callback);
        if($autoack) {
            $this->queue->consume($callback, AMQP_AUTOACK);
        } else {
            $this->queue->consume($callback);
        }
    }


}

