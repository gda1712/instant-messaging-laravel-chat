<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Services\EncryptionService;
use App\Http\Requests\Encryption\UpdatePublicUserKeyRequest;

class EncryptionController extends BaseController
{
    public function getServerPublicKey(Request $request, EncryptionService $encryptionService)
    {
        return $this->sendResponse(base64_encode($encryptionService->getPublicKey()), 'Public key retrieved successfully.');
    }

    public function updateUserPublicKey(UpdatePublicUserKeyRequest $request, EncryptionService $encryptionService)
    {
        $validated = $request->validated();

        try {
            // Should come in base64
            $publicKey = $validated['public_key'];
            // verify base64 public key
            $publicKeyDecoded = base64_decode($publicKey);
            if (!$publicKeyDecoded) {
                return $this->sendError('Invalid public key', [], 400);
            }
            $isValidPublicKey = $encryptionService->verifyPublicKey($publicKeyDecoded);
            if (!$isValidPublicKey) {
                return $this->sendError('Invalid public key', [], 400);
            }
            $user = $request->user();
            $user->public_key = $publicKey;
            $user->save();
            return $this->sendResponse([], 'Public key saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
