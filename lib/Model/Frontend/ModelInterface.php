<?php
/**
 * ModelInterface
 *
 * PHP version 8.1
 *
 * @category Class
 * @package  Kinde\KindeSDK\Model\Frontend
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Kinde Account API
 *
 * Provides endpoints to operate on an authenticated user.  ## Intro  ## How to use  1. Get a user access token - this can be obtained when a user signs in via the methods you've setup in Kinde (e.g. Google, passwordless, etc).  2. Call one of the endpoints below using the user access token in the Authorization header as a Bearer token. Typically, you can use the `getToken` command in the relevant SDK.
 *
 * The version of the OpenAPI document: 1
 * Contact: support@kinde.com
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.13.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Kinde\KindeSDK\Model\Frontend;

/**
 * Interface abstracting model access.
 *
 * @package Kinde\KindeSDK\Model\Frontend
 * @author  OpenAPI Generator team
 */
interface ModelInterface
{
    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName();

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes();

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats();

    /**
     * Array of attributes where the key is the local name, and the value is the original name
     *
     * @return array
     */
    public static function attributeMap();

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters();

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters();

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array
     */
    public function listInvalidProperties();

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool
     */
    public function valid();

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool;

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool;
}
