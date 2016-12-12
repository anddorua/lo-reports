<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 11.12.16
 * Time: 22:13
 */
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Silex\Provider\MonologServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../include/bootstrap_app.php';

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/worker.log',
));

$connection = new AMQPStreamConnection(
    $app['rabbit.config']['server'],
    $app['rabbit.config']['port'],
    $app['rabbit.config']['login'],
    $app['rabbit.config']['password']);

$channel = $connection->channel();

$channel->queue_declare($app['rabbit.config']['queue'], false, false, false, false);

$app['monolog']->info('Worker ready for messages.');

$callback = function($msg) use($app) {
    $app['monolog']->debug('New task received: ' . $msg->body);
    $task = json_decode($msg->body);

    try {
        // make report
        if (!isset($app['reports.config'][$task->id])) {
            $app['monolog']->error('Report with id=' . $task->id . ' not implemented');
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            return;
        }
        $config = $app['reports.config'][$task->id];
        /* @var $dataProvider \App\ReportDataProviders\ReportDataProviderInterface */
        $dataProvider = new $config['provider']();
        $data = $dataProvider->getData();
        $msg = $app['lo_caller']->startReport(
            $app['reports.path'] . DIRECTORY_SEPARATOR . $app['reports.config'][$task->id]['file'],
            "TestReport.xls",
            "/home/application/reports",
            $data,
            function ($line) use (&$progress, $time_start) {
                $op_time = microtime(true);
                echo 'time since begin:' . ($op_time - $time_start) . $line;
                echo(str_repeat('.', 0));
                @ob_end_flush();
                flush();
                $progress[] = $line;
            });
        $app['lo_caller']->removeDirs();








        // todo
        $result = true;
        if($result) {
            $app['monolog']->info('Result is: ' . 'true');
            // store report
            // todo
            // mark as delivered in RabbitMQ
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        } else {
            $app['monolog']->warning('Failed to decode JSON, will retry later');
        }
    } catch(Exception $e) {
        $app['monolog']->warning('Failed to make report, try later');
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($app['rabbit.config']['queue'], '', false, false, false, false, $callback);

// loop over incoming messages
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();