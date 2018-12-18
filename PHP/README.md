# TinyPHP Discord OAuth2
Simple Tiny PHP Library to use in your Discord OAuth2

More Info:

https://discordapp.com/developers/docs/topics/oauth2

https://discordapi.com/permissions.html

## Features

### Create the OAuth2 Object

id: ClientID

secret: The Secret ID

scope: Array with the scope

permissions: Permission code

redirect: The redirect URL

state: Protection for your OAuth2

```php 
$tinyDiscord = new tinyDS_OAuth2(
    array(
        'id' => '',
        'scope' => array('identify'),
        'permissions' => 0,
        'redirect' => '',
        'secret' => '',
        'state' => ''
    )
); 
```

### Get OAuth2 Page URL

Use this function to return your URL

```php
$tinyDiscord->getURL();
```

### Get State

Use this function to return your State Value

```php
$tinyDiscord->getState();
```

### Get Token

Use this function to get your token

code: The code result from the OAuth2 Page

```php
$tinyDiscord->getToken($_GET['code']);
```

### Refresh Token

Use this function to refresh your token

refresh: The token refresh code received from getToken

```php
$token = $tinyDiscord->refreshToken($_GET['refresh']);
```

### Revoke Token

Use this function to revoke your token. The value returned is the HTTP Code

revoke: The token code received from getToken

```php
$revoke = tinyDS_OAuth2::revokeToken($_GET['revoke']);
```

### Add to Group DM

Use this function to add users into a DM. The value returned is the HTTP Code

addtoGroupDM: The token code received from getToken

channelID: The channel ID

userID: The user ID

nick: User Nickname (Optional)

```php
$addtoGroupDM = tinyDS_OAuth2::addtoGroupDM(
            $_GET['addtoGroupDM'],
            $_GET['channelID'],
            $_GET['userID'],
            $_GET['nick']
);
```

### Add Guild Member

Use this function to add users into a Guild. The value returned is the HTTP Code

revoke: The token code received from getToken

nick: User Nickname (Optional)

roles: User Role IDs (Optional)

mute: The user is muted or not

deaf: The user is deaf or not

guildID: The Guild ID

userID: The user ID

```php
$addGuildMember = tinyDS_OAuth2::addGuildMember(array(

            'token' => $_GET['addGuildMember'],

            'nick' => $_GET['nick'],
            'roles' => $_GET['roles'],
            'mute' => $_GET['mute'],
            'deaf' => $_GET['deaf'],

            'guildID' => $_GET['guildID'],
            'userID' => $_GET['userID'],

));
```

### Get User Data

Use this function to call the user info using your token

token: The token code

refresh: You can insert the value refresh to auto refresh the token if the token is expired (Optional)

timeout: Timeout to get the user data (Optional)

type: The data type. You can see more info in https://discordapp.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes 

```php
tinyDS_OAuth2::getUser(array(
    'token' => $_GET['token'], 'type' => 'users/@me', 'refresh' => $_GET['refresh']
));
```

<hr>

### Example Code

