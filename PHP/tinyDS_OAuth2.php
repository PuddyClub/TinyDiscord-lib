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

class tinyDS_OAuth2
{

    protected $id;
    protected $scope;
    protected $permissions;
    protected $redirect;
    protected $secret;
    protected $state;
    protected $expire;

    public function getScope($data = null, $type = 0)
    {

        $scopeFinal = '';
        for ($i = 0; $i < count($data); $i++) {

            if ($i > 0) {

                if ($type == 0) {
                    $scopeFinal = $scopeFinal . '%20' . $data[$i];
                } else if ($type == 1) {
                    $scopeFinal = $scopeFinal . ' ' . $data[$i];
                }

            } else {
                $scopeFinal = $data[$i];
            }

        }

        return $scopeFinal;

    }

    public function __construct($data)
    {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        if (isset($data['scope'])) {
            $this->scope = $data['scope'];
        }
        if (isset($data['permissions'])) {
            $this->permissions = $data['permissions'];
        }
        if (isset($data['redirect'])) {
            $this->redirect = $data['redirect'];
        }
        if (isset($data['secret'])) {
            $this->secret = $data['secret'];
        }
        if (isset($data['state'])) {
            $this->state = $data['state'];
        }

    }

    public function getURL($data = null)
    {

        if (!isset($data)) {

            $data['scope'] = $this->scope;
            $data['state'] = $this->state;
            $data['clientID'] = $this->id;
            $data['redirect'] = $this->redirect;
            $data['permissions'] = $this->permissions;

        }

        if (!is_string($data['scope'])) {
            $scope = $this->getScope($data['scope'], 0);
        } else {
            $scope = '';
        }

        if (is_string($data['state'])) {
            $state = '&state=' . $data['state'];
        } else {
            $state = '';
        }

        return "https://discordapp.com/oauth2/authorize?client_id=" . $data['clientID'] . "&scope=" . $scope . $state . "&response_type=code&redirect_uri=" . urlencode($data['redirect']) . "&permissions=" . $data['permissions'];

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

            if (!isset($data['secret'])) {$secret = $this->secret;} else {
                $secret = $data['secret'];
            }

            if (!isset($data['id'])) {$id = $this->id;} else {
                $id = $data['id'];
            }

            if (!isset($data['redirect'])) {$redirect = $this->redirect;} else {
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

        $this->expire = (int) $tinyresult->expires_in;

        return array(
            "data" => $tinyresult,
            "err" => $tinyerror,
            "state" => $httpcode,
        );

    }

    public function getUser($data)
    {

        if (!isset($data['timeout'])) {
            $data['timeout'] = 1;
        }

        $info = curl_init();
        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/api/" . $data['type'],
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $data['token'],
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $data['timeout'],
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

        if (($httpcode != 200) && (isset($data['refresh'])) && (!empty($data['refresh']))) {

            $newtoken = $this->refreshToken($data['refresh']);
            return $this->getUser(array(
                'token' => $newtoken['data']->access_token, 'type' => $data['type'], 'refreshToken' => $newtoken,
            ));

        } else {

            if (!isset($data['refreshToken'])) {
                $data['refreshToken'] = '';
            }

            return array(
                "data" => $tinyresult,
                "err" => $tinyerror,
                "state" => $httpcode,
                "refresh" => $data['refreshToken'],
            );

        }

    }

    public function addGuildMember($data)
    {

        $postfield = array(
            "access_token" => $data['token'],
        );

        if (isset($data['nick'])) {
            $postfield['nick'] = $data['nick'];
        }
        if (isset($data['roles'])) {
            $postfield['roles'] = $data['roles'];
        }
        if (isset($data['mute'])) {
            $postfield['mute'] = $data['mute'];
        }
        if (isset($data['deaf'])) {
            $postfield['deaf'] = $data['deaf'];
        }

        $revoke = $data;
        $info = curl_init();

        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/guilds/" . $data['guildID'] . "/members/" . $data['userID'],
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_RETURNTRANSFER => true,
        ));

        curl_exec($info);
        $httpcode = curl_getinfo($info, CURLINFO_HTTP_CODE);

        curl_close($info);

        return $httpcode;

    }

    public function addtoGroupDM($token, $channelID, $userID, $nick = null)
    {

        if (!isset($nick)) {
            $postfield = array(
                "access_token" => $token,
            );
        } else {
            $postfield = array(
                "access_token" => $token,
                "nick" => $nick,
            );
        }

        $info = curl_init();

        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/channels/" . $channelID . "/recipients/" . $userID,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_RETURNTRANSFER => true,
        ));

        curl_exec($info);
        $httpcode = curl_getinfo($info, CURLINFO_HTTP_CODE);

        curl_close($info);

        return $httpcode;

    }

    public function getState()
    {
        return $this->state;
    }

    public function getExpiration($time = null)
    {

        if (!isset($time)) {
            $time = $this->expire;
        }

        return strtotime("now") + $time;

    }

    public function hasExpired($time = null)
    {

        if (!isset($time)) {
            $time = $this->getExpiration();
        }

        if (strtotime("now") > $time) {
            return true;
        } else {
            return false;
        }

    }

    public function refreshToken($data = null)
    {

        if (is_string($data)) {

            $refresh = $data;
            $secret = $this->secret;
            $id = $this->id;
            $redirect = $this->redirect;
            $scope = $this->getScope($this->scope, 1);

        } else {

            $refresh = $data['refresh'];

            if (!isset($data['secret'])) {$secret = $this->secret;} else {
                $secret = $data['secret'];
            }

            if (!isset($data['id'])) {$id = $this->id;} else {
                $id = $data['id'];
            }

            if (!isset($data['redirect'])) {$redirect = $this->redirect;} else {
                $redirect = $data['redirect'];
            }

            if (!isset($data['scope'])) {$scope = $this->getScope($this->scope, 1);} else {
                $scope = $this->getScope($data['scope'], 1);
            }

        }

        $info = curl_init();

        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/api/oauth2/token",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                "grant_type" => "refresh_token",
                "client_id" => $id,
                "client_secret" => $secret,
                "redirect_uri" => $redirect,
                "refresh_token" => $refresh,
                "scope" => $scope,
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

    public function revokeToken($data)
    {

        $revoke = $data;
        $info = curl_init();

        curl_setopt_array($info, array(
            CURLOPT_URL => "https://discordapp.com/api/oauth2/token/revoke?token=" . $revoke,
            CURLOPT_RETURNTRANSFER => true,
        ));

        curl_exec($info);
        $httpcode = curl_getinfo($info, CURLINFO_HTTP_CODE);

        curl_close($info);

        return $httpcode;

    }

};
