<?php
namespace GemLibrary\Http;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GemToken
{
    public int       $exp;
    public bool      $isTokenValid;
    public int       $user_id;
    public string    $type;//access or refresh
    public array     $payload;/** @phpstan-ignore-line */
    public ?string   $token_id;
    public ?string   $iss;
    public ?string   $role;
    public ?int      $company_id;
    public ?int      $employee_id;
    public ?string   $error;
    public ?string   $userAgent;
    public ?string   $ip;
    private string   $_secret;

    public function __construct(string $secret , string $issuer = null)
    {
        $this->user_id = 0;
        if($issuer)
        {
            $this->iss = $issuer;
        }
        $this->exp = 0;
        $this->isTokenValid = false;
        $this->error = 'Not Initialized';
        $this->payload = [];
        $this->type = 'not defined';
        $this->_secret = $secret;
    }


    /**
     * @param int $userId
     * @param int $timeToLiveSecond
     * @return string
     */
    public function create(int $userId, int $timeToLiveSecond): string
    {
        $payloadArray = [
            'token_id' => microtime(true),
            'user_id' => $userId,
            'iss' => $this->iss,
            'exp' => (time() + $timeToLiveSecond),
            'type' => $this->type,
            'payload' => $this->payload,
            'role' => $this->role
        ];
        if(isset($this->company_id))
        {
            $payloadArray['company_id'] = $this->company_id;
        }
        if(isset($this->employee_id))
        {
            $payloadArray['employee_id'] = $this->employee_id;
        }
        return JWT::encode($payloadArray, $this->_generate_key(), 'HS256');
    }
    /**
     * @param string $token
     * @description pure token without Bearer you can use WebHelper::BearerTokenPurify() got get pure token
     */
    public function verify(string $token): bool
    {
        try {
            $decodedToken = JWT::decode($token, new Key($this->_generate_key(), 'HS256'));
            if (isset($decodedToken->user_id) && $decodedToken->exp > time() && $decodedToken->user_id>0) {
                $this->token_id = $decodedToken->token_id;
                $this->user_id = (int)$decodedToken->user_id;
                $this->exp = $decodedToken->exp;
                $this->iss = $decodedToken->iss;
                $this->payload = $decodedToken->payload;
                $this->isTokenValid = true;
                $this->type = $decodedToken->type;
                $this->role = $decodedToken->role;
                if(isset($decodedToken->company_id))
                {
                    $this->company_id = $decodedToken->company_id;
                }
                if(isset($decodedToken->employee_id))
                {
                    $this->employee_id = $decodedToken->employee_id;
                }
                $this->error = null;
                return true;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function renew(string $token, int $extensionTime_sec): false|string
    {
        if ($this->verify($token)) {
            return $this->create($this->user_id, $extensionTime_sec);
        }
        return false;
    }

    /**
     * @param string $token
     * @return string|null
     * @description Returns type without validation token
     */
    public function GetType(string $token):string|null
    {
        $tokenParts = explode('.', $token);

        // The payload is the second part of the token
        $payloadBase64 = $tokenParts[1];

        // Decode the payload from base64
        $payload = json_decode(base64_decode($payloadBase64), true);

        // Access the "type" property from the payload
        if (isset($payload['type'])) /** @phpstan-ignore-line */
        {
            return $payload['type'];/** @phpstan-ignore-line */
        } 
        else return null;
    }


    /**
     * @return string
     */
    private function _generate_key(): string
    {
        return $this->_secret . $this->ip . $this->userAgent;
    }
}
