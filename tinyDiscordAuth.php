<?php

/***************************************************************************
 *
 *  TinyPHP-Discord
 *  Author: Jasmin Dreasond
 *  Copyright: © 2018 Jasmin Dreasond
 *  
 *  Github: https://github.com/JasminDreasond
 *  License: MIT
 *
 ***************************************************************************/

// https://discordapp.com/developers/docs/topics/oauth2
// https://discordapi.com/permissions.html
class tinyDSAuth
{

    protected $id;
    protected $scope;
    protected $permissions;
    protected $redirect;
    protected $secret;
    protected $state;

    public function __construct($data)
    {

        $this->id = $data['id'];
        $this->scope = $data['scope'];
        $this->permissions = $data['permissions'];
        $this->redirect = $data['redirect'];
        $this->secret = $data['secret'];
        $this->state = $data['state'];

    }

    public function getURL($data = null)
    {

        if (isset($data) == false) {

            $data['scope'] = $this->scope;
            $data['state'] = $this->state;
            $data['clientID'] = $this->id;
            $data['redirect'] = $this->redirect;
            $data['permissions'] = $this->permissions;

        }

        if (is_string($data['scope']) == false) {

            $scopeFinal = '';
            for ($i = 0; $i < count($data['scope']); $i++) {

                if ($i > 0) {
                    $scopeFinal = $scopeFinal . '%20' . $data['scope'][$i];
                } else {
                    $scopeFinal = $data['scope'][$i];
                }

            }

        } else {
            $scopeFinal = $scope;
        }

        if (is_string($data['state']) == true) {
            $state = '&state=' . $data['state'];
        } else {
            $state = '';
        }

        return "https://discordapp.com/oauth2/authorize?client_id=" . $data['clientID'] . "&scope=" . $scopeFinal . $state . "&response_type=code&redirect_uri=" . urlencode($data['redirect']) . "&permissions=" . $data['permissions'];

    }

    public function getToken($data)
    {

        if (is_string($data)) {

            $code = $data;
            $secret = $this->secret;
            $id = $this->id;
            $redirect = $this->redirect;

        } else {

            $code = $data['code'];

            if (isset($data['secret']) == false) {$secret = $this->secret;} else {
                $secret = $data['secret'];
            }

            if (isset($data['id']) == false) {$id = $this->id;} else {
                $id = $data['id'];
            }

            if (isset($data['redirect']) == false) {$redirect = $this->redirect;} else {
                $redirect = $data['redirect'];
            }

        }

        $info = curl_init();

        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/api/oauth2/token",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                "grant_type" => "authorization_code",
                "client_id" => $id,
                "client_secret" => $secret,
                "redirect_uri" => $redirect,
                "code" => $code,
            ),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $tinyresult = curl_exec($info);
        if ($tinyresult == false) {
            $tinyerror = curl_error($info);
        } else {
            $tinyerror = null;
            $tinyresult = json_decode($tinyresult);
        }

        $httpcode = curl_getinfo($info, CURLINFO_HTTP_CODE);

        curl_close($info);

        return array(
            "data" => $tinyresult,
            "err" => $tinyerror,
            "state" => $httpcode,
        );

    }

    public function getUser($data)
    {

        $info = curl_init();
        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/api/" . $data['type'],
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $data['token'],
            ),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $tinyresult = curl_exec($info);
        if ($tinyresult == false) {
            $tinyerror = curl_error($info);
        } else {
            $tinyerror = null;
            $tinyresult = json_decode($tinyresult, true);
        }

        $httpcode = curl_getinfo($info, CURLINFO_HTTP_CODE);

        curl_close($info);

        return array(
            "data" => $tinyresult,
            "err" => $tinyerror,
            "state" => $httpcode,
        );

    }

    public function getExpiration($time)
    {
        return strtotime("now") + $time;
    }

    public function hasExpired($time)
    {

        if (strtotime("now") > $time) {
            return true;
        } else {
            return false;
        }

    }

};

?>