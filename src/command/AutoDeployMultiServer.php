<?php
namespace Ngocnm\LaravelHelpers\command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class AutoDeployMultiServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helper:deploy-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto pull code and deploy app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Run deploy ?')) {
            $servers = config('helper.deploy.servers');
            $commands = config('helper.deploy.commands');
            foreach ($servers as $server){
                $this->info("===== ".date("H:i d/m/Y"));
                $this->info("Deploy server {$server['name']} : {$server['ip']}");
                if($server['enable']){
                    foreach ($commands as $key => $command){
                        $this->info("$key . Run : {$command['description']} ");
                        if($command['enable']){
                            if(isset($command['ask'])&&$command['ask']&&!$this->confirm('Want run?')){
                                $this->warn("Canceled!");
                                continue;
                            }
                            $command_run = $this->generateCommand($server,$command);
                            $this->info("Running command : $command_run");
                            $result = Process::timeout(300)->run($command_run, function (string $type, string $output) {
                                $this->info($output);
                            });
                            if($result->successful()){
                                $this->info("Run success");
                            }else{
                                $this->error("Run fail!");
                            }
                        }else{
                            $this->warn("Command disabled!");
                        }
                    }
                }else{
                    $this->warn("Server disabled!");
                }
            }
        } else {
            $this->warn("Canceled!");
        }
//        $result = Process::timeout(300)->run('ssh eztech@207.246.80.62 cd /var/www/html/ezedit/api/master/ && composer update --no-dev', function (string $type, string $output) {
//            $this->info($output);
//        });
//        $this->info($result->successful());

    }

    function generateCommand(array $server,array $command): string
    {
        $command_return = [];
        $connect =  "ssh {$server['user_name']}@{$server['ip']}";

        //Thay đổi user ssh
        if(!empty($command['user'])) $connect = "ssh {$command['user']}@{$server['ip']}";

        //Kiểm tra thư mục chạy ssh
        if(!empty($command['folder'])) $command_return[] = "cd {$command['folder']}";

        $command_return = array_merge($command_return,$command['cmd']);

        return  $connect." \" ".implode(' && ',$command_return)." \"";
    }
}
