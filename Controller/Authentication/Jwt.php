<?php
    class Jwt extends BaseController
    {
        public function base64url_encode($data = '')
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        public function base64url_decode($base64 = '')
        {
            return base64_decode(strtr($base64, '-_', '+/') . '=');
        }

        public function jwt_generate_token($payload_arr)
        {
            if (!$payload_arr)
                return false;

            $rsa = new Rsa();
            $datum = new DateTime();
            $timestamp = $datum->getTimestamp();

            $expiry = 600;
            
            $header_arr = array("alg" => "RS256", "typ" => "JWT");
            $payload_arr['iat'] = $timestamp;
            $payload_arr['exp'] = $timestamp + $expiry;

            $header = json_encode($header_arr);
            $payload = json_encode($payload_arr);

            $unsigned = $this->base64url_encode($header) . "." . $this->base64url_encode($payload);

            $signature = $rsa->rsa256_encrypt($unsigned, PUBLIC_E, PUBLIC_N);

            $token = $unsigned . "." . $this->base64url_encode($signature);

            return array(
                "token" => $token,
                "schema" => "Bearer",
                "expires_in" => $expiry
            );
        }
    }
?>