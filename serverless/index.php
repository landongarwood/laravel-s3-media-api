<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Aws\Credentials\Credentials;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\Exception\AwsException;

return function ($event) {
    if (empty($original_file = $event['Records'][0]['s3']['object']['key'] ?? null)) {
        return;
    }

    $transcoder_client = new ElasticTranscoderClient([
        'version' => 'latest',
        'region' => getenv('ET_AWS_DEFAULT_REGION'),
        'credentials' => new Credentials(getenv('ET_AWS_ACCESS_KEY_ID'), getenv('ET_AWS_SECRET_ACCESS_KEY')),
    ]);

    $outputs = [[
        'Key' => end(explode('/', $original_file)),
        'PresetId' => getenv('PRESET_ID'),
    ]];

    try {
        $transcoder_client->createJob([
            'PipelineId' => getenv('PIPELINE_ID'),
            'Input' => [
                'Key' => $original_file,
                'TimeSpan'=> ['Duration' => '00:00:05.0'],
            ],
            'Outputs' => $outputs,
            'OutputKeyPrefix' => 'trimmed/',
        ]);
    } catch (AwsException $e) {
        echo $e->getMessage() . "\n";
    }
};
