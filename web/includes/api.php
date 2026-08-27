<?php

function apiBaseUrl(): string
{
    return getenv('API_BASE_URL') ?: 'http://localhost:8081';
}

function apiRequest($method, $endpoint, $data = null, $token = null) : array
{
    $url = apiBaseUrl() . $endpoint;
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
        throw new Exception("Le service est momentanément indisponible. Merci de réessayer dans quelques instants.");
    }

    return [
        'statusCode' => $statusCode,
        'body' => json_decode($response, true)
    ];
}

function apiRequestRaw($method, $endpoint, $token = null): array
{
    $url = apiBaseUrl() . $endpoint;
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
        throw new Exception("Le service est momentanément indisponible. Merci de réessayer dans quelques instants.");
    }

    return [
        'statusCode'  => $statusCode,
        'body'        => $response,
        'contentType' => $contentType ?? 'application/octet-stream',
    ];
}

function jwtClaims(string $token): array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return [];
    }
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    return $payload ?? [];
}