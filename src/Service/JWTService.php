<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // on génère le token (validity = 3h00 en secondes) via site https://jwt.io/

    /**
     * Génération du JWT
     *
     * @param  mixed $header
     * @param  mixed $payload
     * @param  mixed $secret
     * @param  mixed $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity): string
    {
        //dd($validity);
        //pour avoir payload
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }
        // encoder en base64 (en passant par un encodage json en 1er)
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));
        // nettoyer les valeurs encodées (retrait des + / et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);
        // générer la signature
        $secret = base64_encode($secret);
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);
        // créer le token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;
        return $jwt;
    }

    // vérifier la validité du token (correctement formé)

    public function isValidToken(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    //  on récupère le header
    public function getHeader(string $token): array
    {
        // on démonte le token
        $array = explode('.', $token);

        // on décode le header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }


    //  on récupère le payload
    public function getPayload(string $token): array
    {
        // on démonte le token
        $array = explode('.', $token);

        // on décode le payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    // vérifier si le token a expiré 
    public function isExpiredToken(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    // vérifier la signature du token

    public function checkSignatureToken(string $token, string $secret)
    {
        //on récupère header et payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // on regénère un token // 0 en fin de variable pour la condition validity : permet de sortir de la condition et de ne pas générer à nouveau les 'exp' et 'iat'
        $vérifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $vérifToken;
    }
}
