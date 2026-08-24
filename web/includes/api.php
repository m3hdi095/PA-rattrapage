<?php

function apiRequest($method, $endpoint, $data = null, $token = null) : array 
{
    $url = 'http://localhost:8081' . $endpoint;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("Impossible de contacter l'API (" . $curlError . "). Vérifie que l'API Go tourne bien sur le port 8081.");
    }

    return [
        'statusCode' => $statusCode,
        'body' => json_decode($response, true)
    ];
}

/**
 * Comme apiRequest, mais sans décodage JSON du corps — pour les réponses
 * binaires (ex: le fichier Excel des plannings).
 */
function apiRequestRaw($method, $endpoint, $token = null): array
{
    $url = 'http://localhost:8081' . $endpoint;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [];
    if ($token) {
        $headers[] = 'Authorization: ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("Impossible de contacter l'API (" . $curlError . "). Vérifie que l'API Go tourne bien sur le port 8081.");
    }

    return [
        'statusCode'  => $statusCode,
        'body'        => $response,
        'contentType' => $contentType ?? 'application/octet-stream',
    ];
}

/**
 * Lit les claims d'un JWT sans vérifier la signature (déjà faite côté API) —
 * uniquement pour affichage/branchement UI (ex: savoir si l'admin connecté
 * est super_admin), jamais pour une décision de sécurité côté PHP.
 */
function jwtClaims(string $token): array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return [];
    }
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    return $payload ?? [];
}