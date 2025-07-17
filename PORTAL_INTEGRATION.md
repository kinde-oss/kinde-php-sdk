# Kinde Portal Integration

This document explains how to use the Kinde portal functionality across different PHP frameworks.

## Overview

The Kinde portal allows users to manage their account, billing, and organization settings directly through Kinde's hosted interface. The portal integration provides a seamless way to redirect users to the appropriate portal page.

## Portal Pages Available

The portal supports different sub-navigation sections:

- `profile` - User profile management
- `organization_details` - Organization information
- `organization_members` - Organization member management
- `organization_plan_details` - Current plan details
- `organization_payment_details` - Payment information
- `organization_plan_selection` - Plan selection and upgrades

## Framework Integration

### Laravel

#### Route
```php
// Automatically registered by the package
GET /auth/portal
```

#### Usage
```php
// Basic portal redirect
<a href="{{ route('kinde.portal') }}">Manage Account</a>

// Portal with specific sub-navigation
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_plan_details']) }}">Billing</a>

// Portal with custom return URL
<a href="{{ route('kinde.portal', [
    'sub_nav' => 'organization_payment_details',
    'return_url' => route('dashboard')
]) }}">Payment Settings</a>
```

#### Controller Method
```php
public function portal(Request $request): RedirectResponse
{
    if (!$this->kindeClient->isAuthenticated) {
        return redirect()->route('login');
    }

    $returnUrl = $request->get('return_url', route('dashboard'));
    $subNav = $request->get('sub_nav', 'profile');

    try {
        $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
        return redirect()->away($portalData['url']);
    } catch (Exception $e) {
        return redirect()->back()->withErrors(['portal' => 'Failed to generate portal URL: ' . $e->getMessage()]);
    }
}
```

### Slim Framework

#### Route
```php
$app->get('/auth/portal', [KindeAuthController::class, 'portal']);
```

#### Usage
```php
// Basic portal redirect
<a href="/auth/portal">Manage Account</a>

// Portal with specific sub-navigation
<a href="/auth/portal?sub_nav=organization_plan_details">Billing</a>

// Portal with custom return URL
<a href="/auth/portal?sub_nav=organization_payment_details&return_url=/dashboard">Payment Settings</a>
```

#### Controller Method
```php
public function portal(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
{
    if (!$this->kindeClient->isAuthenticated) {
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/auth/login');
    }

    $queryParams = $request->getQueryParams();
    $returnUrl = $queryParams['return_url'] ?? '/dashboard';
    $subNav = $queryParams['sub_nav'] ?? 'profile';

    try {
        $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
        return $response
            ->withStatus(302)
            ->withHeader('Location', $portalData['url']);
    } catch (Exception $e) {
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/?error=' . urlencode('Failed to generate portal URL: ' . $e->getMessage()));
    }
}
```

### Symfony

#### Route
```yaml
# config/routes.yaml
kinde_auth:
    resource: '@KindeAuthBundle/Controller/'
    type: annotation
    prefix: /auth
```

#### Usage
```twig
{# Basic portal redirect #}
<a href="{{ path('kinde_portal') }}">Manage Account</a>

{# Portal with specific sub-navigation #}
<a href="{{ path('kinde_portal', {'sub_nav': 'organization_plan_details'}) }}">Billing</a>

{# Portal with custom return URL #}
<a href="{{ path('kinde_portal', {
    'sub_nav': 'organization_payment_details',
    'return_url': path('dashboard')
}) }}">Payment Settings</a>
```

#### Controller Method
```php
/**
 * @Route("/auth/portal", name="kinde_portal")
 */
public function portal(Request $request): RedirectResponse
{
    if (!$this->kindeClient->isAuthenticated) {
        return $this->redirectToRoute('kinde_login');
    }

    $returnUrl = $request->query->get('return_url', $this->generateUrl('dashboard'));
    $subNav = $request->query->get('sub_nav', 'profile');

    try {
        $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
        return $this->redirect($portalData['url']);
    } catch (Exception $e) {
        $this->addFlash('error', 'Failed to generate portal URL: ' . $e->getMessage());
        return $this->redirectToRoute('home');
    }
}
```

### CodeIgniter

#### Route
```php
$routes->get('auth/portal', 'KindeAuthController::portal');
```

