<?php

/**
 * Helper functions for Has Functionality Test view
 */

if (!function_exists('getCategoryTitle')) {
    function getCategoryTitle($categoryName) {
        $titles = [
            'hasRoles' => '🎭 hasRoles() Method',
            'hasPermissions' => '🔐 hasPermissions() Method',
            'hasFeatureFlags' => '🚩 hasFeatureFlags() Method',
            'hasBillingEntitlements' => '💰 hasBillingEntitlements() Method',
            'unifiedHas' => '🎯 Unified has() Method',
            'customConditions' => '⚙️ Custom Conditions',
            'forceApiTests' => '🔄 Force API Options',
            'edgeCases' => '🧪 Edge Cases & Error Handling',
            'performance' => '⚡ Performance Tests'
        ];
        
        return $titles[$categoryName] ?? ucfirst($categoryName) . ' Tests';
    }
}

if (!function_exists('formatValue')) {
    function formatValue($value) {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        if (is_array($value)) {
            return 'array[' . count($value) . ']';
        }
        return htmlspecialchars((string)$value);
    }
}
