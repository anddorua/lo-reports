<?php

namespace App\Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Data\Bundle\Reader\JsonBundleReader;

/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 04.12.16
 * Time: 17:14
 */
class ReportsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers  */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', [$this, 'getIndex']);
        $controllers->get('/{id}', [$this, 'getReport'])->assert('id', '[\w\d\_\-]+');
        /*                $controllers->post('/', [$this, 'postEntityUnified']);
                        $controllers->put('/{id}', [$this, 'putEntityUnified'])->assert('id', '\d+');
                        $controllers->delete('/{id}', [$this, 'deleteSingleUnified'])->assert('id', '\d+');*/
        return $controllers;
    }

    public function getReport(Application $app, $id)
    {

        if ($id == 'slow') {
            ini_set('zlib.output_compression', false);
            $stream = function() use ($app) {
                $string_length = 0;
                echo 'Begin test with an ' . $string_length . ' character string...<br />' . "\r\n";

                // For 3 seconds, repeat the string.
                for ($i = 0; $i < 10; $i++) {
                    $string = str_repeat('.', $string_length);
                    echo $string . '<br />' . "\r\n";
                    echo $i . '<br />' . "\r\n";
                    @ob_end_flush();
                    flush();
                    sleep(1);
                }

                echo 'End test.<br />' . "\r\n";
            };

            return $app->stream($stream, 200, [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'no-cache, must-revalidate',
                'X-Accel-Buffering' => 'no',
            ]);
        }


        if (!isset($app['reports.config'][$id])) {
            $app->abort(404, "Report '$id' not found.");
        }
        $config = $app['reports.config'][$id];
        /* @var $dataProvider \App\ReportDataProviders\ReportDataProviderInterface */
        $dataProvider = new $config['provider']();
        $data = $dataProvider->getData();
        ini_set('zlib.output_compression', false);
        $stream = function() use ($app, $data) {

            ini_set('zlib.output_compression', false);

            $progress = [];
            $msg = $app['lo_caller']->startReport(
                "/home/application/reports/report1.ods",
                "TestReport.xls",
                "/home/application/reports",
                $data,
                function ($line) use (&$progress) {
                    echo $line;
                    echo(str_repeat('.', 0));
                    @ob_end_flush();
                    flush();
                    $progress[] = $line;
                });
            $app['lo_caller']->removeDirs();
        };

        return $app->stream($stream, 200, [
            'Content-Type' => 'application/octet-stream',
            //'Content-Type' => 'text/html',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);

/*        return $app['twig']->render('reports/index.html.twig', ['reqObj' => [
            'Message' => $msg->out,
            'Code' => $msg->code,
            'Error' => $msg->error,
            'User' => $processUser['name'],
            'Cwd' => $app['lo_caller']->getCwd(),
            'Perm' => substr(sprintf('%o', fileperms(sys_get_temp_dir())), -4),
            'Progress' => print_r($progress, true),
        ]]);*/
    }
    public function getIndex(Application $app, Request $request)
    {
        $dataToShow = [];
        foreach($app['reports.config'] as $key => $reportEntry) {
            $result = new \stdClass();
            $result->id = $key;
            $result->description = $reportEntry['description'];
            $dataToShow[] = $result;
        }
/*        return new Response($app['jms']->serialize($dataToShow, 'json'), 200, [
           'Content-Type' => $request->getMimeType('json'),
        ]);*/
        return new JsonResponse($dataToShow);
    }
}