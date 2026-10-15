<?php



class AccountForTest implements \Jelix\Authentication\Core\AuthSession\UserAccountInterface
{
    protected $accountId;

    protected $username;

    protected $displayName;

    protected $email;

    public $data = null;

    function __construct($accountId, $username, $displayName, $email)
    {
        $this->accountId = $accountId;
        $this->username = $username;
        $this->displayName = $displayName;
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

    public function getDisplayName()
    {
        return $this->displayName;
    }


    public function getEmail()
    {
        return $this->email;
    }
}
