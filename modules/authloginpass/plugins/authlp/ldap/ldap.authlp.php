<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
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

    protected $uriConnect = '';

    /**
     * @inheritdoc
     */
    public function __construct($params, $profile = null)
    {
        if (!extension_loaded('ldap')) {
            throw new jException('authloginpass~ldap.error.extension.unloaded');
        }
        parent::__construct($params);

        if ($profile === null) {
            if (!isset($this->_params['ldapprofile']) || $this->_params['ldapprofile'] == '') {
                throw new jException('authloginpass~ldap.error.ldap.profile.missing');
            }

            $profile = jProfiles::get('ldap', $this->_params['ldapprofile']);
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
            'searchGroupFilter' => '',
            'searchGroupKeepUserInDefaultGroups' => true,
            'searchGroupProperty' => '',
            'searchGroupBaseDN' => '',
            'featureCreateUser' => true,
            'featureDeleteUser' => true,
            'featureChangePassword' => true,
        );

        // iterate each default parameter and apply it to actual params if missing in $params.
        foreach ($_default_params as $name => $value) {
            if (!isset($this->_params[$name]) || $this->_params[$name] == '') {
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

        if (!isset($this->_params['searchUserFilter']) || $this->_params['searchUserFilter'] == '') {
            throw new jException('authloginpass~ldap.error.searchUserFilter.missing');
        }
        if (!is_array($this->_params['searchUserFilter'])) {
            $this->_params['searchUserFilter'] = array($this->_params['searchUserFilter']);
        }

        if (!isset($this->_params['bindUserDN']) || $this->_params['bindUserDN'] == '') {
            throw new jException('authloginpass~ldap.error.bindUserDN.missing');
        }
        if (!is_array($this->_params['bindUserDN'])) {
            $this->_params['bindUserDN'] = array($this->_params['bindUserDN']);
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
        foreach ($this->_params['searchUserFilter'] as $searchUserFilter) {
            $filter = str_replace(
                array('%%LOGIN%%', '%%USERNAME%%'), // USERNAME deprecated
                $login,
                $searchUserFilter
            );
            $search = ldap_search(
                $connectAdmin,
                $this->_params['searchUserBaseDN'],
                $filter,
                array('dn')
            );
            if ($search && ($entry = ldap_first_entry($connectAdmin, $search))) {
                return true;
            }
        }
        return false;
    }

    /**
     *  @inheritdoc
     */
    public function createUser($login, $password, $email, $name = '')
    {
        if (!$this->_params['featureCreateUser']) {
            return false;
        }
// TODO
        //return true;
        return false;
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($login)
    {
        if (!$this->_params['featureDeleteUser']) {
            return false;
        }
        /*
        $user = new AuthUser($login, array(
            AuthUser::ATTR_NAME =>$name,
            AuthUser::ATTR_EMAIL =>$email,
        );
        return $user;
        */
        return false;
    }

    /**
     * @inheritdoc
     */
    public function changePassword($login, $newpassword)
    {
        if (!$this->_params['featureChangePassword']) {
            return false;
        }
// TODO
        // return true;
        return false;
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

        // see if the user exists into the ldap directory
        $userAttributes = array();
        $userLdapAttributes = $this->searchLdapUserAttributes($connectAdmin, $login, $userAttributes);
        if ($userLdapAttributes === false) {
            jLog::log('authloginpass ldap: user '.$login.' not found into the ldap', 'auth');
            return false;
        }

        $connect = $this->_getLinkId();
        if (!$connect) {
            jLog::log('authloginpass ldap: impossible to connect to ldap', 'auth');
            return false;
        }
        // authenticate user. let's try with all configured DN
        $userDn = $this->bindUser($connect, $userLdapAttributes, $login, $password);
        ldap_close($connect);

        if ($userDn === false) {
            jLog::log('authloginpass ldap: cannot authenticate to ldap with given bindUserDN for the login ' . $login. '. Wrong DN or password', 'auth');
            foreach ($this->bindUserDnTries as $dn) {
                jLog::log('authloginpass ldap:  tried to connect with bindUserDN=' . $dn, 'auth');
            }
            return false;
        }

        // retrieve the user group (if relevant)
        $userGroups = $this->searchUserGroups($connectAdmin, $userDn, $userLdapAttributes, $login);
        ldap_close($connectAdmin);
        if ($userGroups !== false) {
            // the user is at least in a ldap group, so we synchronize ldap groups
            // with jAcl2 groups
            $this->synchronizeAclGroups($login, $userGroups);
        }

        $user = new AuthUser($login, $userAttributes);
        return $user;
    }

    protected function synchronizeAclGroups($login, $userGroups)
    {
        if ($this->_params['searchGroupKeepUserInDefaultGroups']) {
            // Add default groups
            $gplist = jDao::get('jacl2db~jacl2group', 'jacl2_profile')
                ->getDefaultGroups();
            foreach ($gplist as $group) {
                $idx = array_search($group->name, $userGroups);
                if ($idx === false) {
                    $userGroups[] = $group->name;
                }
            }
        }

        // we know the user group: we should be sure it is the same in jAcl2
        $gplist = jDao::get('jacl2db~jacl2groupsofuser', 'jacl2_profile')
            ->getGroupsUser($login);
        $groupsToRemove = array();
        foreach ($gplist as $group) {
            if ($group->grouptype == 2) { // private group
                continue;
            }
            $idx = array_search($group->name, $userGroups);
            if ($idx !== false) {
                unset($userGroups[$idx]);
            } else {
                $groupsToRemove[] = $group->name;
            }
        }
        foreach ($groupsToRemove as $group) {
            jAcl2DbUserGroup::removeUserFromGroup($login, $group);
        }
        foreach ($userGroups as $newGroup) {
            if (jAcl2DbUserGroup::getGroup($newGroup)) {
                jAcl2DbUserGroup::addUserToGroup($login, $newGroup);
            }
        }
    }

    /**
     * @return string[]|false  ldap attributes or false if not found
     */
    protected function searchLdapUserAttributes($connect, $login, &$userAttributes)
    {
        $searchAttributes = array_keys($this->_params['searchAttributes']);
        foreach ($this->_params['searchUserFilter'] as $searchUserFilter) {
            $filter = str_replace(
                array('%%LOGIN%%', '%%USERNAME%%'), // USERNAME deprecated
                $login,
                $searchUserFilter
            );
            $search = ldap_search(
                $connect,
                $this->_params['searchUserBaseDN'],
                $filter,
                $searchAttributes
            );
            if ($search && ($entry = ldap_first_entry($connect, $search))) {
                $attributes = ldap_get_attributes($connect, $entry);
                return $this->readLdapAttributes($attributes, $userAttributes);
            }
        }
        return false;
    }

    protected $bindUserDnTries = array();

    protected function bindUser($connect, $userAttributes, $login, $password)
    {
        $bind = false;
        $this->bindUserDnTries = array();
        foreach ($this->_params['bindUserDN'] as $dn) {
            if (preg_match('/^\\$\w+$/', trim($dn))) {
                $dnAttribute = substr($dn, 1);
                if (isset($userAttributes[$dnAttribute])) {
                    $realDn = $userAttributes[$dnAttribute];
                } else {
                    continue;
                }
            } elseif (preg_match_all('/(\w+)=%\?%/', $dn, $m)) {
                $realDn = $dn;
                foreach ($m[1] as $k => $attr) {
                    if (isset($userAttributes[$attr])) {
                        $realDn = str_replace($m[0][$k], $attr.'='.$userAttributes[$attr], $realDn);
                    } else {
                        continue 2;
                    }
                }
            } else {
                $realDn = str_replace(
                    array('%%LOGIN%%', '%%USERNAME%%'), // USERNAME deprecated
                    $login,
                    $dn
                );
            }
            $bind = @ldap_bind($connect, $realDn, $password);
            if ($bind) {
                break;
            } else {
                jLog::log('authloginpass ldap: error when trying to connect with '.$realDn.': '.ldap_errno($connect).':'.ldap_error($connect), 'auth');
                $this->bindUserDnTries[] = $realDn;
            }
        }
        return ($bind ? $realDn : false);
    }


    protected function readLdapAttributes($attributes, &$userAttributes)
    {
        $mapping = $this->_params['searchAttributes'];
        $ldapAttributes = array();
        foreach ($attributes as $ldapAttr => $attr) {
            if (isset($attr['count']) && $attr['count'] > 0) {
                if ($attr['count'] > 1) {
                    $val = array_shift($attr);
                } else {
                    $val = $attr[0];
                }
                $ldapAttributes[$ldapAttr] = $val;
                if (isset($mapping[$ldapAttr])) {
                    $objAttr = $mapping[$ldapAttr];
                    unset($mapping[$ldapAttr]);
                    if ($objAttr != '') {
                        $userAttributes[$objAttr] = $val;
                    }
                }
            }
        }

        foreach ($mapping as $ldapAttr => $objAttr) {
            if ($objAttr != '' && !isset($userAttributes[$objAttr])) {
                $userAttributes[$objAttr] = '';
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
        }
        return $groups;
    }

    /**
     * open the connection to the ldap server
     */
    protected function _getLinkId()
    {
        if ($connect = ldap_connect($this->uriConnect)) {
            //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, $this->_params['protocolVersion']);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if ($this->_params['tlsMode'] == 'starttls') {
                if (!@ldap_start_tls($connect)) {
                    jLog::log('authloginpass ldap: connection error: impossible to start TLS connection: '.ldap_errno($connect).':'.ldap_error($connect), 'error');
                    return false;
                }
            }
            return $connect;
        }
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
            $bind = ldap_bind($connect);
        } else {
            $bind = ldap_bind($connect, $this->_params['adminUserDn'], $this->_params['adminPassword']);
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

}
