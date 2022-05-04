<?php

namespace App\Http\Controllers;

use App\Services\FileManagementService;

class SongsController extends Controller
{
    protected $fileManagementService;

    public function __construct(FileManagementService $fileManagementService)
    {
        parent::__construct();

        $this->fileManagementService = $fileManagementService;
    }

    public function index()
    {
        $urls = $this->fileManagementService->getList();

        return response()->json(['urls' => $urls], 200);
    }

    public function getUploadUrl()
    {
        $presignedUrl = $this->fileManagementService->getPresignedUrl();

        return response()->json(['url' => $presignedUrl], 201);
    }
}
