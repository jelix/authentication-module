How to manage an account
========================

The account module provides a component to load account information about the user
which is authenticated, during the get_account step of the workflow. You don't have to do something.

However, if you don't want to use the account module, and if you want to provide your 
own account management, you need to "plug" this management to the authentication system.

Here is how.

The account object
------------------

When the application supports accounts, the authentication system stores an
object representing the account, into the session. It should implement the
interface `Jelix\Authentication\Core\AuthSession\UserAccountInterface`.
It declares three methods to retrieve the account id of the user, his name and his email.
Here is an example of the object you should implement:

```php
class MyAccountImplementation implements \Jelix\Authentication\Core\AuthSession\UserAccountInterface
{
    protected $accountId;

    protected $username;

    protected $realname;

    protected $email;

    function __construct($accountId, $username, $realname, $email)
    {
        $this->accountId = $accountId;
        $this->username = $username;
        $this->realname = $realname;
        $this->email = $email;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getRealName()
    {
        return $this->realname;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
```

This object could also implement other methods you may need into your
application.

The adapter to set the account into the session
------------------------------------------------

To give this object to the authentication system during the authentication
process, you must create an object implementing the  `Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface` interface.

Its class should be indicated into the `accountWorkflowAdapter` configuration parameter of 
the `authentication` section.

The object is called during the `get_account` step of the authentication workflow.

An example of such object:

```php
use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;

class myGetAccountObject implements AccountWorkflowAdapterInterface
{

    public function searchAccount(WorkflowState $workflowState) : int
    {
        $idpId = $workflowState->getIdpId();
        $authUser = $workflowState->getTemporaryUser();
        
        // your object allowing to search in accounts
        $loader = new MyAccountLoader();
        // search 
        $account = $loader->searchAccount($authUser->getUserId(), $idpId);
        if ($account) {
            // an account is found, set it to the authenticated user stored into the session
            $authUser->setAccount($account);
            return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND;
        }
        // the account is not found, we should indicate it. 
        return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_NOT_FOUND;
    }

    public function getActionToCreateAccount() : ?WorkflowAction
    {
        return null;
    }
}

```

If there is an account, call the method `setAccount($account)` on the `AuthUser` object.

If there is no account, you have three choices, depending on the behavior you want to have:

- The authentication must fail, so you have to return `AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_NOT_FOUND` or throw a `StepException` exception. The user will return to the authentication form.
  The error message indicated to `StepException` may be displayed (this is the case
  with the authentication form of the authloginpass module).
- Or you create the account with minimal information, and the user can continue the authentication. See below.
- Or you want the user to fill a form to create the account, so
  - you return `AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_TO_CREATE`
  - you must implement the controller allowing to display and save the form
  - you must implement the `getActionToCreateAccount()` method on your `myGetAccountObject()`.
    See below.

After the account is found or created, the user will be redirected to the next authentication step like 2FA or access validation.

Setup the workflow to create an account automatically
----------------------------------------------------

Your account workflow adapter has to be changed a bit.
In the `searchAccount` method, create account data, then set the account object with `setAccount($account)` on the `AuthUser` object. Then return `AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_CREATED`

```php
use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;

class myGetAccountObject implements AccountWorkflowAdapterInterface
{

    public function searchAccount(WorkflowState $workflowState) : int
    {
        $idpId = $workflowState->getIdpId();
        $authUser = $workflowState->getTemporaryUser();
        
        // your object allowing to search in accounts
        $loader = new MyAccountLoader();
        // search 
        $account = $loader->searchAccount($authUser->getUserId(), $idpId);
        if ($account) {
            // an account is found, set it to the authenticated user stored into the session
            $authUser->setAccount($account);
            return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND;
        }
        
        // let's create an account with an object you have to create, for example MyAccountCreator
        $creator = new MyAccountCreator();
        $account = $creator->createAccountFromUser($authUser);
        $authUser->setAccount($account);

        // the account is created, we should indicate it. 
        return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_CREATED;
    }

    public function getActionToCreateAccount() : ?WorkflowAction
    {
        return null;
    }
}

```


Setup the workflow to create an account with a form
----------------------------------------------------

You want the user to create an account with a form. It could be a form to fill with personal data.
For example: name, preferences, and all information not provided by the AuthUser object.

Your account workflow adapter should be changed:

- `searchAccount` must return `AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_TO_CREATE`
- the `getActionToCreateAccount()` method must return the action to redirect to, that will display
  and save the form to create the account 
- you must implement the controller for this action


```php
use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;

class myGetAccountObject implements AccountWorkflowAdapterInterface
{

    public function searchAccount(WorkflowState $workflowState) : int
    {
        $idpId = $workflowState->getIdpId();
        $authUser = $workflowState->getTemporaryUser();
        
        // your object allowing to search in accounts
        $loader = new MyAccountLoader();
        // search 
        $account = $loader->searchAccount($authUser->getUserId(), $idpId);
        if ($account) {
            // an account is found, set it to the authenticated user stored into the session
            $authUser->setAccount($account);
            return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND;
        }
        // the account is not found, we should create it. 
        return AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_TO_CREATE;
    }

    public function getActionToCreateAccount() : ?WorkflowAction
    {
        // the url to redirect immediately, to fill the account form
        $formUrl = jUrl::get('myaccountmodule~createaccount:form');

        // add this workflow action, by indicating the possible other actions
        // after filling the form
        return new WorkflowAction($formUrl, [
            new \jSelectorActFast('classic', 'myaccountmodule', 'createaccount:save'),
        ]);
    }
}
```

Now you create the controller.

In each action of the account creation process, you should check that the
authentication workflow is alive, and you could then retrieve user data
to do the process.

At the end of the account creation process, you should give your account
object to the authentication system with `$workflow->setNewAccountToUser($account);`.


Here is an example of the controller:

```php

class createaccountCtrl extends \jController
{

    function form()
    {
    
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            // no workflow, you should stop here: display an error message
            // or redirect the user to the homepage or elsewhere.
            
            return ...;
        }
        
        if (!$workflow->isCurrentStep('create_account')) {
            // this is not the right step, redirect the user to the right step
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        $resp = $this->getResponse('html');
        // we display the form
        // ..
        
        return $resp;
    }

    function save()
    {
        // verify we are in the authentication workflow
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            return ...;
        }
        
        if (!$workflow->isCurrentStep('create_account')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        // here we check the form...
        $formOk = ...;
        
        if ($formOk) {
            // the content of the form is ok
            
            // here we create the account
            $account = ...;
            // save the account here
            // ...
            
            // now we can indicate the account to the authenticated user
            $workflow->setNewAccountToUser($account);

            // we can now redirect the user to the next step of the authentication
            return $this->redirectToUrl($workflow->getNextAuthenticationUrl());
        }
        
        // bad form, we return to the form
        
        return $this->redirect('createaccount:form')
    }
}
```

