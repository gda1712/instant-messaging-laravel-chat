<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateRsaKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:rsa-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA keys for encryption and decryption in chat messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // save in keys disk
//        $filePath = Storage::disk('disks')->putFileAs(
//            '',
//            $uploadedFile,
//            $timestampedFilename
//        );
    }
}
