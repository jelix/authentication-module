<?php



class AccountForTest implements \Jelix\Authentication\Core\AuthSession\UserAccountInterface
{
    protected $accountId;

    protected $name;

    protected $email;

    function __construct($accountId, $username, $email)
    {
        $this->accountId = $accountId;
        $this->name = $username;
        $this->email = $email;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getUserName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
