<?php

namespace Kinde\KindeSDK\Sdk\Enums;

class AcrValues
{
    // Step-up authentication contexts (RFC 9470)
    const MFA = 'mfa'; // Second factor
    const PHR = 'phr'; // Phishing-resistant factor (passkey), also meets MFA
}
