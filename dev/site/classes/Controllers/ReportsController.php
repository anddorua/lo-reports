<?php

namespace App\Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $progress = [];
        if (!isset($app['reports.config'][$id])) {
            throw new NotFoundHttpException("Report '$id' not found.");
        }
        $config = $app['reports.config'][$id];
        /* @var $dataProvider \App\ReportDataProviders\ReportDataProviderInterface */
        $dataProvider = new $config['provider']();
        $data = $dataProvider->getData();

        /* @var $app['lo_caller'] \App\Services\LOCaller */
        $msg = $app['lo_caller']->startReport(
            "/home/application/reports/report1.ods",
            "TestReport.xls",
            "/home/application/reports",
            $data,
            function ($line) use (&$progress) {
                $progress[] = $line;
            });
        $processUser = posix_getpwuid(posix_geteuid());
        $app['lo_caller']->removeDirs();
        return $app['twig']->render('reports/index.html.twig', ['reqObj' => [
            'Message' => $msg->out,
            'Code' => $msg->code,
            'Error' => $msg->error,
            'User' => $processUser['name'],
            'Cwd' => $app['lo_caller']->getCwd(),
            'Perm' => substr(sprintf('%o', fileperms(sys_get_temp_dir())), -4),
            'Progress' => print_r($progress, true),
        ]]);
    }

    public function getIndex(Application $app)
    {
        $progress = [];
        // $json = file_get_contents(__DIR__ . '/../../include/test_report_data.json');
        // $data = [ 'test_data' => json_decode($json),  ];


        $row = 1;
        if (($handle = fopen(__DIR__ . '/../../include/test_report_data.csv', "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                echo "<p> $num полей в строке $row: <br /></p>\n";
                $row++;
                for ($c=0; $c < $num; $c++) {
                    echo $data[$c] . "<br />\n";
                }
            }
            fclose($handle);
        } else {
            $data = [];
        }



        $msg = $app['lo_caller']->startReport(
            "/home/application/reports/report1.ods",
            "TestReport.xls",
            "/home/application/reports",
            $data,
            function ($line) use ($progress) {
                $progress[] = $line;
            });
        $processUser = posix_getpwuid(posix_geteuid());
        //$app['lo_caller']->removeDirs();
        return $app['twig']->render('reports/index.html.twig', ['reqObj' => [
            'Message' => $msg->out,
            'Code' => $msg->code,
            'Error' => $msg->error,
            'User' => $processUser['name'],
            'Cwd' => $app['lo_caller']->getCwd(),
            'Perm' => substr(sprintf('%o', fileperms(sys_get_temp_dir())), -4)
        ]]);
    }
}