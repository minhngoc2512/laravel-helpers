<?php

namespace Ngocnm\LaravelHelpers\jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageToSlack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $payload;
    public $type;

    public function __construct($payload, $type = 'error')
    {
        $this->payload = $payload;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $url_slack = config('helper.jobs.slack.slack_error_url');
            if ($this->type == 'log') {
                $url_slack = config('helper.jobs.slack.slack_log_url');
            }
            if (empty($url_slack)) {
                Log::error("Not found url slack in job SendMessageToSlack");
                return;
            }
            if (is_array($this->payload) || is_object($this->payload)) {
                $this->payload = json_encode($this->payload);
            }
            if (empty($this->payload)) {
                return;
            }
            $client = new Client();
            $data = [
                "type" => "mrkdwn",
                'text' => $this->payload
            ];
            $res = $client->post($url_slack, [
                'json' => $data
            ]);
            if ($res->getStatusCode() !== 200) {
                throw new \Exception($res->getReasonPhrase());
            }
        } catch (\Exception $e) {
            Log::error("Send message slack error: {$e->getMessage()}");
            print_r($e->getMessage());
        }
    }
}