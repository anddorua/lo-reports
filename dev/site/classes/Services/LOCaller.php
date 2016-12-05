<?php
namespace App\Services;
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 04.12.16
 * Time: 18:50
 */
class LOCaller
{
    const max_utime = 2000000;
    const wait_quant = 50000;

    private $reportDir;
    private $dataDir;
    private $dstDir;
    private $cwd;

    public function __construct($reportDir)
    {
        $this->reportDir = $reportDir;
    }

    /**
     * @return mixed
     */
    public function getCwd()
    {
        return $this->cwd;
    }


    public function callMacro($macroName)
    {
        $exec_out = [];
        $exec_return_code = 0;
        exec(
            'soffice --invisible --nodefault --norestore "macro:///Standard.Module1.starter()"'
            , $exec_out
            , $exec_return_code
        );
        $res = new \StdClass();
        $res->out = implode('<br>',$exec_out);
        $res->code = $exec_return_code;
        return $res;
    }

    public function startMacro($macroName)
    {
        $descriptorspec = array(
            // 0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
            1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать
            2 => array("file", "/tmp/error-output.txt", "a") // stderr - файл для записи
        );

        $cwd = '/tmp';

        $process = proc_open('exec soffice --invisible --nodefault --norestore "macro:///Standard.Module1.starter()"', $descriptorspec, $pipes, $cwd, null);

        $res = new \StdClass();
        $res->out = 'no result';
        $res->code = 1000;
        $res->error = '';
        if (is_resource($process)) {
            // $pipes теперь выглядит так:
            // 0 => записывающий обработчик, подключенный к дочернему stdin
            // 1 => читающий обработчик, подключенный к дочернему stdout
            // Вывод сообщений об ошибках будет добавляться в /tmp/error-output.txt

/*            fwrite($pipes[0], '<?php print_r($_ENV); ?>');
            fclose($pipes[0]);*/

            $res->out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Важно закрывать все каналы перед вызовом
            // proc_close во избежание мертвой блокировки
            $return_value = proc_close($process);

            $res->code = $return_value;
        } else {
            $res->error = 'process did not started';
        }
        return $res;
    }

    private function createDirs()
    {
        $this->cwd = sys_get_temp_dir() . '/' . uniqid('lo_');
        $this->dataDir = $this->cwd . '/data';
        $this->dstDir = $this->cwd . '/dst';
        mkdir($this->cwd, 0755);
        mkdir($this->dataDir, 0755);
        mkdir($this->dstDir, 0755);
        return $this->cwd;
    }

    public function removeDirs()
    {
        if (isset($this->dataDir)) {
            array_map('unlink', glob($this->dataDir . "/*"));
            rmdir($this->dataDir);
        }
        if (isset($this->dstDir)) {
            array_map('unlink', glob($this->dstDir . "/*"));
            rmdir($this->dstDir);
        }
        if (isset($this->cwd)) {
            rmdir($this->cwd);
        }
    }

    public function startMacro2($reportName)
    {
        $descriptorspec = array(
            // 0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
            1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать
            2 => array("file", "/tmp/error-output.txt", "a") // stderr - файл для записи
        );

        $this->cwd = $this->createDirs();

        $process = proc_open('exec soffice --invisible --nodefault --norestore "macro:///Standard.Module1.starter(\"' . $reportName .'\", \"' . $this->reportDir . '\", \"' . $this->dataDir . '\", \"' . $this->dstDir . '\")"', $descriptorspec, $pipes, $this->cwd, null);

        $res = new \StdClass();
        $res->out = '';
        $res->code = 1000;
        $res->error = '';
        if (is_resource($process)) {
            // $pipes теперь выглядит так:
            // 0 => записывающий обработчик, подключенный к дочернему stdin
            // 1 => читающий обработчик, подключенный к дочернему stdout
            // Вывод сообщений об ошибках будет добавляться в /tmp/error-output.txt

            stream_set_blocking($pipes[1], false);
            $time_passed = 0;
            $wait_for_terminate = false;
            $status = null;
            do {
                $res->out .= stream_get_contents($pipes[1]);
                $status = proc_get_status($process);
                if ($status['running'] === false) {
                    break;
                } else {
                    if ($time_passed >= self::max_utime && !$wait_for_terminate) {
                        proc_terminate($process);
                        $wait_for_terminate = true;
                        $res->error = 'forced termination';
                    }
                    usleep(self::wait_quant);
                    $time_passed += self::wait_quant;
                }
            } while (true);

            $res->out .= stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Важно закрывать все каналы перед вызовом
            // proc_close во избежание мертвой блокировки
            proc_close($process);

            $res->code = $status['exitcode'];
        } else {
            $res->error = 'process did not started';
        }

        return $res;
    }

}