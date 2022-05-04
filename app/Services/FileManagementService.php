<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class FileManagementService
{
    const FILE_EXTENSION = 'mp3';
    const EXPIRY_IN_MINUTES = 10;
    const UPLOAD_DIRECTORY_PATH = 'uploads/';
    const TRIMMED_DIRECTORY_PATH = 'trimmed/';

    public function getPresignedUrl(): string
    {
        $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => Config::get('filesystems.disks.s3.bucket'),
            'Key' => $this->generateUploadFilePath,
            'ACL' => 'public-read',
        ]);

        $client->createPresignedRequest($cmd, "+" . self::EXPIRY_IN_MINUTES . " minutes");

        return (string)$client
            ->createPresignedRequest($cmd, "+" . self::EXPIRY_IN_MINUTES . " minutes")
            ->getUri();
    }

    public function getList(): array
    {
        $s3 = Storage::disk('s3');

        return array_map(function ($filePath) use ($s3) {
            return $s3->temporaryUrl($filePath, Carbon::now()->addMinutes(self::EXPIRY_IN_MINUTES));
        }, $s3->allFiles(self::UPLOAD_DIRECTORY_PATH));
    }

    protected function generateUploadFilePath(): string
    {
        return self::UPLOAD_DIRECTORY_PATH . Carbon::now()->timestamp . '.' . self::FILE_EXTENSION;
    }
}
