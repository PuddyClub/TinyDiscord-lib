# TinyPHP-Discord
Tiny PHP Library to use the Discord oAuth2

## Example Code

```php


    session_start();

    $tinyDiscord = new tinyDSAuth(array(
        'id' => '',
        'scope' => array('identify'),
        'permissions' => 0,
        'redirect' => '',
        'secret' => '',
        'state' => '',
    ));

    if (isset($_GET['token'])) {

        $tiny_user = tinyDSAuth::getUser(array(
            'token' => $_GET['token'], 'type' => 'users/@me', 'refresh' => $_GET['refresh_token'],
        ));

        // Show some token details
        echo '<h2>Token details:</h2>';
        echo 'Token: ' . $_GET['token'] . "<br/>";
        echo 'Refresh token: ' . $_GET['refresh_token'] . "<br/>";

        echo '<h2>Resource owner details:</h2>';
        printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

    } else

    if (isset($_GET['code'])) {

        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            http_response_code(405);

            echo 'ERROR STATE';

        } else {

            $token = $tinyDiscord->getToken($_GET['code']);

            if ((isset($token['err']) == false) && (isset($token['data']->error) == false)) {

                $tiny_user = tinyDSAuth::getUser(array(
                    'token' => $token['data']->access_token, 'type' => 'users/@me', 'refresh' => $token['data']->refresh_token,
                ));

                if ((isset($ds_user['err']) == false) && (isset($ds_user['data']->error) == false)) {

                    // Show some token details
                    echo '<h2>Token details:</h2>';
                    echo 'Token: ' . $token['data']->access_token . "<br/>";
                    echo 'Refresh token: ' . $token['data']->refresh_token . "<br/>";
                    echo 'Token Type: ' . $token['data']->token_type . "<br/>";

                    echo 'Expires: ' . date("m/d/Y h:i:s A T", tinyDSAuth::getExpiration($token['data']->expires_in)) . " - ";
                    echo (tinyDSAuth::hasExpired(tinyDSAuth::getExpiration($token['data']->expires_in)) ? 'expired' : 'not expired') . "<br/>";

                    echo '<h2>Resource owner details:</h2>';
                    printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

                    echo '<pre>';
                    print_r($tiny_user);
                    echo '</pre>';

                    echo '<pre>';
                    print_r($token);
                    echo '</pre>';

                } else {

                    echo 'ERROR GET DATA!';

                }

            } else {

                echo 'ERROR GET TOKEN!';

            }

        }

    } else {

        $_SESSION['oauth2state'] = $tinyDiscord->getState();
        header('Location: ' . $tinyDiscord->getURL());

    }


```