<?php

use Jelix\Authentication\RequestConfirmation\RequestException;
use Jelix\Authentication\RequestConfirmation\UserRequest;
use PHPUnit\Framework\TestCase;
use Jelix\Authentication\RequestConfirmation\Requests;

class confirmationRequestTest extends TestCase
{

    protected static $reqId;
    protected static $reqCode;
    protected static $reqId2;
    protected static $reqCode2;

    public function testCreateConfirmationRequest()
    {
        \jDb::getConnection()->exec('DELETE FROM auth_user_requests');
        $requests = new Requests();
        $ttl =  new \DateInterval('PT5M');
        $userReq = $requests->createRequest(
            UserRequest::TYPE_RECOVERY_ACCOUNT,
            'robert',
            'robert@example.com',
            $ttl);
        self::$reqId = $userReq->getRequestId();
        self::$reqCode = $userReq->getReadableRequestCode();
        $this->assertNotEmpty($userReq->getRequestId());
        $this->assertNotEmpty($userReq->getHashedRequestCode());
        $this->assertEquals('robert', $userReq->getLogin());
        $this->assertEquals('robert@example.com', $userReq->getEmail());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_PENDING));

    }

    /**
     * @depends testCreateConfirmationRequest
     */
    public function testCreateCancelledConfirmationRequest()
    {
        $requests = new Requests();
        $userReq = $requests->createCancelledRequest(UserRequest::TYPE_RECOVERY_ACCOUNT);
        self::$reqId2 = $userReq->getRequestId();
        self::$reqCode2 = $userReq->getReadableRequestCode();
        $this->assertNotEmpty($userReq->getRequestId());
        $this->assertNotEmpty($userReq->getHashedRequestCode());
        $this->assertEquals('', $userReq->getLogin());
        $this->assertEquals('', $userReq->getEmail());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_CANCELLED));

    }

    /**
     * @depends testCreateCancelledConfirmationRequest
     */
    public function testGetConfirmationRequest()
    {
        $requests = new Requests();
        $userReq = $requests->getRequest(self::$reqId);
        $this->assertNotNull($userReq);
        $this->assertEquals(self::$reqId, $userReq->getRequestId());
        $this->assertNull($userReq->getReadableRequestCode());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_PENDING));

        $userReq = $requests->getRequest(self::$reqId2);
        $this->assertNotNull($userReq);
        $this->assertEquals(self::$reqId2, $userReq->getRequestId());
        $this->assertNull($userReq->getReadableRequestCode());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_CANCELLED));
    }

    /**
     * @depends testGetConfirmationRequest
     */
    public function testCheckGoodCode()
    {
        $requests = new Requests();
        $userReq = $requests->checkRequest(self::$reqId, self::$reqCode);
        $this->assertNotNull($userReq);
        $this->assertEquals(self::$reqId, $userReq->getRequestId());
    }

    /**
     * @depends testCheckGoodCode
     */
    public function testCheckBadCode()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(RequestException::CODE_BAD_CONFIRMATION_CODE);
        $requests = new Requests();
        $requests->checkRequest(self::$reqId, '1234');
    }

    /**
     * @depends testCheckBadCode
     */
    public function testCheckCodeBadReqId()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(RequestException::CODE_BAD_CONFIRMATION_CODE);
        $requests = new Requests();
        $requests->checkRequest('1324654987987', self::$reqCode);
    }

    /**
     * @depends testCheckCodeBadReqId
     */
    public function testCheckExpiredCode()
    {
        $db = \jDb::getConnection();
        $db->exec('UPDATE auth_user_requests SET req_expiration_date = \'2023-12-12 12-12\' WHERE req_id = \''.self::$reqId."'");
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(RequestException::CODE_EXPIRED_CODE);
        $requests = new Requests();
        $requests->checkRequest(self::$reqId, self::$reqCode);
    }

    /**
     * @depends testCheckExpiredCode
     */
    public function testCheckCodeBadStatus()
    {
        $db = \jDb::getConnection();
        $db->exec('UPDATE auth_user_requests SET req_status=\''.UserRequest::STATUS_CANCELLED.'\',  req_expiration_date = \''.date('Y-m-d').' 23:59\' WHERE req_id = \''.self::$reqId."'");
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(RequestException::CODE_EXPIRED_CODE);
        $requests = new Requests();
        $requests->checkRequest(self::$reqId, self::$reqCode);
    }


    public function testSaveAsCheckedConfirmed()
    {
        $requests = new Requests();
        $ttl =  new \DateInterval('PT5M');
        $userReq = $requests->createRequest(
            UserRequest::TYPE_RECOVERY_ACCOUNT,
            'alain',
            'alain@example.com',
            $ttl);
        $reqId = $userReq->getRequestId();

        $userReq->saveAsChecked();

        $userReq = $requests->getRequest($reqId);
        $this->assertNotNull($userReq);
        $this->assertEquals($reqId, $userReq->getRequestId());
        $this->assertEquals('alain', $userReq->getLogin());
        $this->assertEquals('alain@example.com', $userReq->getEmail());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_CHECKED));

        $userReq->saveAsConfirmed();

        $userReq = $requests->getRequest($reqId);
        $this->assertNotNull($userReq);
        $this->assertEquals($reqId, $userReq->getRequestId());
        $this->assertTrue($userReq->hasStatus($userReq::STATUS_CONFIRMED));

    }

}