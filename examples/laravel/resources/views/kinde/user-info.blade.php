<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">User Info</h1>
    <div class="mb-4">
        <h2 class="text-lg font-semibold">User Details</h2>
        <pre>{{ var_export($userDetails, true) }}</pre>
    </div>
    <div class="mb-4">
        <h2 class="text-lg font-semibold">Permissions</h2>
        <pre>{{ var_export($permissions, true) }}</pre>
    </div>
    <div class="mb-4">
        <h2 class="text-lg font-semibold">Organization</h2>
        <pre>{{ var_export($organization, true) }}</pre>
    </div>
</div> 