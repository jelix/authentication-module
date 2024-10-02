<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 * @link      https://jelix.org
 * @licence   MIT
 */


namespace Jelix\Authentication\RequestConfirmation;

class UserRequest
{

    const TYPE_RECOVERY_ACCOUNT = 'RECOVERY_ACCOUNT';
    const TYPE_EMAIL_CHANGE = 'EMAIL_CHANGE';

    const STATUS_PENDING = 'PENDING';
    const STATUS_CHECKED = 'CHECKED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @var \jDaoRecordBase
     */
    protected $daoRecord = null;

    protected $clearCode = null;

    /**
     * @param \jDaoRecordBase $record
     */
    public function __construct(\jDaoRecordBase $record, $clearCode=null)
    {
        $this->daoRecord = $record;
        $this->clearCode = $clearCode;
    }


    public function getReadableRequestCode()
    {
        return $this->clearCode;
    }

    public function getHashedRequestCode()
    {
        return $this->daoRecord->req_code;
    }

    public function getLogin()
    {
        return $this->daoRecord->req_login;
    }

    public function getEmail()
    {
        return $this->daoRecord->req_email;
    }

    public function getRequestId()
    {
        return $this->daoRecord->req_id;
    }

    public function hasStatus($status)
    {
        return $this->daoRecord->req_status == $status;
    }

    public function saveAsChecked()
    {
        if ($this->daoRecord->req_status == self::STATUS_PENDING) {
            $this->daoRecord->req_status = self::STATUS_CHECKED;
            $this->daoRecord->save();
        }
    }

    public function saveAsConfirmed()
    {
        if ($this->daoRecord->req_status == self::STATUS_CHECKED) {
            $this->daoRecord->req_status = self::STATUS_CONFIRMED;
            $this->daoRecord->req_confirmation_date = date('Y-m-d H:i');
            $this->daoRecord->req_code = '';
            $this->daoRecord->save();
        }
    }

}