#### Usage
```php
// Basic portal redirect
<a href="<?= base_url('auth/portal') ?>">Manage Account</a>

// Portal with specific sub-navigation
<a href="<?= base_url('auth/portal?sub_nav=organization_plan_details') ?>">Billing</a>

// Portal with custom return URL
<a href="<?= base_url('auth/portal?sub_nav=organization_payment_details&return_url=' . base_url('dashboard')) ?>">Payment Settings</a>
```

#### Controller Method
```php
public function portal()
{
    if (!$this->kindeClient->isAuthenticated) {
        return redirect()->to('/auth/login');
    }

    $returnUrl = $this->request->getGet('return_url') ?? base_url('dashboard');
    $subNav = $this->request->getGet('sub_nav') ?? 'profile';

    try {
        $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
        return redirect()->away($portalData['url']);
    } catch (Exception $e) {
        session()->setFlashdata('error', 'Failed to generate portal URL: ' . $e->getMessage());
        return redirect()->back();
    }
}
```

## Common Use Cases

### 1. Account Management
```php
// Redirect to profile management
<a href="{{ route('kinde.portal', ['sub_nav' => 'profile']) }}">Edit Profile</a>
```

### 2. Billing Management
```php
// Redirect to billing details
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_plan_details']) }}">Billing</a>

// Redirect to payment settings
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_payment_details']) }}">Payment Settings</a>

// Redirect to plan selection
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_plan_selection']) }}">Upgrade Plan</a>
```

### 3. Organization Management
```php
// Redirect to organization details
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_details']) }}">Organization Settings</a>

// Redirect to member management
<a href="{{ route('kinde.portal', ['sub_nav' => 'organization_members']) }}">Manage Members</a>
```

### 4. Custom Return URLs
```php
// Redirect to portal and return to specific page
<a href="{{ route('kinde.portal', [
    'sub_nav' => 'organization_plan_details',
    'return_url' => route('billing.success')
]) }}">Manage Billing</a>
```

## Error Handling

All portal routes include proper error handling:

1. **Authentication Check**: Redirects to login if user is not authenticated
2. **Invalid Return URL**: Validates that return URL is absolute
3. **API Errors**: Catches and displays errors from Kinde API
4. **Network Issues**: Handles connection problems gracefully

## Security Considerations

1. **Authentication Required**: Portal routes are protected by authentication middleware
2. **URL Validation**: Return URLs are validated to be absolute URLs
3. **Token Validation**: Uses valid access tokens for API calls
4. **Error Sanitization**: Error messages are sanitized before display

## Integration Examples

### Dashboard with Portal Links
```vue
<template>
    <div class="dashboard">
        <h1>Welcome, {{ kinde.user?.given_name }}</h1>
        
        <div class="portal-links">
            <a href="/auth/portal?sub_nav=profile" class="btn btn-primary">
                Edit Profile
            </a>
            
            <a href="/auth/portal?sub_nav=organization_plan_details" class="btn btn-secondary">
                Billing
            </a>
            
            <a href="/auth/portal?sub_nav=organization_members" class="btn btn-info">
                Team Members
            </a>
        </div>
    </div>
</template>
```

### Settings Page
```blade
@extends('layouts.app')

@section('content')
<div class="settings-page">
    <h2>Account Settings</h2>
    
    <div class="settings-grid">
        <div class="setting-card">
            <h3>Profile</h3>
            <p>Update your personal information</p>
            <a href="{{ route('kinde.portal', ['sub_nav' => 'profile']) }}" class="btn">
                Manage Profile
            </a>
        </div>
        
        <div class="setting-card">
            <h3>Billing</h3>
            <p>Manage your subscription and payment methods</p>
            <a href="{{ route('kinde.portal', ['sub_nav' => 'organization_plan_details']) }}" class="btn">
                Manage Billing
            </a>
        </div>
        
        <div class="setting-card">
            <h3>Organization</h3>
            <p>Manage your organization settings and members</p>
            <a href="{{ route('kinde.portal', ['sub_nav' => 'organization_details']) }}" class="btn">
                Organization Settings
            </a>
        </div>
    </div>
</div>
@endsection
```

This portal integration provides a seamless way for users to manage their Kinde account settings while maintaining the security and user experience of your application. 