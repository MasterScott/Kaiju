<?php

class KaijuRequest
{
    public static function SendRequest($Url, $PostRequest = true, $Body = null, $Headers = null, $Method = null) : array
    {
        $curlRequest = curl_init();

        curl_setopt($curlRequest, CURLOPT_URL, $Url);
        curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);

        if ($PostRequest) {
            curl_setopt($curlRequest, CURLOPT_POST, true);
        }

        if ($Method !== null) {
            curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, $Method);
        }

        if ($Body !== null) {
            curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $Body);
        }

        if ($Headers !== null) {
            curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $Headers);
        }

        $RequestMessageResponse = curl_exec($curlRequest);

        $RequestStatusCode = curl_getinfo($curlRequest, CURLINFO_HTTP_CODE);

        curl_close($curlRequest);

        return array(
            'responseString' => $RequestMessageResponse,
            'status_code' => $RequestStatusCode
        );
    }
}