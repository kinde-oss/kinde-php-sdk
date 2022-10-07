<?php

namespace Kinde\KindeSDK\Sdk\Enums;

class GrantType {
    const clientCredentials = 'client_credentials';
    const authorizationCode = 'authorization_code';
    const PKCE = 'authorization_code_flow_pkce';
}