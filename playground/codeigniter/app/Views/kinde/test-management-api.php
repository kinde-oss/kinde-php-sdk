<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinde Management API Test Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .summary-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .summary-card.success .number {
            color: #28a745;
        }
        .summary-card.failed .number {
            color: #dc3545;
        }
        .header-fix-status {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .header-fix-status.working {
            background: #e8f5e8;
            border-color: #4caf50;
        }
        .header-fix-status.not-working {
            background: #ffebee;
            border-color: #f44336;
        }
        .test-results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .test-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .test-card.success {
            border-color: #28a745;
        }
        .test-card.failed {
            border-color: #dc3545;
        }
        .test-header {
            padding: 15px;
            font-weight: bold;
            color: white;
        }
        .test-card.success .test-header {
            background: #28a745;
        }
        .test-card.failed .test-header {
            background: #dc3545;
        }
        .test-body {
            padding: 15px;
        }
        .test-info {
            margin-bottom: 10px;
        }
        .test-info strong {
            color: #333;
        }
        .error-details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .errors-section {
            background: #ffebee;
            border: 1px solid #f44336;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        .errors-section h3 {
            color: #d32f2f;
            margin-top: 0;
        }
        .error-list {
            list-style: none;
            padding: 0;
        }
        .error-list li {
            background: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            border-left: 4px solid #f44336;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kinde Management API Test Dashboard</h1>
            <p>Comprehensive testing of all Management API endpoints to verify header fix functionality</p>
        </div>
        
        <div class="content">
            <a href="/" class="back-link">← Back to Home</a>
            
            <!-- Header Fix Status -->
            <div class="header-fix-status <?= $headerFixStatus['working'] === true ? 'working' : ($headerFixStatus['working'] === false ? 'not-working' : '') ?>">
                <h3>Header Fix Status</h3>
                <p><strong>Status:</strong> 
                    <?php if($headerFixStatus['working'] === true): ?>
                        ✅ Working
                    <?php elseif($headerFixStatus['working'] === false): ?>
                        ❌ Not Working
                    <?php else: ?>
                        ⚠️ Unknown
                    <?php endif; ?>
                </p>
                <p><strong>Message:</strong> <?= htmlspecialchars($headerFixStatus['message']) ?></p>
                <?php if(isset($headerFixStatus['test_call'])): ?>
                    <p><strong>Test Call:</strong> <?= htmlspecialchars($headerFixStatus['test_call']) ?></p>
                <?php endif; ?>
                <?php if(isset($headerFixStatus['error'])): ?>
                    <p><strong>Error:</strong> <code><?= htmlspecialchars($headerFixStatus['error']) ?></code></p>
                <?php endif; ?>
            </div>

            <!-- Summary -->
            <div class="summary">
                <div class="summary-card">
                    <h3>Total Tests</h3>
                    <div class="number"><?= $summary['total'] ?></div>
                </div>
                <div class="summary-card success">
                    <h3>Successful</h3>
                    <div class="number"><?= $summary['successful'] ?></div>
                </div>
                <div class="summary-card failed">
                    <h3>Failed</h3>
                    <div class="number"><?= $summary['failed'] ?></div>
                </div>
                <div class="summary-card">
                    <h3>Success Rate</h3>
                    <div class="number"><?= $summary['success_rate'] ?>%</div>
                </div>
            </div>

            <!-- Test Results -->
            <h2>API Endpoint Test Results</h2>
            <div class="test-results">
                <?php foreach($testResults as $endpoint => $result): ?>
                    <div class="test-card <?= $result['success'] ? 'success' : 'failed' ?>">
                        <div class="test-header">
                            <?= ucfirst(str_replace('_', ' ', $endpoint)) ?>
                        </div>
                        <div class="test-body">
                            <?php if($result['success']): ?>
                                <div class="test-info">
                                    <strong>Status:</strong> ✅ Success
                                </div>
                                <?php if(isset($result['count'])): ?>
                                    <div class="test-info">
                                        <strong>Count:</strong> <?= $result['count'] ?>
                                    </div>
                                <?php endif; ?>
                                <div class="test-info">
                                    <strong>Response:</strong> API call completed successfully
                                </div>
                            <?php else: ?>
                                <div class="test-info">
                                    <strong>Status:</strong> ❌ Failed
                                </div>
                                <div class="test-info">
                                    <strong>Error Code:</strong> <?= isset($result['code']) ? htmlspecialchars($result['code']) : 'N/A' ?>
                                </div>
                                <div class="error-details">
                                    <strong>Error Message:</strong><br>
                                    <?= htmlspecialchars($result['error']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Errors Summary -->
            <?php if(!empty($errors)): ?>
                <div class="errors-section">
                    <h3>Error Summary</h3>
                    <ul class="error-list">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Test Instructions -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3>Test Instructions</h3>
                <p>This dashboard tests all available Management API endpoints to verify that the header fix is working correctly. If you see:</p>
                <ul>
                    <li><strong>High success rate (90%+):</strong> The header fix is working correctly</li>
                    <li><strong>Low success rate with content-type errors:</strong> The header fix may not be working</li>
                    <li><strong>Authentication errors:</strong> Check your Management API credentials</li>
                    <li><strong>Permission errors:</strong> Your M2M application may not have the required scopes</li>
                </ul>
                <p><strong>Note:</strong> Some endpoints may fail due to insufficient permissions or missing data, which is normal. The key indicator is whether you see content-type related errors.</p>
            </div>
        </div>
    </div>
</body>
</html> 