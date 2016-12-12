<?php
/**
 * Created by PhpStorm.
 * User: andriy
 * Date: 12.12.16
 * Time: 20:44
 */
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/../vendor/autoload.php';

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('report', false, false, false, false);

$msg = new AMQPMessage("{\"id\": \"test\", \"user_id\": \"1\"}");
$channel->basic_publish($msg, '', 'report');

echo " [x] Sent {$msg->body}\n";

$channel->close();
$connection->close();