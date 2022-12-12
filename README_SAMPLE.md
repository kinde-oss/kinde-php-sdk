# @kinde-oss/react-native-sdk-0-5x

React Native Client for `@kinde-oss/react-native-sdk-0-5x`
Provides endpoints to manage your Kinde Businesses

We only support the [Authorization Code Flow with PKCE](https://oauth.net/2/pkce/).
For more information, please visit [https://kinde.com/docs](https://kinde.com/docs)

## Support Versions

-   React Native: 0.50 -> 0.59

## Installing dependencies

You will need Node, the React Native command line interface, a JDK, Android Studio (for Android) and Xcode (for iOS).

Follow [the installation instructions for your chosen OS](https://archive.reactnative.dev/docs/0.50/getting-started) to install dependencies;

## Installation

#### npm

To publish the library as a [npm](https://www.npmjs.com/), please follow the procedure in ["Publishing npm packages"](https://docs.npmjs.com/getting-started/publishing-npm-packages).

Then install it via:

```shell
npm install @kinde-oss/react-native-sdk-0-5x --save
```

##### Local development

**Note:** Before you run `npm install`, ensure you already have `.git` folder, or you can generate it with command:

```shell
git init
```

To use the library locally without publishing to a remote npm registry, first install the dependencies by changing into the directory containing `package.json` (and this README). Let's call this `JAVASCRIPT_CLIENT_DIR`. Then run:

```shell
npm install
```

To use this SDK, switch to the directory you want to use your `@kinde-oss/react-native-sdk-0-5x` from, and run:

```shell
npm pack /path/to/<JAVASCRIPT_CLIENT_DIR> && npm install ./@kinde-oss/react-native-sdk-0-5x-<version>.tgz
```

## Getting Started

Please follow the [installation](#installation) instruction.

### Kinde configuration

On the Kinde web app navigate to Settings in the left menu, then select App keys and find the Callbacks input field.

Here you want to put in the callback URLs for your React Native app, which should look something like this:

-   Allowed callback URLs - `myapp://myhost.kinde.com/kinde_callback`
-   Allowed logout redirect URLs - `myapp://myhost.kinde.com/kinde_callback`

Make sure you press the Save button at the bottom of the page!

Note: The `myapp://myhost.kinde.com/kinde_callback` is used as an example of local URL Scheme, change to the local local URL Scheme that you use.

### Environments

If you would like to use our Environments feature as part of your development process. You will need to create them first within your Kinde account. In this case you would use the Environment subdomain in the code block above.

### Configuring your app

#### Environment variables

Put these variables in your .env file. You can find these variables on your Kinde Settings -> App keys page.

-   `KINDE_ISSUER_URL` - your Kinde domain
-   `KINDE_POST_CALLBACK_URL` - After the user authenticates we will callback to this address. Make sure this URL is under your allowed callback URLs
-   `KINDE_POST_LOGOUT_REDIRECT_URL` - where you want users to be redirected to after logging out. Make sure this URL is under your allowed logout redirect URLs
-   `KINDE_CLIENT_ID` - you can find this on the App Keys page

```javascript
KINDE_ISSUER_URL=https://your_kinde_domain.kinde.com
KINDE_POST_CALLBACK_URL=myapp://your_kinde_domain.kinde.com/kinde_callback
KINDE_POST_LOGOUT_REDIRECT_URL=myapp://your_kinde_domain.kinde.com/kinde_callback
KINDE_CLIENT_ID=your_kinde_client_id
```

### Configuration Deep link

If your app was launched from an external url registered to your app you can access and handle it from any component you want with:

```javascript
...
import { ..., Linking, Platform, ... } from 'react-native';
...

componentDidMount() {
    Linking.getInitialURL()
      .then((url) => {
        if (url) {
          // Need to implement
        }
      })
      .catch((err) => console.error("An error occurred", err));
  Linking.addEventListener('url', (event) => {
      if (event.url) {
        // Need to implement
      }
    })
}
```

#### iOS

On iOS, you'll need to link `RCTLinking` to your project by following the steps described here. If you also want to listen to incoming app links during your app's execution, you'll need to add the following lines to your `AppDelegate.m`

```swift
// iOS 9.x or newer
#import <React/RCTLinkingManager.h>

- (BOOL)application:(UIApplication *)application
   openURL:(NSURL *)url
   options:(NSDictionary<UIApplicationOpenURLOptionsKey,id> *)options
{
  return [RCTLinkingManager application:application openURL:url options:options];
}
```

If you're targeting iOS 8.x or older, you can use the following code instead:

```swift
// iOS 8.x or older
#import <React/RCTLinkingManager.h>

- (BOOL)application:(UIApplication *)application openURL:(NSURL *)url
  sourceApplication:(NSString *)sourceApplication annotation:(id)annotation
{
  return [RCTLinkingManager application:application openURL:url
                      sourceApplication:sourceApplication annotation:annotation];
}
```

Please make sure you have configuration URL scheme in `Info.plist`, so app can be opened by deep link:

```swift
...
<key>CFBundleURLTypes</key>
<array>
  <dict>
    <key>CFBundleTypeRole</key>
    <string>Editor</string>
    <key>CFBundleURLName</key>
    <string>myapp</string> // you can change it
    <key>CFBundleURLSchemes</key>
    <array>
      <string>myapp</string> // you can change it
    </array>
  </dict>
</array>
...
```

#### Android

Open `AndroidManifest.xml` and update your scheme:

```xml
<intent-filter>
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="myapp" android:host="your_kinde_issuer.kinde.com" />  // you can change it
</intent-filter>
```

### Integration your app

You’ll need to create a new instance of the Kinde Auth client object. Please execute this code below:

```javascript
...
import { KindeSDK } from '@kinde-oss/react-native-sdk-0-5x';
...

...
state = {
  ...
  client: new KindeSDK(YOUR_KINDE_ISSUER, YOUR_KINDE_REDIRECT_URI, YOUR_KINDE_CLIENT_ID, YOUR_KINDE_LOGOUT_REDIRECT_URI)
  ...
}
...
```

### Login / Register

The Kinde client provides methods for an easy to implement login / register flow.
As an example if you add buttons in your render as follows:

```javascript
<View>
    <View>
        <Button title="Sign In" onPress={this.handleSignIn} />
    </View>
    <View>
        <Button title="Sign Up" color="#000" onPress={this.handleSignUp} />
    </View>
</View>
```

Then define new functions that match for each button:
**\*Note**: Make sure you've already defined KindeSDK as client in the state\*

```javascript
...
constructor() {
  ...
  this.handleSignUp = this.handleSignUp.bind(this);
  this.handleSignIn = this.handleSignIn.bind(this);
  ...
}

handleSignUp() {
  this.state.client.register();
}

handleSignIn() {
  this.state.client.login();
}
...
```

### Handle redirect

Once your user is redirected back to your app from Kinde, using the getToken method to get token instance from Kinde

```javascript
...
constructor() {
  ...
  this.handleCallback = this.handleCallback.bind(this);
  ...
}
...
componentDidMount() {
  Linking.getInitialURL()
    .then((url) => {
      if (url) {
        this.handleCallback(url);
      }
    })
    .catch((err) => console.error("An error occurred", err));

  Linking.addEventListener('url', (event) => {
    if (event.url) {
      this.handleCallback(event.url);
    }
  })
}

handleCallback(url) {
  this.state.client.getToken(url).then(() => {
    console.log('Authenticated!!!');
  });
}
```

You can also get the current authentication status with `authStatusConstants`:

```javascript
...
import {..., authStatusConstants ,...} from '@kinde-oss/react-native-sdk-0-5x';
...

...

handleCallback(url) {
  if (this.state.client.authStatus !== authStatusConstants.UNAUTHENTICATED) {
    this.state.client.getToken(url).then(() => {
      console.log('Authenticated!!!');
    });
  }
}
```

Or simply use `checkIsUnauthenticated` from the SDK to determine whether the user is authenticated or not:

```javascript
handleCallback(url) {
  if (!this.state.client.checkIsUnAuthenticated()) {
    this.state.client.getToken(url).then(() => {
      console.log('Authenticated!!!');
    });
  }
}
```

### Logout

This is implemented in much the same way as logging in or registering. The Kinde SPA client comes with a logout method

```javascript
...
constructor() {
  ...
  this.handleLogout = this.handleLogout.bind(this);
  ...
}
...

handleLogout() {
  ...
  this.state.client.logout();
  ...
}
```

### Get user information

**\*Note warning:** Before you call the API, please make sure that you've already authenticated. If not, errors will appear there.\*

To access the user information, use the `UserApi, ApiClient` classes exported from `@kinde-oss/react-native-sdk-0-5x`, then call the `getUserProfile` method of `UserApi` instance

```javascript
...
import { ..., OAuthApi, ApiClient, ... } from '@kinde-oss/react-native-sdk-0-5x';
...

state = {
  ...
  apiClient: new ApiClient(YOUR_KINDE_ISSUER),
  ...
}
...
constructor() {
  ...
  this.getUserProfile = this.getUserProfile.bind(this);
  ...
}
...



getUserProfile() {
  const apiInstance = new OAuthApi(this.state.apiClient)
  apiInstance.getUser({}, (err, data, response) => {
    if (err) {
      console.error(err)
      return;
    }
    console.log(data);
  });
}

```

### View users in Kinde

If you navigate to the "Users" page within Kinde you will see your newly registered user there. 🚀

### User Permissions

Once a user has been verified as login in, your product/application will be returned the JWT token with an array of permissions for that user. You will need to configure your product/application to read permissions and unlock the respective functions.

You set Permissions in your Kinde account (see help article), the below is an example set of permissions.

```javascript
const permissions = [
    'create:todos',
    'update:todos',
    'read:todos',
    'delete:todos',
    'create:tasks',
    'update:tasks',
    'read:tasks',
    'delete:tasks'
];
```

We provide helper functions to more easily access permissions:

```javascript
this.state.client.getPermission('create:todos');
// {orgCode: "org_1234", isGranted: true}

this.state.client.getPermissions();
// {orgCode: "org_1234", permissions: ["create:todos", "update:todos", "read:todos"]}
```

A practical example in code might look something like:

```
if (this.state.client.getPermission("create:todos").isGranted) {
    // show Create Todo button in UI
}
```

### Audience

An `audience` is the intended recipient of an access token - for example the API for your application. The audience argument can be passed to the Kinde client to request an audience be added to the provided token.

The audience of a token is the intended recipient of the token.

```javascript
...
state = {
  ...
  client: new KindeSDK(YOUR_KINDE_ISSUER, YOUR_KINDE_REDIRECT_URI, YOUR_KINDE_CLIENT_ID, YOUR_KINDE_LOGOUT_REDIRECT_URI, YOUR_SCOPES,
  {
    audience: 'api.yourapp.com'
  })
  ...
}
...
```

For details on how to connect, see [Register an API](https://kinde.com/docs/developer-tools/register-an-api/)

### Overriding scope

By default the KindeSDK SDK requests the following scopes:

-   profile
-   email
-   offline
-   openid

You can override this by passing scope into the KindeSDK

```javascript
...
state = {
  ...
  client: new KindeSDK(YOUR_KINDE_ISSUER, YOUR_KINDE_REDIRECT_URI, YOUR_KINDE_CLIENT_ID, YOUR_KINDE_LOGOUT_REDIRECT_URI, "profile email offline openid")
  ...
}
...
```

### Getting claims

We have provided a helper to grab any claim from your id or access tokens. The helper defaults to access tokens:

```javascript
this.state.client.getClaim('aud');
// ["api.yourapp.com"]

this.state.client.getClaim('given_name', 'id_token');
// "David"
```

### Organizations Control

#### Create an organization

To have a new organization created within your application, you will need to run a similar function to below:

```javascript
<Button title="Create Organization" onPress={this.handleSignIn} />
```

Then define new function that match for button:
**\*Note**: Make sure you've already defined KindeSDK as client in the state\*

```javascript
...
constructor() {
  ...
  this.handleCreateOrg = this.handleCreateOrg.bind(this);
  this.handleSignIn = this.handleSignIn.bind(this);
  ...
}

handleCreateOrg() {
  this.state.client.createOrg();
}

// You can also pass org_name as your organization
this.state.client.createOrg({org_name: 'Your Organization'});
...
```

#### Sign and sign in to organizations

Kinde has a unique code for every organization. You’ll have to pass this code through when you register a new user. Example function below:

```javascript
this.state.client.register({ org_code: 'your_org_code' });
```

If you want a user to sign into a particular organization, pass this code along with the sign in method.

```javascript
this.state.client.login({ org_code: 'your_org_code' });
```

Following authentication, Kinde provides a json web token (jwt) to your application. Along with the standard information we also include the org_code and the permissions for that organization (this is important as a user can belong to multiple organizations and have different permissions for each). Example of a returned token:

```json
{
    "aud": [],
    "exp": 1658475930,
    "iat": 1658472329,
    "iss": "https://your_subdomain.kinde.com",
    "jti": "123457890",
    "org_code": "org_1234",
    "permissions": ["read:todos", "create:todos"],
    "scp": ["openid", "profile", "email", "offline"],
    "sub": "kp:123457890"
}
```

The id_token will also contain an array of Organizations that a user belongs to - this is useful if you wanted to build out an organization switcher for example.

```json
{
...
"org_codes": ["org_1234", "org_4567"]
...
}
```

There are two helper functions you can use to extract information:

```javascript
this.state.client.getOrganization();
// {orgCode: "org_1234"}

this.state.client.getUserOrganizations();
// {orgCodes: ["org_1234", "org_abcd"]}
```

## How to run test

The simplest way to run the JavaScript test suite is by using the following command at the root of your React Native checkout:

```bash
npm test
```

_Note: Ensure you have already run `npm install` before_

## SDK API Reference

| Property                        | Type    | Is required | Default        | Description                                                                                                       |
| ------------------------------- | ------- | ----------- | -------------- | ----------------------------------------------------------------------------------------------------------------- |
| issuer                          | string  | Yes         |                | Either your Kinde instance url or your custom domain. e.g [https://yourapp.kinde.com](https://yourapp.kinde.com/) |
| redirectUri                     | string  | Yes         |                | The url that the user will be returned to after authentication                                                    |
| clientId                        | string  | Yes         |                | The id of your application - get this from the Kinde admin area                                                   |
| logoutRedirectUri               | string  | No          |                | Where your user will be redirected upon logout                                                                    |
| scope                           | boolean | No          | openid offline | The scopes to be requested from Kinde                                                                             |
| additionalParameters            | object  | No          | {}             | Additional parameters that will be passed in the authorization request                                            |
| additionalParameters - audience | string  | No          |                | The audience claim for the JWT                                                                                    |

## KindeSDK methods

| Property             | Description                                                                                       | Arguments                        | Usage                                                                        | Sample output                                                                        |
| -------------------- | ------------------------------------------------------------------------------------------------- | -------------------------------- | ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| login                | Constructs redirect url and sends user to Kinde to sign in                                        | org_code?: string                | kinde.login();                                                               |                                                                                      |
| register             | Constructs redirect url and sends user to Kinde to sign up                                        | org_code?: string                | kinde.register();                                                            |                                                                                      |
| logout               | Logs the user out of Kinde                                                                        |                                  | kinde.logout();                                                              |                                                                                      |
| getToken             | Returns the raw Access token from URL after logged from Kinde                                     | url: string                      | kinde.getToken(url);                                                         | eyJhbGciOiJIUzI1...                                                                  |
| createOrg            | Constructs redirect url and sends user to Kinde to sign up and create a new org for your business | org_name?: string                | kinde.createOrg(); or kinde.createOrg({org_name: 'your organization name'}); | redirect                                                                             |
| getClaim             | Gets a claim from an access or id token                                                           | claim: string, tokenKey?: string | kinde.getClaim('given_name', 'id_token');                                    | "David"                                                                              |
| getPermission        | Returns the state of a given permission                                                           | key: string                      | kinde.getPermission('read:todos');                                           | {orgCode: "org_1234", isGranted: true}                                               |
| getPermissions       | Returns all permissions for the current user for the organization they are logged into            |                                  | kinde.getPermissions();                                                      | {orgCode: "org_1234", permissions: \["create:todos", "update:todos", "read:todos"\]} |
| getOrganization      | Get details for the organization your user is logged into                                         |                                  | kinde.getOrganization();                                                     | {orgCode: "org_1234"}                                                                |
| getUserDetails       | Returns the profile for the current user                                                          |                                  | kinde.getUserDetails();                                                      | {given_name: "Dave"; id: "abcdef"; family_name: "Smith"; email: "dave@smith.com"}    |
| getUserOrganizations | Gets an array of all organizations the user has access to                                         |                                  | kinde.getUserOrganizations();                                                | {orgCodes: \["org_1234", "org_5678"\]}                                               |

If you need any assistance with getting Kinde connected reach out to us at support@kinde.com.

## Documentation for API Endpoints

All URIs are relative to *https://app.kinde.com/api/v1*

| Class                                       | Method                                                    | HTTP request                    | Description                                                 |
| ------------------------------------------- | --------------------------------------------------------- | ------------------------------- | ----------------------------------------------------------- |
| _@kinde-oss/react-native-sdk-0-5x.OAuthApi_ | [**getUser**](docs/OAuthApi.md#getUser)                   | **GET** /oauth2/user_profile    | Returns the details of the currently logged in user         |
| _@kinde-oss/react-native-sdk-0-5x.OAuthApi_ | [**getUserProfileV2**](docs/OAuthApi.md#getUserProfileV2) | **GET** /oauth2/v2/user_profile | Returns the details of the currently logged in user         |
| _@kinde-oss/react-native-sdk-0-5x.UsersApi_ | [**createUser**](docs/UsersApi.md#createUser)             | **POST** /user                  | Creates a user record                                       |
| _@kinde-oss/react-native-sdk-0-5x.UsersApi_ | [**getUsers**](docs/UsersApi.md#getUsers)                 | **GET** /users                  | Returns a paginated list of end-user records for a business |

## Documentation for Models

-   [@kinde-oss/react-native-sdk-0-5x.CreateUser200Response](docs/CreateUser200Response.md)
-   [@kinde-oss/react-native-sdk-0-5x.CreateUserRequest](docs/CreateUserRequest.md)
-   [@kinde-oss/react-native-sdk-0-5x.CreateUserRequestIdentitiesInner](docs/CreateUserRequestIdentitiesInner.md)
-   [@kinde-oss/react-native-sdk-0-5x.CreateUserRequestIdentitiesInnerDetails](docs/CreateUserRequestIdentitiesInnerDetails.md)
-   [@kinde-oss/react-native-sdk-0-5x.CreateUserRequestProfile](docs/CreateUserRequestProfile.md)
-   [@kinde-oss/react-native-sdk-0-5x.User](docs/User.md)
-   [@kinde-oss/react-native-sdk-0-5x.UserIdentity](docs/UserIdentity.md)
-   [@kinde-oss/react-native-sdk-0-5x.UserIdentityResult](docs/UserIdentityResult.md)
-   [@kinde-oss/react-native-sdk-0-5x.UserProfile](docs/UserProfile.md)
-   [@kinde-oss/react-native-sdk-0-5x.UserProfileV2](docs/UserProfileV2.md)