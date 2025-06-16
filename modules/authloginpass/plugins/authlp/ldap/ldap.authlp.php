<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2024 Laurent Jouanneau
 * @license   MIT
 */

use \Jelix\Authentication\LoginPass\BackendAbstract;
use Jelix\Authentication\Core\AuthSession\AuthUser;

/**
 * authentication provider for the authloginpass module
 *
 * it checks authentication into an ldap
 *
 * @internal see https://tools.ietf.org/html/rfc4510
 */
class ldapBackend extends BackendAbstract
{

    /**
     * default user attributes list
     * @var array
     */
    protected $_default_attributes = array(
        "cn"=>"lastname",
        "name"=>"firstname"
    );

    protected $_defaultNewUserAttributes = array(
        "objectClass" => "inetOrgPerson",
        "userPassword" => "%%PASSWORD%%"
    );


    protected $uriConnect = '';


    /**
     * It's a cache for the latest user retrieved by userExists.
     *
     * It avoids to do the same query when calling userExists() and then
     * another method that needs the retrieved record.
     *
     * @var ldapBackendUserLdapAttributes|null the user record retrieved during userExists
     */
    protected $latestExistingUser = null;

    /**
     * @param array $params configuration parameters
     * @param array|null profile data. if not given, it will use the profile
     *                   indicated into the profile parameter
     */
    public function __construct($params, $profile = null)
    {
        if (!extension_loaded('ldap')) {
            throw new jException('authloginpass~ldap.error.extension.unloaded');
        }
        parent::__construct($params);

        if ($profile === null) {
            if (!isset($this->_params['profile']) || $this->_params['profile'] == '') {
                throw new jException('authloginpass~ldap.error.ldap.profile.missing');
            }

            $profile = jProfiles::get('ldap', $this->_params['profile']);
        }
        $this->_params = array_merge($this->_params, $profile);

        // default ldap parameters
        $_default_params = array(
            'hostname'      =>  'localhost',
            'tlsMode'       => '',
            'port'          =>  389,
            'adminUserDn'      =>  null,
            'adminPassword'      =>  null,
            'protocolVersion'   =>  3,
            'searchUserBaseDN' => '',
            'searchUserByEmailFilter' => array(),
            'searchGroupFilter' => '',
            'searchGroupKeepUserInDefaultGroups' => true,
            'searchGroupProperty' => '',
            'searchGroupBaseDN' => '',
            'bindUserDN' => '',
            'newUserDN' => '',
            'passwordLdapHashAlgo' => '',
            'passwordLdapSaltLength' => 5,
            'featureCreateUser' => true,
            'featureDeleteUser' => true,
            'featureChangePassword' => true,
            'aclAdapterClass' => ''
        );

        // iterate each default parameter and apply it to actual params if missing in $params.
        foreach ($_default_params as $name => $value) {
            if (!isset($this->_params[$name]) || $this->_params[$name] === '') {
                $this->_params[$name] = $value;
            }
        }

        if ($this->_params['searchUserBaseDN'] == '') {
            throw new jException('authloginpass~ldap.error.search.base.missing');
        }

        if (!isset($this->_params['searchAttributes']) || $this->_params['searchAttributes'] == '') {
            $this->_params['searchAttributes'] = $this->_default_attributes;
        } else {
            $attrs = explode(",", $this->_params['searchAttributes']);
            $this->_params['searchAttributes'] = array();
            foreach ($attrs as $attr) {
                if (strpos($attr, ':') === false) {
                    $attr = trim($attr);
                    $this->_params['searchAttributes'][$attr] = $attr;
                } else {
                    $attr = explode(':', $attr);
                    $this->_params['searchAttributes'][trim($attr[0])] = trim($attr[1]);
                }
            }
        }

        if (!isset($this->_params['newUserLdapAttributes']) || $this->_params['newUserLdapAttributes'] == '') {
            $this->_params['newUserLdapAttributes'] = $this->_defaultNewUserAttributes;
        } else {
            $attrs = explode(",", $this->_params['newUserLdapAttributes']);
            $this->_params['newUserLdapAttributes'] = array();
            foreach ($attrs as $attr) {
                if (strpos($attr, ':') !== false) {
                    $attr = explode(':', $attr);
                    $this->_params['newUserLdapAttributes'][trim($attr[0])] = trim($attr[1]);
                }
            }
        }

        if (!isset($this->_params['searchUserFilter']) || $this->_params['searchUserFilter'] == '') {
            throw new jException('authloginpass~ldap.error.searchUserFilter.missing');
        }
        if (!is_array($this->_params['searchUserFilter'])) {
            $this->_params['searchUserFilter'] = array($this->_params['searchUserFilter']);
        }

        if ($this->_params['searchUserByEmailFilter'] == '') {
            throw new jException('authloginpass~ldap.error.searchUserByEmailFilter.missing');
        }

        if (!is_array($this->_params['searchUserByEmailFilter'])) {
            $this->_params['searchUserByEmailFilter'] = array($this->_params['searchUserByEmailFilter']);
        }

        if (!isset($this->_params['bindUserDN']) || $this->_params['bindUserDN'] == '') {
            throw new jException('authloginpass~ldap.error.bindUserDN.missing');
        }
        if (!is_array($this->_params['bindUserDN'])) {
            $this->_params['bindUserDN'] = array($this->_params['bindUserDN']);
        }

        if ($this->_params['newUserDN'] == '' && $this->_params['featureCreateUser']) {
            throw new jException('authloginpass~ldap.error.newUserDN.missing');
        }

        $uri = $this->_params['hostname'];

        if (preg_match('!^ldap(s?)://!', $uri, $m)) { // old way to specify ldaps protocol
            $predefinedPort = '';
            if (preg_match('!:(\d+)/?!', $uri, $mp)) {
                $predefinedPort = $mp[1];
            }
            if (isset($m[1]) && $m[1] == 's') {
                $this->_params['tlsMode'] = 'ldaps';
            }
            elseif ($this->_params['tlsMode'] == 'ldaps') {
                $this->_params['tlsMode'] = 'starttls';
            }
            if ($predefinedPort == '') {
                $uri .= ':'.$this->_params['port'];
            }
            else {
                $this->_params['port'] = $predefinedPort;
            }
            $this->uriConnect = $uri;
        }
        else {
            $uri .= ':'.$this->_params['port'];
            if ($this->_params['tlsMode'] == 'ldaps' || $this->_params['port'] == 636 ) {
                $this->uriConnect = 'ldaps://'.$uri;
                $this->_params['tlsMode'] = 'ldaps';
            }
            else {
                $this->uriConnect = 'ldap://'.$uri;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getFeatures()
    {
        $feat = 0;
        if ($this->_params['featureChangePassword']) {
            $feat |= self::FEATURE_CHANGE_PASSWORD;
        }
        if ($this->_params['featureCreateUser']) {
            $feat |= self::FEATURE_CREATE_USER;
        }
        if ($this->_params['featureDeleteUser']) {
            $feat |= self::FEATURE_DELETE_USER;
        }
        return $feat;
    }

    /**
     * @inheritdoc
     */
    public function userExists($login)
    {
        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }
        // see if the user exists into the ldap directory
        $this->latestExistingUser = $this->searchLdapUserAttributes($connectAdmin, $login);
        ldap_close($connectAdmin);
        return ($this->latestExistingUser !== false);
    }

    /**
     * @inheritdoc
     */
    public function userWithEmailExists($email)
    {
        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }
        // see if the user exists into the ldap directory
        $this->latestExistingUser = $this->searchLdapUserAttributes($connectAdmin, $email, true);
        ldap_close($connectAdmin);
        if ($this->latestExistingUser) {
            return $this->latestExistingUser->getLogin();
        }
        return false;
    }

    public function getUser($login)
    {
        if ($this->latestExistingUser && $this->latestExistingUser->getLogin() == $login) {
            return new AuthUser($login, $this->latestExistingUser->userAttributes);
        }

        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return null;
        }
        // see if the user exists into the ldap directory
        $result = $this->searchLdapUserAttributes($connectAdmin, $login);
        ldap_close($connectAdmin);

        if (!$result) {
            return null;
        }

        return new AuthUser($login, $result->userAttributes);
    }

