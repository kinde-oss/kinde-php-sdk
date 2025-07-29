<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">User Info</h1>
    
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">User Details</h2>
        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ var_export($userDetails, true) }}</pre>
    </div>
    
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Permissions</h2>
        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ var_export($permissions, true) }}</pre>
    </div>
    
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Organization</h2>
        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ var_export($organization, true) }}</pre>
    </div>
    
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Entitlements</h2>
        @if($entitlementsError)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>Error:</strong> {{ $entitlementsError }}
            </div>
        @elseif(empty($entitlements))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No entitlements found for this user.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($entitlements as $entitlement)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $entitlement->getFeatureName() }}</h3>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $entitlement->getFeatureKey() }}</span>
                        </div>
                        
                        <div class="space-y-2 text-sm text-gray-600">
                            @if($entitlement->getEntitlementLimitMax() !== null)
                                <div class="flex justify-between">
                                    <span>Max Limit:</span>
                                    <span class="font-medium">{{ number_format($entitlement->getEntitlementLimitMax()) }}</span>
                                </div>
                            @endif
                            
                            @if($entitlement->getEntitlementLimitMin() !== null)
                                <div class="flex justify-between">
                                    <span>Min Limit:</span>
                                    <span class="font-medium">{{ number_format($entitlement->getEntitlementLimitMin()) }}</span>
                                </div>
                            @endif
                            
                            @if($entitlement->getUnitAmount() !== null)
                                <div class="flex justify-between">
                                    <span>Unit Amount:</span>
                                    <span class="font-medium">{{ number_format($entitlement->getUnitAmount()) }}</span>
                                </div>
                            @endif
                            
                            @if($entitlement->getFixedCharge() !== null)
                                <div class="flex justify-between">
                                    <span>Fixed Charge:</span>
                                    <span class="font-medium">${{ number_format($entitlement->getFixedCharge() / 100, 2) }}</span>
                                </div>
                            @endif
                            
                            @if($entitlement->getPriceName())
                                <div class="flex justify-between">
                                    <span>Price Plan:</span>
                                    <span class="font-medium">{{ $entitlement->getPriceName() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                <h3 class="text-md font-semibold mb-2">Raw Entitlements Data</h3>
                <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ var_export($entitlements, true) }}</pre>
            </div>
            
            @if(!empty($entitlementChecks))
                <div class="mt-6">
                    <h3 class="text-md font-semibold mb-2">Entitlement Method Demonstrations</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-blue-900 mb-2">hasEntitlement() Method</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span>Has first entitlement:</span>
                                        <span class="font-mono">{{ $entitlementChecks['has_entitlement'] ? 'true' : 'false' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Has non-existent entitlement:</span>
                                        <span class="font-mono">{{ $entitlementChecks['non_existent_entitlement'] ? 'true' : 'false' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-blue-900 mb-2">getEntitlementLimit() Method</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span>First entitlement limit:</span>
                                        <span class="font-mono">{{ $entitlementChecks['entitlement_limit'] ?? 'null' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Non-existent limit:</span>
                                        <span class="font-mono">{{ $entitlementChecks['non_existent_limit'] ?? 'null' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h4 class="font-medium text-blue-900 mb-2">getEntitlement() Method Result</h4>
                            <pre class="bg-white p-3 rounded text-xs overflow-x-auto">{{ var_export($entitlementChecks['specific_entitlement'], true) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div> 