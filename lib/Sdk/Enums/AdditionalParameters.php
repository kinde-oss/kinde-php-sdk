<?php

namespace Kinde\KindeSDK\Sdk\Enums;

class AdditionalParameters
{
    // Prompt types matching JS Utils SDK
    const PROMPT_LOGIN = 'login';   // Force user re-authentication
    const PROMPT_CREATE = 'create'; // Show registration screen  
    const PROMPT_NONE = 'none';     // Silent authentication

    const ADDITIONAL_PARAMETER = [
        'audience' => 'string',
        'org_code' => 'string',
        'org_name' => 'string',
        'is_create_org' => 'string',
        'login_hint' => 'string',
        'connection_id' => 'string',
        'lang' => 'string',
        'plan_interest' => 'string',
        'pricing_table_key' => 'string',
        'prompt' => 'string',
        'redirect_uri' => 'string'
    ];
}
