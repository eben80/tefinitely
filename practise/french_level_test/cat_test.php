<?php
require_once '../../api/auth_check.php';
checkAccess(false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaptive French Level Test (CAT)</title>
    <?php require_once '../../api/version_helper.php'; ?>
    <link rel="stylesheet" href="<?= asset_v('css/toast.css') ?>">
    <link rel="stylesheet" href="<?= asset_v('css/main.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .test-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); min-height: 400px; display: flex; flex-direction: column; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem; }
        .stem { font-size: 1.4rem; font-weight: 600; margin-bottom: 2rem; color: #333; line-height: 1.4; }
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .option-btn { background: #f8f9fa; border: 2px solid #dee2e6; padding: 1.5rem; border-radius: 8px; cursor: pointer; text-align: left; font-size: 1.1rem; transition: all 0.2s; display: flex; align-items: center; gap: 1rem; }
        .option-btn:hover { border-color: #007bff; background: #eef6ff; transform: translateY(-2px); }
        .option-btn.selected { border-color: #007bff; background: #eef6ff; }
        .option-label { width: 30px; height: 30px; background: #fff; border: 1px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; }
        .progress-container { margin-top: auto; padding-top: 2rem; }
        .progress-bar-bg { height: 10px; background: #eee; border-radius: 5px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: #007bff; width: 0%; transition: width 0.5s; }
        .stats { display: flex; gap: 2rem; font-size: 0.9rem; color: #666; margin-top: 0.5rem; }
        .result-card { text-align: center; padding: 3rem 1rem; }
        .result-level { font-size: 4rem; font-weight: bold; color: #28a745; margin: 1rem 0; }
        .spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin: 2rem auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @media (max-width: 600px) { .options-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo"></a>
    </header>
    <div id="toast-container"></div>

    <div class="container">
        <div id="test-view" class="test-container">
            <!-- Test content will be injected here -->
             <div class="spinner"></div>
             <p style="text-align: center;">Initializing adaptive engine...</p>
        </div>
    </div>

    <script src="<?= asset_v('js/toast.js') ?>"></script>
    <script src="<?= asset_v('js/auth.js') ?>"></script>
    <script src="<?= asset_v('js/cat_test.js') ?>"></script>
</body>
</html>
