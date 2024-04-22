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
            $command = $this->generateCommand($config);
            $this->info("Running command : $command");
            $result = Process::timeout(300)->run($command, function (string $type, string $output) {
                $this->info($output);
            });
            if ($result->successful()) {
                $this->info("Run success");
            } else {
                $this->error("Run fail!");
            }

//            $file_size = filesize($config['database']['folder'] . "/" . $config['database']['file_name']) / 1000000;
            $folder = (string)time();
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
            $command_sip = $config['zip_path'] . " -r " . $path_zip . " " . $config['database']['folder'] . "/" . $config['database']['file_name'];
            $result_zip = Process::timeout(300)->run($command_sip, function (string $type, string $output) {
                $this->info($output);
            });
            $path_storage = "backups/" . $folder . '/' . $config['database']['file_name'] . ".zip";
            if ($result_zip->successful()) {
                $this->info("Run success");
            } else {
                $this->error("Run fail!");
            }
            $file_size = filesize($path_zip) / 1000000;

            if ($file_size <= 100) {
                $ok = Storage::disk($config['backup_driver'])->put($path_storage, file_get_contents($path_zip));
                if (!$ok) {
                    $this->error("Upload to storage fail!");
                }
                $backup = new BackupFile();
                $backup->path = [
                    'number_file' => 1,
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
                $command_create_folder = "mkdir " . $config['database']['folder'] . "/" . $folder . "/split";
                $result_create_folder = Process::timeout(300)->run($command_create_folder, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_create_folder->successful()) {
                    $this->info("Run create folder success");
                } else {
                    $this->error("Run create folder fail!");
                }

                $command_split = $config['zip_path'] . " -s 100m " . $path_zip . " --out " . $config['database']['folder'] . "/" . $folder . "/split/" . $config['database']['file_name'] . ".zip";
                $result_split = Process::timeout(300)->run($command_split, function (string $type, string $output) {
                    $this->info($output);
                });
                if ($result_split->successful()) {
                    $this->info("Run split file success");
                } else {
                    $this->error("Run split file fail!");
                }

                $files = File::allFiles($config['database']['folder'] . "/" . $folder . '/split');
                foreach ($files as $file) {
                    $path_storage = "backups/" . $folder . '/split/' . $file->getFilename();
                    $ok = Storage::disk($config['backup_driver'])->put($path_storage, file_get_contents($file));
                    if (!$ok) {
                        $this->error("Upload to storage fail!");
                    }
                }
                $backup = new BackupFile();
                $backup->path = [
                    'number_file' => 'multiple',
                    'path_zip' => $config['database']['folder'] . "/" . $folder . '/split',
                    'path_storage' => "backups/" . $folder . '/split',
                ];
                $backup->status = 1;
                $backup->save();
                $this->info('Backup save DB file more than 100MB success!');
                $this->info('Path storage: ' . "backups/" . $folder . '/split');
                $this->info('Path zip: ' .  $config['database']['folder'] . "/" . $folder . '/split');
            }

            $number_backups = $config['number_of_backup'];
            $backups = BackupFile::orderBy('created_at', 'desc')->get();
            $i = 1;
            foreach ($backups as $backup) {
                $i += 1;
                if ($i > $number_backups){
                    $path_zip = $backup->path['path_zip'];
                    if ($backup->path['number_file'] == 'multiple'){
                        $command_remove = "rm -r -f " . $path_zip;
                    }elseif ($backup->path['number_file'] == 1){
                        $command_remove = "rm " . $path_zip;
                    }
                    $result_remove_file = Process::timeout(300)->run($command_remove, function (string $type, string $output) {
                        $this->info($output);
                    });
                    if ($result_remove_file->successful()) {
                        $this->info("Run remove file success");
                    } else {
                        $this->error("Run remove file fail!");
                    }
                    $backup->delete();
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
        foreach ($config['database']['tables'] as $table) {
            $tables .= " $table";
        }
        return $config['mysqldump_path'] . " --defaults-extra-file={$config['database']['file_config']} {$config['database']['database_name']} {$tables} > {$config['database']['folder']}/{$config['database']['file_name']}";
    }

}
