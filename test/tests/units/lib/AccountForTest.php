<?php



class AccountForTest implements \Jelix\Authentication\Core\AuthSession\UserAccountInterface
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
