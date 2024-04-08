<?php

namespace Ngocnm\LaravelHelpers\command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Ngocnm\LaravelHelpers\models\BackupFile;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helper:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto backup database';

    public function handle()
    {
        if ($this->ask('Run Backup Database?')) {
            $config = config('helper.backup');
            if ($config['enable']) {
                $this->info("===== " . date("H:i d/m/Y"));
                $this->info("Backup Database: {$config['database']['database']}");
                if ($config['ask']) {
                    if (!$this->confirm('Want run?')) {
                        $this->warn("Canceled!");
                        return;
                    }
                    $command = $this->generateCommand($config['database']);
                    $this->info("Running command : $command");
                    $result = Process::timeout(300)->run($command, function (string $type, string $output) {
                        $this->info($output);
                    });
                    if ($result->successful()) {
                        $this->info("Run success");
                    } else {
                        $this->error("Run fail!");
                    }

                    $file_size = filesize($config['folder'] . "/" . $config['file_name']) / 1000000;
                    if ($file_size <= 100) {
                        $path = date('/backups/Y/m/d', time()) . '/' . $config['file_name'];
                        $ok = Storage::put($path, file_get_contents($config['folder'] . "/" . $config['file_name']));
                        if (!$ok){
                            $this->error("Upload to storage fail!");
                        }
                        $backup = new BackupFile();
                        $backup->path = $path;
                        $backup->status = 1;
                        $backup->save();
                        $this->info('Backup save DB success!');
                    }
                    if ($file_size > 100) {
                        $this->info("File size is too large, please check it!");

                    }
                }
            } else {
                $this->warn("Backup disabled!");
            }
        } else {
            $this->warn("Canceled!");
        }
    }

    private function generateCommand($config): string
    {
        $tables = "";
        foreach ($config['database']['tables'] as $table) {
            $tables .= " $table";
        }
        return "mysqldump --defaults-extra-file={$config['file_config']} {$config['database_name']} {$tables} > {$config['folder']}/{$config['file_name']}";
    }

}