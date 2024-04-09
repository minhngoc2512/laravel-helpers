<?php

namespace Ngocnm\LaravelHelpers\command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Ngocnm\LaravelHelpers\models\BackupFile;
use Illuminate\Support\Facades\File;


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
//        if ($this->confirm('Run Backup Database?')) {
        $config = config('helper.backup');
        if ($config['enable']) {
            $this->info("===== " . date("H:i d/m/Y"));
            $this->info("Backup Database: {$config['database']['database_name']}");
//                if ($config['ask']) {
//                    if (!$this->confirm('Want run?')) {
//                        $this->warn("Canceled!");
//                        return;
//                    }
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

            $file_size = filesize($config['database']['folder'] . "/" . $config['database']['file_name']) / 1000000;
            $folder = (string)time();

            if ($file_size <= 100) {
                $command_create_folder = "mkdir " . $config['database']['folder'] . '/' . $folder;
                $result_create_folder = Process::timeout(300)->run($command_create_folder, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_create_folder->successful()) {
                    $this->info("Run create folder success");
                } else {
                    $this->error("Run create folder fail!");
                }

                $path_zip = $config['database']['folder'] . "/" . $folder . '/' . $config['database']['file_name'] . ".zip";
                $command_sip = "zip -r " . $path_zip . " " . $config['database']['folder'] . "/" . $config['database']['file_name'];
                $result_zip = Process::timeout(300)->run($command_sip, function (string $type, string $output) {
                    $this->info($output);
                });
                $path_storage = "backups/" . $folder . '/' . $config['database']['file_name'] . ".zip";
                if ($result_zip->successful()) {
                    $this->info("Run success");
                } else {
                    $this->error("Run fail!");
                }
                $ok = Storage::disk($config['backup_driver'])->put($path_storage, file_get_contents($path_zip));
                if (!$ok) {
                    $this->error("Upload to storage fail!");
                }
                $backup = new BackupFile();
                $backup->path = [
                    'path_zip' => $path_zip,
                    'path_storage' => $path_storage,
                ];
                $backup->status = 1;
                $backup->save();
                $this->info('Backup save DB success!');
                $this->info('Path storage: ' . $path_storage);
                $this->info('Path zip: ' . $path_zip);
            }
            if ($file_size > 100) {
                $this->info("File size is too large, please check it!");
                $path_zip = $config['database']['folder'] . "/" . $folder . '/' . $config['database']['file_name'] . ".zip";
                $command_sip = "zip -r " . $path_zip . " " . $config['database']['folder'] . "/" . $config['database']['file_name'];
                $result_zip = Process::timeout(300)->run($command_sip, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_zip->successful()) {
                    $this->info("Run zip file success");
                } else {
                    $this->error("Run zip file fail!");
                }

                $folder = (string)time();
                $command_create_folder = "mkdir " . $config['database']['folder'] . "/backups/" . $folder;
                $result_create_folder = Process::timeout(300)->run($command_create_folder, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_create_folder->successful()) {
                    $this->info("Run create folder success");
                } else {
                    $this->error("Run create folder fail!");
                }

                $command_split = "zip -s 100m " . $path_zip . " --out " . $config['database']['folder'] . "/" . $folder . "/" . $config['database']['file_name'] . ".zip";
                $result_split = Process::timeout(300)->run($command_split, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_split->successful()) {
                    $this->info("Run split file success");
                } else {
                    $this->error("Run split file fail!");
                }

                $files = File::allFiles($config['database']['folder'] . "/" . $folder);
                foreach ($files as $file) {
                    $path_storage = "backups/" . $folder . '/' . $file->getFilename();
                    $ok = Storage::disk($config['backup_driver'])->put($path_storage, file_get_contents($file));
                    if (!$ok) {
                        $this->error("Upload to storage fail!");
                    }
                    $backup = new BackupFile();
                    $backup->path = [
                        'path_zip' => $file->getPathname(),
                        'path_storage' => $path_storage,
                    ];
                    $backup->status = 1;
                    $backup->save();
                    $this->info('Backup save DB file more than 100MB success!');
                    $this->info('Path storage: ' . $path_storage);
                    $this->info('Path zip: ' . $file->getPathname());
                }
            }
        } else {
            $this->warn("Backup disabled!");
        }
    }
//        } else {
//            $this->warn("Canceled!");
//        }
//    }

//    }

    private
    function generateCommand($config): string
    {
        $tables = "";
        foreach ($config['tables'] as $table) {
            $tables .= " $table";
        }
        return "mysqldump --defaults-extra-file={$config['file_config']} {$config['database_name']} {$tables} > {$config['folder']}/{$config['file_name']}";
    }

}
