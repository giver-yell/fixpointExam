<?php

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 定数定義
define('UNDEFINED',           'undefined');
define('SERVER_NAME',         'server_name');
define('SUBNET_NAME',         'subnet_name');
define('FAILURE_PERIOD',      'failure_period');
define('OVER_RESPONSE_TIME',  'over_resp_time');
define('SWITCH_FAILURE_TIME', 'switch_failure_time');
define('SUB_NET_MASK_LIST', [
    8  => '255.0.0.0',
    9  => '255.128.0.0',
    10 => '255.192.0.0',
    11 => '255.224.0.0',
    12 => '255.240.0.0',
    13 => '255.248.0.0',
    14 => '255.252.0.0',
    15 => '255.254.0.0',
    16 => '255.255.0.0',
    17 => '255.255.128.0',
    18 => '255.255.192.0',
    19 => '255.255.224.0',
    20 => '255.255.240.0',
    21 => '255.255.248.0',
    22 => '255.255.252.0',
    23 => '255.255.254.0',
    24 => '255.255.255.0',
    25 => '255.255.255.128',
    26 => '255.255.255.192',
    27 => '255.255.255.224',
    28 => '255.255.255.240',
    29 => '255.255.255.248',
    30 => '255.255.255.252',
    31 => '255.255.255.254',
    32 => '255.255.255.255',
]);

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

    $serverFailureCsvFileName  = '4_server_failure_'  . date('YmdHis') . '.csv';
    $serverOverloadCsvFileName = '4_server_overload_' . date('YmdHis') . '.csv';
    $switchFailureCsvFileName  = '4_switch_failure_'  . date('YmdHis') . '.csv';
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
                $prefix = explode('/', $logArr[$key][1])[1];
                $logArrBySubnet[SUB_NET_MASK_LIST[$prefix]][] = $logArr[$key][2];
            }

            // 配列のcount
            $logCount = count($logArr);
            $lastKey  = $logCount - 1;
            foreach ($logArrByServer as $key => $logs) {
                $logCountByServer[$key] = count($logs);
                $lastKeyByServer[$key]  = $logCountByServer[$key] - 1;
            }
            foreach ($logArrBySubnet as $key => $logs) {
                $logCountBySubnet[$key] = count($logs);
                $lastKeyBySubnet[$key]  = $logCountBySubnet[$key] - 1;
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

            // スイッチの故障
            $output   = [];
            foreach ($logArrBySubnet as $subnetName => $respTimes) {
                for ($i = 0; $i <= $lastKeyBySubnet[$subnetName]; $i++) {
                    $errCount = 0;
                    if ($respTimes[$i] == '-') {
                        // csv出力データの作成
                        for ($j = $i; $j <= $lastKeyBySubnet[$subnetName]; $j++) {
                            $errCount++;
                            if ($respTimes[$j] != '-') {
                                // 連続N回以上タイムアウトの場合
                                if ($errCount >= TIME_OUT_COUNT_LIMIT) {
                                    $output[$subnetName][SUBNET_NAME]         = $subnetName;
                                    $output[$subnetName][SWITCH_FAILURE_TIME] = $respTimes[$j];
                                    break 2;
                                }
                            }
                            if ($j == $lastKeyBySubnet[$subnetName] && $respTimes[$lastKeyBySubnet[$subnetName]] == '-') {
                                // 最後の配列がタイムアウトの場合
                                $output[$subnetName][SUBNET_NAME]         = $subnetName;
                                $output[$subnetName][SWITCH_FAILURE_TIME] = UNDEFINED;
                            }
                        }
                    }
                }

                // 故障したサーバのcsv出力
                $header = [SUBNET_NAME, SWITCH_FAILURE_TIME];
                $fp = fopen($switchFailureCsvFileName, 'w');
                if ($fp) {
                    fputcsv($fp, $header);
                    foreach ($output as $key => $value) {
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