```php


    session_start();

    // The Auth System
    $tinyDiscord = new tinyDS_OAuth2(array(
        'id' => '',
        'scope' => array('identify'),
        'permissions' => 0,
        'redirect' => '',
        'secret' => '',
        'state' => ''
    ));

    // is Token? Use the token here to test
    
    if (isset($_GET['revoke'])) {

        echo '<pre>';
        print_r(tinyDS_OAuth2::revokeToken($_GET['revoke']));
        echo '</pre>';

    } else 
    
    // Add to Group DM
    if (isset($_GET['addtoGroupDM'])) {

        echo '<pre>';
        print_r(tinyDS_OAuth2::addtoGroupDM(
            $_GET['addtoGroupDM'],
            $_GET['channelID'],
            $_GET['userID']
        ));
        echo '</pre>';

    } else 
    
    // Add Guild Member
    if (isset($_GET['addGuildMember'])) {

        echo '<pre>';
        print_r(tinyDS_OAuth2::addGuildMember(array(

            'token' => $_GET['addGuildMember'],

            'nick' => $_GET['nick'],
            'roles' => $_GET['roles'],
            'mute' => $_GET['mute'],
            'deaf' => $_GET['deaf'],

            'guildID' => $_GET['guildID'],
            'userID' => $_GET['userID'],

        )));
        echo '</pre>';

    } else

    // Others
    if ((isset($_GET['guilds'])) || (isset($_GET['connections']))) {

        // Guild List
        if (isset($_GET['guilds'])) {
            $thetk = $_GET['guilds'];
            $thetk_title = 'Guild';
            $thetk_type = 'users/@me/guilds';
        } 
        
        // Connection List
        else if (isset($_GET['connections'])) {
            $thetk = $_GET['connections'];
            $thetk_title = 'Connection';
            $thetk_type = 'users/@me/connections';
        }

        $tiny_data = tinyDS_OAuth2::getUser(array(
            'token' => $thetk, 'type' => $thetk_type, 'refresh' => $_GET['refresh_token'],
        ));

        $tiny_user = tinyDS_OAuth2::getUser(array(
            'token' => $thetk, 'type' => 'users/@me', 'refresh' => $_GET['refresh_token'],
        ));

        echo '<h2>Token details:</h2>';
        if ((!isset($tiny_user['err'])) && (!isset($tiny_user['data']->error))) {

            // Show some token details
            echo 'Token: ' . $thetk . "<br/>";
            echo 'Refresh token: ' . $_GET['refresh_token'] . "<br/>";

            echo '<h2>Resource owner details:</h2>';
            printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

        }

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

        echo '<h2>' . $thetk_title . ' details:</h2>';

        echo '<pre>';
        print_r($tiny_data);
        echo '</pre>';

    } else
    
    if (isset($_GET['refresh'])) {

        $token = $tinyDiscord->refreshToken($_GET['refresh']);

        if ((!isset($token['err'])) && (!isset($token['data']->error))) {

            $tiny_user = tinyDS_OAuth2::getUser(array(
                'token' => $token['data']->access_token, 'type' => 'users/@me'
            ));

            if ((!isset($tiny_user['err'])) && (!isset($tiny_user['data']->error))) {

                // Show some token details
                echo '<h2>Token details:</h2>';
                echo 'Token: ' . $token['data']->access_token . "<br/>";
                echo 'Refresh token: ' . $token['data']->refresh_token . "<br/>";
                echo 'Token Type: ' . $token['data']->token_type . "<br/>";

                echo 'Expires: ' . date("m/d/Y h:i:s A T", $tinyDiscord->getExpiration()) . " - ";
                echo ($tinyDiscord->hasExpired() ? 'expired' : 'not expired') . "<br/>";

                echo '<h2>Resource owner details:</h2>';
                printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

            }

        }

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

        echo '<pre>';
        print_r($token);
        echo '</pre>';

    } else

    if (isset($_GET['token'])) {

        // Get user info
                $tiny_user = tinyDS_OAuth2::getUser(array(
            'token' => $_GET['token'], 'type' => 'users/@me', 'refresh' => $_GET['refresh_token']
        ));

        if ((!isset($tiny_user['err'])) && (!isset($tiny_user['data']->error))) {

            // Show some token details
            echo '<h2>Token details:</h2>';
            echo 'Token: ' . $_GET['token'] . "<br/>";
            echo 'Refresh token: ' . $_GET['refresh_token'] . "<br/>";

            echo '<h2>Resource owner details:</h2>';
            printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

        }

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

    } else

    // is code from the OAuth2? Use the code here
    if (isset($_GET['code'])) {

        // Protection
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            http_response_code(405);

            echo 'ERROR STATE';

        } else {

            // Get the user token
            $token = $tinyDiscord->getToken($_GET['code']);

            if ((!isset($token['err'])) && (!isset($token['data']->error))) {

                // Get the user info
                $tiny_user = tinyDS_OAuth2::getUser(array(
                    'token' => $token['data']->access_token, 'type' => 'users/@me'
                ));

                if ((!isset($tiny_user['err'])) && (!isset($tiny_user['data']->error))) {

                    // Show some token details
                    echo '<h2>Token details:</h2>';
                    echo 'Token: ' . $token['data']->access_token . "<br/>";
                    echo 'Refresh token: ' . $token['data']->refresh_token . "<br/>";
                    echo 'Token Type: ' . $token['data']->token_type . "<br/>";

                    echo 'Expires: ' . date("m/d/Y h:i:s A T", $tinyDiscord->getExpiration()) . " - ";
                    echo ($tinyDiscord->hasExpired() ? 'expired' : 'not expired') . "<br/>";

                    echo '<h2>Resource owner details:</h2>';
                    printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);



                }

            }

            echo '<pre>';
            print_r($tiny_user);
            echo '</pre>';

            echo '<pre>';
            print_r($token);
            echo '</pre>';

        }

    } else {

        // Send the user into the Discord OAuth2
        $_SESSION['oauth2state'] = $tinyDiscord->getState();
        header('Location: ' . $tinyDiscord->getURL());

    }


```