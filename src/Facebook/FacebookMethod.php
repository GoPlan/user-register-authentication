<?php
/**
 * Created by PhpStorm.
 *
 * Duc-Anh LE (ducanh.ke@gmail.com)
 *
 * User: ducanh-ki
 * Date: 3/6/17
 * Time: 5:17 PM
 */

namespace CreativeDelta\User\Facebook;


use CreativeDelta\User\Core\Domain\OAuthAuthenticationInterface;
use CreativeDelta\User\Core\Domain\UserRegisterMethodAdapter;
use Zend\Db\Adapter\AdapterInterface;

class FacebookMethod implements UserRegisterMethodAdapter, OAuthAuthenticationInterface
{
    const METHOD_NAME              = "facebook";
    const METHOD_TABLE_NAME        = "UserFacebook";
    const METHOD_CONFIG_APP_ID     = "appId";
    const METHOD_CONFIG_APP_SECRET = "appSecret";
    const METHOD_CONFIG_APP_SCOPE  = "appScope";

    const FACEBOOK_RESPONSE        = "code";
    const FACEBOOK_OAUTH_URL       = "https://www.facebook.com/v2.8/dialog/oauth";
    const FACEBOOK_TOKEN_URL       = "https://graph.facebook.com/v2.8/oauth/access_token";
    const FACEBOOK_GRAPH_URL       = "https://graph.facebook.com/me";
    const FACEBOOK_SCOPE           = "public_profile";
    const FACEBOOK_PROFILE_FIELDS  = "id, first_name, last_name, email";
    const FACEBOOK_PROFILE_ID_NAME = "id";

    const RESULT_QUERY_CODE  = "code";
    const RESULT_QUERY_STATE = "state";

    const PROFILE_FIELD_ID         = "id";
    const PROFILE_FIELD_FIRST_NAME = "first_name";
    const PROFILE_FIELD_LAST_NAME  = "last_name";
    const PROFILE_FIELD_EMAIL      = "email";

    /**
     * @var  AdapterInterface
     */
    protected $dbAdapter;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var  FacebookClient
     */
    protected $facebookClient;

    /**
     * @var FacebookTable
     */
    protected $facebookTable;

    /**
     * UserFacebookService constructor.
     * @param $dbAdapter
     * @param $config
     */
    public function __construct($dbAdapter, array $config)
    {
        $this->config    = $config;
        $this->dbAdapter = $dbAdapter;

        $appId     = $config[self::METHOD_CONFIG_APP_ID];
        $appSecret = $config[self::METHOD_CONFIG_APP_SECRET];
        $appScope  = $config[self::METHOD_CONFIG_APP_SCOPE];

        $this->facebookTable  = new FacebookTable($this->dbAdapter);
        $this->facebookClient = new FacebookClient($appId, $appSecret, $appScope);
    }

    /**
     * Use this method to receive URL for Facebook authentication. To initialize the sign-in sequence, redirect yourself to the URL.
     * Once sign-in step is completed, the user will be redirected back to the URL provided previously in $redirectUri.
     * You should have a controller::action catch this redirection. Then in this action, further activities can be arranged using returned the "code" and "state".
     *
     * @param null $redirectUri
     * @param      $state
     * @return string
     */
    public function makeAuthenticationUrl($redirectUri, $state = null)
    {
        return $this->facebookClient->makeAuthenticationUrl($redirectUri, $state);
    }

    /**
     * Since service is only set to received [code] in the authentication response,
     * an extra step must be made in order to receive an access_token for your application.
     *
     * Use this method and provide it with the code (received from authentication) to get a token.
     *
     * @param $redirectUri
     * @param $code
     * @return $this
     * @throws FacebookException|\Exception
     */
    public function initAccessToken($redirectUri, $code)
    {
        $this->facebookClient->initAccessToken($redirectUri, $code);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->facebookClient->getAccessToken();
    }


    /**
     * @param string $fields // a comma separated string of profile fields to be retrieved
     * @return array
     * @throws FacebookException|\Exception
     */
    public function getOAuthProfile($fields = null)
    {
        return $this->facebookClient->getFacebookProfile($fields);
    }

    /**
     * @return null|FacebookProfile
     */
    public function getLocalProfile()
    {
        $oauthData = $this->getOAuthProfile();
        $localData = $this->facebookTable->getByUserId($oauthData[self::FACEBOOK_PROFILE_ID_NAME]);
        $profile   = $localData ? FacebookProfile::newFromArray($this->facebookTable, $localData->getArrayCopy(), true) : null;
        return $profile;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function has($userId)
    {
        return $this->facebookTable->hasUserId($userId);
    }

    public function register($identityId, $userId, $dataJson)
    {
        $profile = new FacebookProfile($this->facebookTable);
        $profile->setAutoSequence(FacebookTable::AUTO_SEQUENCE);
        $profile->setIdentityId($identityId);
        $profile->setUserId($userId);
        $profile->save();

        return $profile->getId();
    }

    public function getName()
    {
        return self::METHOD_NAME;
    }

    public function getTableName()
    {
        return self::METHOD_TABLE_NAME;
    }

}