# # UsersResponseUsersInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | Unique id of the user in Kinde. | [optional]
**provided_id** | **string** | External id for user. | [optional]
**email** | **string** | Default email address of the user in Kinde. | [optional]
**last_name** | **string** | User&#39;s last name. | [optional]
**first_name** | **string** | User&#39;s first name. | [optional]
**is_suspended** | **bool** | Whether the user is currently suspended or not. | [optional]
**picture** | **string** | User&#39;s profile picture URL. | [optional]
**total_sign_ins** | **int** | Total number of user sign ins. | [optional]
**failed_sign_ins** | **int** | Number of consecutive failed user sign ins. | [optional]
**last_signed_in** | **string** | Last sign in date in ISO 8601 format. | [optional]
**created_on** | **string** | Date of user creation in ISO 8601 format. | [optional]
**organizations** | **string[]** | Array of organizations a user belongs to. | [optional]
**identities** | [**\Kinde\KindeSDK\Model\UserIdentitiesInner[]**](UserIdentitiesInner.md) | Array of identities belonging to the user. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
