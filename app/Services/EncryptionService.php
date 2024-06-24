<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use Illuminate\Support\Facades\Storage;

class EncryptionService
{

    private $publicKey;
    private $privateKey;


    public function __construct()
    {
        // check if keys exist in storage
        if (Storage::disk('keys')->has('private.pem') && Storage::disk('keys')->has('public.pem')) {
            $this->privateKey = Storage::disk('keys')->get('private.pem');
            $this->publicKey = Storage::disk('keys')->get('public.pem');
        } else {
            $keys = $this->generateKeys();
            // save keys in disk 'keys'
            Storage::disk('keys')->put('private.pem', $keys['private']);
            Storage::disk('keys')->put('public.pem', $keys['public']);

            $this->publicKey = $keys['public'];
            $this->privateKey = $keys['private'];
        }
    }

    /**
     * Generate encryption keys
     * @return array
     */
    private function generateKeys() {
        $private = RSA::createKey();
        $public = $private->getPublicKey();

        // save keys in storage, in keys disk
        $privateKey = $private->toString('PKCS1');
        $publicKey = $public->toString('PKCS8');

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    /**
    * Get Public Key
    * @return string
     */
    public function getPublicKey() {
        return $this->publicKey;
    }

    /**
     * Encrypt String, this returns the message encrypted and in base64
     * $publicKey should be in base64
     * @param string $data
     * @param string $publicKey
     * @return string
     */
    public function encrypt($data, $publicKey)
    {
        openssl_public_encrypt($data, $encryptedData, $publicKey);
        return base64_encode($encryptedData);
    }

    public function decrypt($data) {
        openssl_private_decrypt(base64_decode($data), $decryptedData, $this->privateKey);
        return $decryptedData;
    }

    public function verifyPublicKey($publicKey) {
        try {
            $key = RSA::loadPublicKey($publicKey);
            return true;
        } catch (NoKeyLoadedException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
