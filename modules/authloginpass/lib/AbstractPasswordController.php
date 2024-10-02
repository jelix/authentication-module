<?php
/**
 * @author    Laurent Jouanneau <laurent@jelix.org>
 * @copyright 2019-2024 Laurent Jouanneau
 *
 * @link      https://jelix.org
 * @license   MIT
 */

namespace Jelix\Authentication\LoginPass;

abstract class AbstractPasswordController extends AbstractController
{
    // public $pluginParams = array(
    //     '*' => array('auth.required' => false),
    // );

    protected $pagePasswordTitle = 'password.page.title';

    protected $formPasswordTitle = 'password.form.change.title';

    protected $formPasswordTpl = 'password_reset_change';

    protected $actionController = 'password_reset';

    protected $forRegistration = false;

    /**
     * @var PasswordReset
     */
    protected $passwordReset;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->passwordReset = new PasswordReset(
            $this->forRegistration,
            null,
            $this->config,
            \jApp::services()->eventDispatcher()

        );
    }

    protected function _check()
    {
        if (\jAuthentication::isCurrentUserAuthenticated()) {
            return $this->noaccess('no_access_auth');
        }

        if ($this->passwordReset->isPasswordResetEnabled()) {
            return null;
        }
        return $this->notavailable();
    }


}
