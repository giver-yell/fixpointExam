<?php

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 定数定義
define('UNDEFINED',          'undefined');
define('SERVER_NAME',        'server_name');
define('FAILURE_PERIOD',     'failure_period');
define('OVER_RESPONSE_TIME', 'over_resp_time');

if (empty($argv[1]) || empty($argv[2]) || empty($argv[3])) {
    // パラメータのエラー処理
    echo ('与えられたパラメータが適正ではありません。');
} else {
    // 与えられたパラメータを定数定義
    // タイムアウトの上限N回
    define('TIME_OUT_COUNT_LIMIT',        $argv[1]);
    // 直近m回
    define('RECENT_PING',                 $argv[2]);
    // 平均応答時間の上限tミリ秒
    define('LIMIT_AVERAGE_RESPONSE_TIME', $argv[3]);

    $serverFailureCsvFileName  = '3_server_failure_' .  date('YmdHis') . '.csv';
    $serverOverloadCsvFileName = '3_server_overload_' . date('YmdHis') . '.csv';
    $logFileName = 'ping.log';
    // ログファイル読み取り
    if (file_exists($logFileName)) {
        // ファイル読み取り時、空白行削除
        $logFile = file($logFileName, FILE_IGNORE_NEW_LINES);
        if (!empty($logFile)) {
            // カンマ区切りで配列へ格納
            foreach ($logFile as $key => $log) {
                $logArr[$key] = explode(',', $log);
                $logArrByServer[$logArr[$key][1]][] = $logArr[$key][2];
            }

            // 配列のcount
            $logCount = count($logArr);
            $lastKey  = $logCount - 1;
            foreach ($logArrByServer as $key => $logs) {
                $logCountByServer[$key] = count($logs);
                $lastKeyByServer[$key]  = $logCountByServer[$key] - 1;
            }

            // サーバの故障
            $output   = [];
            foreach ($logArr as $key => $logs) {
                $errCount = 0;
                if ($logs[2] == '-') {
                    // csv出力データの作成
                    // $logs[0]:＜確認日時＞, $logs[1]:＜サーバアドレス＞, $logs[2]:＜応答時間＞
                    for ($i = $key; $i < $logCount; $i++) {
                        if ($logArr[$i][2] != '-') {
                            // 連続N回以上タイムアウトの場合
                            if ($errCount >= TIME_OUT_COUNT_LIMIT) {
                                $output[$key][SERVER_NAME]    = $logArr[$key][1];
                                $output[$key][FAILURE_PERIOD] = $logArr[$i][2];
                                break;
                            } else {
                                break;
                            }
                        }
                        if ($i == $lastKey && $logArr[$lastKey][2] == '-') {
                            // 最後の配列がタイムアウトの場合
                            $output[$key][SERVER_NAME]    = $logArr[$key][1];
                            $output[$key][FAILURE_PERIOD] = UNDEFINED;
                        }
                        $errCount++;
                    }

                    // 故障したサーバのcsv出力
                    $header = [SERVER_NAME, FAILURE_PERIOD];
                    $fp = fopen($serverFailureCsvFileName, 'w');
                    if ($fp) {
                        fputcsv($fp, $header);
                        foreach ($output as $key => $value) {
                            fputcsv($fp, $value);
                        }
                    }
                    fclose($fp);
                }
            }

            // サーバ毎の過負荷状態
            $output   = [];
            foreach ($logArrByServer as $serverName => $respTime) {
                $totalRespTime[$serverName] = 0;
                for ($i = $lastKeyByServer[$serverName]; $i > $lastKeyByServer[$serverName] - RECENT_PING; $i--) {
                    if ($respTime[$i] != '-') {
                        $totalRespTime[$serverName] += $respTime[$i];
                    }
                }
                // 平均応答時間算出（切り上げ）
                // 平均を出す値が全てタイムアウトの場合はサーバ過負荷状態とせず、サーバの故障として出力する
                $aveRespTime[$serverName] = (int)ceil($totalRespTime[$serverName] / RECENT_PING);

                if ($aveRespTime[$serverName] >= LIMIT_AVERAGE_RESPONSE_TIME) {
                    $output[$serverName][SERVER_NAME]        = $serverName;
                    $output[$serverName][OVER_RESPONSE_TIME] = $aveRespTime[$serverName];
                }

                // 故障したサーバのcsv出力
                $header = [SERVER_NAME, OVER_RESPONSE_TIME];
                $fp = fopen($serverOverloadCsvFileName, 'w');
                if ($fp) {
                    fputcsv($fp, $header);
                    foreach ($output as $value) {
                        fputcsv($fp, $value);
                    }
                }
                fclose($fp);
            }
        } else {
            echo 'ログファイルが空です。';
        }
    } else {
        echo 'ログファイルがありません。';
    }
}
