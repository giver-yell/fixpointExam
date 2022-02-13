<?php

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 定数定義
define('UNDEFINED',      'undefined');
define('SERVER_NAME',    'server_name');
define('FAILURE_PERIOD', 'failure_period');

$csvFileName = '1_server_failure_' . date('YmdHis') . '.csv';
$logFileName = 'ping.log';
// ログファイル読み取り
if (file_exists($logFileName)) {
    // ファイル読み取り時、空白行削除
    $logFile = file($logFileName, FILE_IGNORE_NEW_LINES);
    if (!empty($logFile)) {
        // カンマ区切りで配列へ格納
        foreach ($logFile as $key => $log) {
            $logArr[$key] = explode(',', $log);
        }
        // 配列のcount
        $logCount = count($logArr);
        $lastKey  = $logCount - 1;

        // csv出力データの作成
        $output = [];
        foreach ($logArr as $key => $logs) {
            // $logs[0]:＜確認日時＞, $logs[1]:＜サーバアドレス＞, $logs[2]:＜応答時間＞
            if ($logs[2] == '-') {
                $output[$key][SERVER_NAME] = $logs[1];
                // 故障期間の算出
                if ($key == $lastKey) {
                    // 最後の配列
                    $output[$key][FAILURE_PERIOD] = UNDEFINED;
                } elseif ($logArr[$key + 1][2] != '-') {
                    // 次の配列がタイムアウトでない場合
                    $output[$key][FAILURE_PERIOD] = $logArr[$key + 1][2];
                } else {
                    // 次の配列もタイムアウトの場合、その次の配列を確認
                    for ($i = $key; $i < $logCount; $i++) {
                        if ($logArr[$i][2] != '-') {
                            $output[$key][FAILURE_PERIOD] = $logArr[$i][2];
                            break;
                        }
                        if ($i == $lastKey && $logArr[$lastKey][2] == '-') {
                            // 最後の配列がタイムアウトの場合
                            $output[$key][FAILURE_PERIOD] = UNDEFINED;
                        }
                    }
                }
            }
        }

        // 故障したサーバのcsv出力
        $header = [SERVER_NAME, FAILURE_PERIOD];
        $fp = fopen($csvFileName, 'w');
        if ($fp) {
            fputcsv($fp, $header);
            foreach ($output as $key => $value) {
                fputcsv($fp, $value);
            }
        }
        fclose($fp);
    } else {
        echo 'ログファイルが空です。';
    }
} else {
    echo 'ログファイルがありません。';
}