    public function updateUser($login, $attributes)
    {
        $ldapAttributes = null;
        if ($this->latestExistingUser && $this->latestExistingUser->getLogin() == $login) {
            $ldapAttributes = $this->latestExistingUser;
        }
        $this->latestExistingUser = null;

        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return ;
        }

        if (!$ldapAttributes) {
            $ldapAttributes = $this->searchLdapUserAttributes($connectAdmin, $login);
            if (!$ldapAttributes) {
                return null;
            }
        }

        ldap_mod_replace($connectAdmin, $ldapAttributes->dn, $attributes);
        ldap_close($connectAdmin);
    }

    /**
     * @inheritdoc
     */
    public function createUser($login, $password, $email, $name = '')
    {
        $this->latestExistingUser = null;

        if (!$this->_params['featureCreateUser']) {
            return false;
        }

        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }

        $ldapAttributes = $this->createLdapAttributes(array(
            'email' => $email,
            'realname' => $name ?: $login
        ));

        foreach($this->_params['newUserLdapAttributes'] as $attr=>$val) {
            if ($val[0] == '%') {
                $val = str_replace(array('%%PASSWORD%%', '%%LOGIN%%', '%%REALNAME%%'),
                    array($this->hashPassword($password), $login, $name ?: $login),
                    $val
                );
            }
            $ldapAttributes[$attr] = $val;
        }

        $dn = $this->createDn($this->_params['newUserDN'], $login, $ldapAttributes);
        $result = @ldap_add ($connectAdmin , $dn, $ldapAttributes);
        if (!$result) {
            //echo('authloginpass ldap: error when trying to create a user "'.$login.'": '.ldap_errno($connectAdmin).':'.ldap_error($connectAdmin));
            jLog::log('authloginpass ldap: error when trying to create a user "'.$login.'": '.ldap_errno($connectAdmin).':'.ldap_error($connectAdmin), 'auth');
        }
        ldap_close($connectAdmin);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($login)
    {
        if (!$this->_params['featureDeleteUser']) {
            return false;
        }
        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }

        if ($this->latestExistingUser && $this->latestExistingUser->getLogin() == $login) {
            $ldapAttributes = $this->latestExistingUser;
        }
        else {
            $ldapAttributes  = $this->searchLdapUserAttributes($connectAdmin, $login);
        }
        $this->latestExistingUser = null;

        if (!$ldapAttributes) {
            ldap_close($connectAdmin);
            return true;
        }

        $user = new AuthUser($login, $ldapAttributes->userAttributes);

        $result = ldap_delete($connectAdmin, $ldapAttributes->dn);
        ldap_close($connectAdmin);

        if (!$result) {
            return false;
        }
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function changePassword($login, $newpassword)
    {
        if (!$this->_params['featureChangePassword']) {
            return false;
        }

        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }

        if ($this->latestExistingUser && $this->latestExistingUser->getLogin() == $login) {
            $ldapAttributes = $this->latestExistingUser;
        }
        else {
            $ldapAttributes  = $this->searchLdapUserAttributes($connectAdmin, $login);
        }
        $this->latestExistingUser = null;

        if (!$ldapAttributes) {
            ldap_close($connectAdmin);
            return true;
        }

        $entry = array(
            "userPassword" => $this->hashPassword($newpassword)
        );

        if (ldap_modify($connectAdmin, $ldapAttributes->dn, $entry) === false) {
            jLog::log('authloginpass ldap: error when trying to change password of user "'.$login.'": '.ldap_errno($connectAdmin).':'.ldap_error($connectAdmin), 'auth');
            ldap_close($connectAdmin);
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function verifyAuthentication($login, $password)
    {
        if (trim($password) == '' || trim($login) == '') {
            // we don't want Unauthenticated Authentication
            // and Anonymous Authentication
            // https://tools.ietf.org/html/rfc4513#section-5.1
            return false;
        }

        $connectAdmin = $this->_bindLdapAdminUser();
        if (!$connectAdmin) {
            return false;
        }

        if ($this->latestExistingUser && $this->latestExistingUser->getLogin() == $login) {
            $ldapAttributes = $this->latestExistingUser;
        }
        else {
            $ldapAttributes  = $this->searchLdapUserAttributes($connectAdmin, $login);
        }
        $this->latestExistingUser = null;

        if (!$ldapAttributes) {
            jLog::log('authloginpass ldap: user '.$login.' not found into the ldap', 'auth');
            ldap_close($connectAdmin);
            return true;
        }

        $connect = $this->_getLinkId();
        if (!$connect) {
            jLog::log('authloginpass ldap: impossible to connect to ldap', 'auth');
            ldap_close($connectAdmin);
            return false;
        }

        // authenticate user. let's try with all configured DN
        $userDn = $this->bindUser($connect, $ldapAttributes->ldapAttributes, $login, $password);
        ldap_close($connect);

        if ($userDn === false) {
            jLog::log('authloginpass ldap: cannot authenticate to ldap with given bindUserDN for the login ' . $login. '. Wrong DN or password', 'auth');
            foreach ($this->bindUserDnTries as $erroMessage) {
                jLog::log($erroMessage, 'auth');
            }
            ldap_close($connectAdmin);
            return false;
        }

        // retrieve the user group (if relevant)
        $userGroups = $this->searchUserGroups($connectAdmin, $userDn, $ldapAttributes->ldapAttributes, $login);
        ldap_close($connectAdmin);
        if ($userGroups !== false) {
            // the user is at least in a ldap group, so we synchronize ldap groups
            $this->synchronizeAclGroups($login, $userGroups);
        }

        $user = new AuthUser($login, $ldapAttributes->userAttributes);
        return $user;
    }

    public function getUsersList()
    {
        $this->latestExistingUser = null;
        throw new \Exception('not implemented');
    }

    protected function synchronizeAclGroups($login, $userGroups)
    {
        if ($this->_params['aclAdapterClass'] == '') {
            throw new \Exception('cannot synchronize acl groups, class is missing into aclAdapterClass');
        }

        $class = $this->_params['aclAdapterClass'];
        /** @var \Jelix\Authentication\LoginPass\LdapAclAdapterInterface $aclAdapter */
        $aclAdapter = new $class();
        $aclAdapter->synchronizeAclGroups($login, $userGroups, $this->_params['searchGroupKeepUserInDefaultGroups']);
    }

    /**
     * @return ldapBackendUserLdapAttributes|false   ldap & user attributes or false if not found
     */
    protected function searchLdapUserAttributes($connect, $login, $loginIsEmail = false)
    {
        $searchAttributes = array_keys($this->_params['searchAttributes']);
        $filters = array();

        $filterStr = 'searchUserFilter';
        $filterTokens = array('%%LOGIN%%', '%%USERNAME%%'); // USERNAME deprecated

        if ($loginIsEmail) {
            $filterStr = 'searchUserByEmailFilter';
            $filterTokens = '%%EMAIL%%';
        }

        foreach ($this->_params[$filterStr] as $searchUserFilter) {
            $filter = str_replace(
                $filterTokens,
                $login,
                $searchUserFilter
            );
            $search = @ldap_search(
                $connect,
                $this->_params['searchUserBaseDN'],
                $filter,
                $searchAttributes
            );
            if (!$search) {
                $this->logLdapError($connect, 'ldap error during search of the user "'.$login.'" with the filter "'.$filter.'"');
            }
            else if (($entry = @ldap_first_entry($connect, $search))) {
                $dn = ldap_get_dn($connect, $entry);
                $attributes = ldap_get_attributes($connect, $entry);
                return $this->readLdapAttributes($dn, $attributes);
            }
            else {
                $filters[] = $filter;
            }
        }
        jLog::log('authloginpass: ldap user "'.$login.'" not found with the filters :"'.implode('", "', $filters).'"', 'auth');
        return false;
    }

    protected $bindUserDnTries = array();

    protected function bindUser($connect, $userLdapAttributes, $login, $password)
    {
        $bind = false;
        $this->bindUserDnTries = array();
        $dnList = $this->getPossibleUserDn($login, $userLdapAttributes);
        foreach ($dnList as $realDn) {
            $bind = @ldap_bind($connect, $realDn, $password);
            if ($bind) {
                break;
            } else {
                $this->bindUserDnTries[] = $this->getLdapError($connect, 'tried to connect with "'.$realDn.'"');;
            }
        }
        return ($bind ? $realDn : false);
    }

    protected function getPossibleUserDn($login, $userLdapAttributes)
    {
        $dnList = array();
        foreach ($this->_params['bindUserDN'] as $dn) {
            $realDn = $this->createDn($dn, $login, $userLdapAttributes);
            if ($realDn !== false) {
                $dnList[] = $realDn;
            }
        }
        return $dnList;
    }

    protected function createDn($dnPattern, $login, $userLdapAttributes)
    {
        if (preg_match('/^\\$\w+$/', trim($dnPattern))) {
            $dnAttribute = substr($dnPattern, 1);
            if (isset($userLdapAttributes[$dnAttribute])) {
                $realDn = $userLdapAttributes[$dnAttribute];
            } else {
                jLog::log('authloginpass ldap bindUser notice: attribute '.$dnAttribute.' not found into user attributes', 'auth');
                return false;
            }
        } elseif (preg_match_all('/(\w+)=%\?%/', $dnPattern, $m)) {
            $realDn = $dnPattern;
            foreach ($m[1] as $k => $attr) {
                if (isset($userLdapAttributes[$attr])) {
                    $realDn = str_replace($m[0][$k], $attr.'='.$userLdapAttributes[$attr], $realDn);
                } else {
                    jLog::log('authloginpass ldap bindUser notice: attribute '.$attr.' not found into user attributes', 'auth');
                    return false;
                }
            }
        } else {
            $realDn = str_replace(
                array('%%LOGIN%%', '%%USERNAME%%'), // USERNAME deprecated
                $login,
                $dnPattern
            );
        }
        return $realDn;
    }

    /**
     * Returns ldap attributes as an associative array, and build
     * a list of object attributes matching the mapping given into searchAttributes
     *
     * @param array $origLdapAttributes list of values provided by ldap_get_attributes
     * @return ldapBackendUserLdapAttributes
     */
    protected function readLdapAttributes($dn, $origLdapAttributes)
    {
        $mapping = $this->_params['searchAttributes'];
        $attrs = new ldapBackendUserLdapAttributes();
        foreach ($origLdapAttributes as $ldapAttr => $attr) {
            if (isset($attr['count']) && $attr['count'] > 0) {
                if ($attr['count'] > 1) {
                    $val = array_shift($attr);
                } else {
                    $val = $attr[0];
                }
                $attrs->ldapAttributes[$ldapAttr] = $val;
                if (isset($mapping[$ldapAttr])) {
                    $objAttr = $mapping[$ldapAttr];
                    unset($mapping[$ldapAttr]);
                    if ($objAttr != '') {
                        $attrs->userAttributes[$objAttr] = $val;
                    }
                }
            }
        }

        foreach ($mapping as $ldapAttr => $objAttr) {
            if ($objAttr != '' && !isset($attrs->userAttributes[$objAttr])) {
                $attrs->userAttributes[$objAttr] = '';
            }
        }

        $attrs->dn = $dn;

        return $attrs;
    }

    /**
     * Construct a list of ldap attributes, corresponding to object attributes.
     *
     * It uses the searchAttributes configuration parameter.
     *
     * @param array $values list of object attributes
     * @return array list of ldap attributes
     */
    protected function createLdapAttributes($values)
    {
        $ldapAttributes = array();
        foreach($this->_params['searchAttributes'] as $ldapAttr => $attr) {
            if (isset($values[$attr])) {
                $ldapAttributes[$ldapAttr] = $values[$attr];
            }
        }
        return $ldapAttributes;
    }


    protected function searchUserGroups($connect, $userDn, $userLdapAttributes, $login)
    {
        // Do not search for groups if no group filter passed
        // Usefull to forbid the driver to sync groups from LDAP and loose all related groups for the user
        if ($this->_params['searchGroupFilter'] == '') {
            return false;
        }

        $searchStr = array_keys($userLdapAttributes);
        $searchStr[] = 'USERDN';
        $searchStr[] = 'LOGIN';
        $searchStr[] = 'USERNAME'; // USERNAME deprecated
        $searchStr = array_map(function ($val) {
            return '%%'.$val.'%%';
        }, $searchStr);
        $values = array_values($userLdapAttributes);
        // escape parenthesis
        $values = array_map(function ($val) {
            return str_replace(
                array('(', ')'),
                array('\\(', '\\)'),
                $val
            );
        }, $values);
        $values[] = $userDn;
        $values[] = $login;
        $values[] = $login;

        $filter = str_replace(
            $searchStr,
            $values,
            $this->_params['searchGroupFilter']
        );
        $grpProp = $this->_params['searchGroupProperty'];

        $groups = array();
        $search = ldap_search(
            $connect,
            $this->_params['searchGroupBaseDN'],
            $filter,
            array($grpProp)
        );
        if ($search) {
            $entry = ldap_first_entry($connect, $search);
            if ($entry) {
                do {
                    $attributes = ldap_get_attributes($connect, $entry);
                    if (isset($attributes[$grpProp]) && $attributes[$grpProp]['count'] > 0) {
                        $groups[] = $attributes[$grpProp][0];
                    }
                } while ($entry = ldap_next_entry($connect, $entry));
            }
            else {
                jLog::log('authloginpass ldap: no groups found for the user  "'.$login.'", with the searchGroupFilter query "'.$filter.'"', 'auth');
            }
        }
        else {
            $this->logLdapError($connect, 'ldap error during group search for "'.$login.'", with the searchGroupFilter query "'.$filter.'"');
        }
        return $groups;
    }

    /**
     * open the connection to the ldap server
     */
    protected function _getLinkId()
    {
        if ($connect = @ldap_connect($this->uriConnect)) {
            //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, $this->_params['protocolVersion']);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if ($this->_params['tlsMode'] == 'starttls') {
                if (!@ldap_start_tls($connect)) {
                    $this->logLdapError($connect, 'connection error: impossible to start TLS connection to '.$this->uriConnect);
                    return false;
                }
            }
            return $connect;
        }
        jLog::log('authloginpass: ldap error, bad syntax in the given uri "'.$this->uriConnect.'"', 'auth');
        return false;
    }

    /**
     * open the connection to the ldap server
     * and bind to the admin user
     * @return resource|false the ldap connection
     */
    protected function _bindLdapAdminUser()
    {
        $connect = $this->_getLinkId();
        if (!$connect) {
            jLog::log('authloginpass ldap: impossible to connect to ldap', 'auth');
            return false;
        }

        if ($this->_params['adminUserDn'] == '') {
            $bind = @ldap_bind($connect);
        } else {
            $bind = @ldap_bind($connect, $this->_params['adminUserDn'], $this->_params['adminPassword']);
        }
        if (!$bind) {
            if ($this->_params['adminUserDn'] == '') {
                jLog::log('authloginpass ldap: impossible to authenticate to ldap as anonymous admin user', 'auth');
            } else {
                jLog::log('authloginpass ldap: impossible to authenticate to ldap with admin user '.$this->_params['adminUserDn'], 'auth');
            }
            ldap_close($connect);
            return false;
        }
        return $connect;
    }


    protected function hashPassword($password)
    {
        switch($this->_params['passwordLdapHashAlgo']) {
            case 'SHA':
                return "{SHA}" . base64_encode(pack( "H*", sha1( $password )));
            case 'SSHA':
                $salt = $this->generateSalt();
                return "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
            case 'MD5':
                return "{MD5}".base64_encode(pack( "H*", md5( $password )));
            case 'SMD5':
                $salt = $this->generateSalt();
                return "{SMD5}" . base64_encode(pack("H*", md5($password . $salt)) . $salt);
            case '':
                return $password;
            default:
                throw new \Exception("ldap hash password: Unknown algorithm '".$this->_params['passwordLdapHashAlgo']."'");
        }
    }

    protected function generateSalt() {
        $generator = (new \RandomLib\Factory)->getMediumStrengthGenerator();
        $length = $this->_params['passwordLdapSaltLength'];
        $salt = $generator->generateString($length, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        return $salt;
    }

    protected function getLdapError($connect, $contextMessage)
    {
        $message = "ldapdao error: $contextMessage \n";
        $message .= "\tldap error " . ldap_errno($connect) . ':' . ldap_error($connect);
        if (@ldap_get_option($connect, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diagnostic)) {
            $message .= "\n\t" . $diagnostic;
        }
        return $message;
    }

    protected function logLdapError($connect, $contextMessage)
    {
        jLog::log($this->getLdapError($connect, $contextMessage), 'auth');
    }
}


class ldapBackendUserLdapAttributes {

    public $dn = '';

    public $ldapAttributes = array();

    public $userAttributes = array();

    public function getLogin()
    {
        if (isset($this->userAttributes[AuthUser::ATTR_LOGIN])) {
            return $this->userAttributes[AuthUser::ATTR_LOGIN];
        }
        return null;
    }

    public function getEmail()
    {
        if (isset($this->userAttributes[AuthUser::ATTR_EMAIL])) {
            return $this->userAttributes[AuthUser::ATTR_EMAIL];
        }
        return null;
    }
}
