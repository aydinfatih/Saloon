<?php

namespace Sammyjo20\Saloon\Traits;

trait AuthenticatesRequests
{
    use HasKeychain;

    /**
     * Attach basic authentication to the request.
     *
     * @param string $username
     * @param string $password
     * @param bool $withDigest
     * @return AuthenticatesRequests|\Sammyjo20\Saloon\Http\SaloonConnector
     */
    public function withBasicAuth(string $username, string $password, bool $withDigest = false): self
    {
        $auth = [$username, $password];

        if ($withDigest === true) {
            $auth[] = 'digest';
        }

        $this->addConfig('auth', $auth);

        return $this;
    }

    /**
     * Attach basic authentication with a digest to the request.
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withDigestAuth(string $username, string $password): self
    {
        $this->withBasicAuth($username, $password, true);

        return $this;
    }

    /**
     * Attach an Authorization token to the request.
     *
     * @param string $token
     * @param string|null $type
     * @return $this
     */
    public function withToken(string $token, ?string $type = 'Bearer'): self
    {
        $this->addHeader('Authorization', trim($type . ' ' . $token));

        return $this;
    }
}