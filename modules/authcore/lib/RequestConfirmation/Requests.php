<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 * @link      https://jelix.org
 * @licence   MIT
 */

namespace Jelix\Authentication\RequestConfirmation;

class Requests
{
    /**
     * Create a request to confirm something.
     *
     * In the returned UserRequest object, you got the code
     * to send to the user. The user should then type
     * the code in a form, and you have to call checkRequest()
     * to verify he gave the right code.
     *
     * @param string $type one of UserRequest::TYPE_* constant
     * @param string $login the user identifiant
     * @param string $email
     * @param mixed $content
     * @return UserRequest
     * @throws \Random\RandomException
     */
    public function createRequest($type, $login, $email, \DateInterval $ttl, $content = null)
    {
        $reqCode = random_int(10000, 999999);

        $reqDao = \jDao::get('authcore~auth_user_requests');
        
        $reqRecord = $reqDao->createRecord();
        $reqRecord->req_id = sha1($type.$reqCode.date('Y-m-d H:i'));
        $reqRecord->req_type = $type;
        $reqRecord->req_code = password_hash($reqCode, PASSWORD_DEFAULT);
        $reqRecord->req_status = UserRequest::STATUS_PENDING;
        $reqRecord->req_login = $login;
        $reqRecord->req_email = $email;
        $reqRecord->req_content = $content;

        $requestDate = new \DateTime();
        $requestDate->add($ttl);
        $reqRecord->req_expiration_date = $requestDate->format("Y-m-d H:i");

        $reqDao->insert($reqRecord);
        
        $req = new UserRequest($reqRecord, $reqCode);

        return $req;
    }

    /**
     * Creates a fake request
     *
     * When a user gaves a wrong login or email before recovering his
     * account for example, a good practice is to not indicate the
     * error, but let him to continue the process, so to show him the form
     * that allow to indicate the confirmation code.
     *
     * So a hacker doesn't know if the given login/email exists into
     * your application.
     *
     * Creating a fake request allow you to retrieve a request id,
     * but, as it is marked as cancelled, the user could not
     * confirm a code and could not continue.
     *
     * @param string $type
     * @return UserRequest
     * @throws \Random\RandomException
     */
    public function createCancelledRequest($type)
    {
        $reqCode = random_int(10000, 999999);

        $reqDao = \jDao::get('authcore~auth_user_requests');

        $reqRecord = $reqDao->createRecord();
        $reqRecord->req_id = sha1($type.$reqCode.date('Y-m-d H:i'));
        $reqRecord->req_type = $type;
        $reqRecord->req_code = password_hash($reqCode, PASSWORD_DEFAULT);
        $reqRecord->req_status = UserRequest::STATUS_CANCELLED;
        $reqRecord->req_login = '';
        $reqRecord->req_email = '';
        $reqRecord->req_content = '';
        $reqRecord->req_expiration_date = date("Y-m-d H:i");

        $reqDao->insert($reqRecord);

        $req = new UserRequest($reqRecord, $reqCode);

        return $req;
    }

    /**
     * @param string $reqId
     * @return UserRequest|null
     * @throws \jException
     */
    public function getRequest($reqId)
    {
        $reqDao = \jDao::get('authcore~auth_user_requests');
        $reqRecord = $reqDao->get($reqId);
        if (!$reqRecord) {
            return null;
        }
        $req = new UserRequest($reqRecord);
        return $req;
    }

    /**
     * Check if the given code is good for the given request
     *
     * An exception is thrown if the code is bad, the request
     * doesn't exist, the request has expired or has already been
     * confirmed.
     *
     * @param string $reqId
     * @param string $reqCode
     * @return UserRequest
     * @throws RequestException
     * @throws \jException
     */
    public function checkRequest($reqId, $reqCode)
    {
        $reqDao = \jDao::get('authcore~auth_user_requests');
        $reqRecord = $reqDao->get($reqId);
        if (!$reqRecord) {
            // bad request id
            throw new RequestException(RequestException::CODE_BAD_CONFIRMATION_CODE);
        }

        if ($reqRecord->req_code == '' || !password_verify($reqCode, $reqRecord->req_code)) {
            // bad code
            throw new RequestException(RequestException::CODE_BAD_CONFIRMATION_CODE);
        }

        if ($reqRecord->req_expiration_date < date('Y-m-d H:i')) {
            // code has expired
            throw new RequestException(RequestException::CODE_EXPIRED_CODE);
        }

        if ($reqRecord->req_status != UserRequest::STATUS_PENDING  && $reqRecord->req_status != UserRequest::STATUS_CHECKED) {
            // bad code status
            throw new RequestException(RequestException::CODE_EXPIRED_CODE);
        }

        return new UserRequest($reqRecord, $reqCode);
    }
}
