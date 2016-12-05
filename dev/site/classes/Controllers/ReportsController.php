<?php

namespace App\Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
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
/*        $controllers->get('/{id}', [$this, 'getSingleUnified'])->bind('author')->assert('id', '\d+');
        $controllers->post('/', [$this, 'postEntityUnified']);
        $controllers->put('/{id}', [$this, 'putEntityUnified'])->assert('id', '\d+');
        $controllers->delete('/{id}', [$this, 'deleteSingleUnified'])->assert('id', '\d+');*/
        return $controllers;
    }

    public function getIndex(Application $app)
    {
        //$msg = $app['lo_caller']->callMacro("dummy");
        $msg = $app['lo_caller']->startMacro2("dummy");
        $processUser = posix_getpwuid(posix_geteuid());
        $app['lo_caller']->removeDirs();
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