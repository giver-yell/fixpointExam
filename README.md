# フィックスポイント社様筆記試験

- 筆記試験の問題は`question.txt`へ記載

## プログラム実行方法

※`fixpoint/ping.log`のログデータを読み込み、csv 出力を行う

- 設問 1  
  `php question1.php`
- 設問 2  
  `php question2.php [連続タイムアウト回数N]`  
  例）`php question2.php 3`
- 設問 3  
  `php question3.php [連続タイムアウト回数N] [直近ping応答回数m] [平均応答時間上限t]`  
  例）`php question3.php 3 2 8`
- 設問 4  
  `php question4.php [連続タイムアウト回数N] [直近ping応答回数m] [平均応答時間上限t]`  
  例）`php question4.php 3 2 8`

## テスト内容

- 設問 1
  - 1_1
    実行したコマンド：`php question1.php`  
    テスト内容：複数のタイムアウトしたサーバが含まれたログ  
    テストしたログ；`testResult/1/1_1/ping.log`  
    テスト結果；`testResult/1/1_1/1_server_failure_20220213201556.csv`
  - 1_2
    実行したコマンド：`php question1.php`  
    テスト内容：全てのログデータがタイムアウト
    テストしたログ；`testResult/1/1_2/ping.log`  
    テスト結果；`testResult/1/1_2/1_server_failure_20220213202537.csv`
  - 1_3
    実行したコマンド：`php question1.php`  
    テスト内容：全てのログデータがタイムアウトしていない
    テストしたログ；`testResult/1/1_3/ping.log`  
    テスト結果；`testResult/1/1_3/1_server_failure_20220213202814.csv`
  - 1_4
    実行したコマンド：`php question1.php`  
    テスト内容：ログファイルが空
    テストしたログ；`testResult/1/1_4/ping.log`  
    テスト結果；'ログファイルが空です。'が出力される
  - 1_5
    実行したコマンド：`php question1.php`  
    テスト内容：ログファイルが存在しない
    テストしたログ；-  
    テスト結果；'ログファイルがありません。'が出力される
- 設問 2
  - 2_1
    実行したコマンド：`php question2.php 3`  
    テスト内容：連続 N 回以上のタイムアウトがない場合  
    テストしたログ；`testResult/2/2_1/ping.log`  
    テスト結果；`testResult/2/2_1/2_server_failure_20220213203657.csv`
  - 2_2
    実行したコマンド：`php question2.php 3`  
    テスト内容：連続 N 回以上のタイムアウトがある場合  
    テストしたログ；`testResult/2/2_2/ping.log`  
    テスト結果；`testResult/2/2_2/2_server_failure_20220213203912.csv`
  - 2_3
    実行したコマンド：`php question2.php 3`  
    テスト内容：連続 N 回以上のタイムアウトが複数ある場合  
    テストしたログ；`testResult/2/2_3/ping.log`  
    テスト結果；`testResult/2/2_3/2_server_failure_20220213204130.csv`
  - 2_4
    実行したコマンド：`php question2.php 3`  
    テスト内容：連続 N 回以上のタイムアウトがあり、最後のログがタイムアウトの場合、故障期間がわからないものを'undefined'で返す  
    テストしたログ；`testResult/2/2_4/ping.log`  
    テスト結果；`testResult/2/2_4/2_server_failure_20220213204302.csv`
  - 2_5
    実行したコマンド：`php question2.php 3`  
    テスト内容：ログファイルが空  
    テストしたログ；`testResult/2/2_5/ping.log`  
    テスト結果；'ログファイルが空です。'が出力される
  - 2_6
    実行したコマンド：`php question2.php 3`  
    テスト内容：ログファイルが存在しない  
    テストしたログ；-  
    テスト結果；'ログファイルがありません。'が出力される
  - 2_7
    実行したコマンド：`php question2.php`  
    テスト内容：コマンドで引数が渡されない場合  
    テストしたログ；-  
    テスト結果；'パラメータが渡されていません。'が出力される
- 設問 3

  - 3_1
    実行したコマンド：`php question3.php 3 2 8`  
    テスト内容：直近 m 回の平均応答時間が t ミリ秒を超えたサーバがない場合  
    テストしたログ；`testResult/3/3_1/ping.log`  
    テスト結果；`testResult/3/3_1/3_server_overload_20220213210735.csv`
  - 3_2
    実行したコマンド：`php question3.php 3 2 8`  
    テスト内容：直近 m 回の平均応答時間が t ミリ秒を超えたサーバがある場合  
    テストしたログ；`testResult/3/3_2/ping.log`  
    テスト結果；`testResult/3/3_2/3_server_overload_20220213205942.csv`
  - 3_3
    実行したコマンド：`php question3.php 3 2 8`  
    テスト内容：直近 m 回の平均応答時間が t ミリ秒を超えたサーバが複数ある場合  
    テストしたログ；`testResult/3/3_3/ping.log`  
    テスト結果；`testResult/3/3_3/3_server_overload_20220213210937.csv`
  - 3_4
    実行したコマンド：`php question3.php 3 2 8`  
    テスト内容：ログファイルが空  
    テストしたログ；`testResult/3/3_4/ping.log`  
    テスト結果；'ログファイルが空です。'が出力される
  - 3_5
    実行したコマンド：`php question3.php 3 2 8`  
    テスト内容：ログファイルが存在しない  
    テストしたログ；-  
    テスト結果；'ログファイルがありません。'が出力される
  - 3_6
    実行したコマンド：`php question3.php 3 2 `  
    テスト内容：コマンドで引数が渡されない場合  
    テストしたログ；-  
    テスト結果；'与えられたパラメータが適正ではありません。'が出力される

- 設問 4
  - 4_1
    実行したコマンド：`php question4.php 3 2 8`  
    テスト内容：サブネットの故障がない場合  
    テストしたログ；`testResult/4/4_1/ping.log`  
    テスト結果；`testResult/4/4_1/4_switch_failure_20220213211408.csv`
  - 4_2
    実行したコマンド：`php question4.php 3 2 8`  
    テスト内容：サブネットの故障がある場合  
    テストしたログ；`testResult/4/4_2/ping.log`  
    テスト結果；`testResult/4/4_2/4_switch_failure_20220213211633.csv`
  - 4_3
    実行したコマンド：`php question4.php 3 2 8`  
    テスト内容：サブネットの故障が複数ある場合、故障時間がわからない場合'undefined'を返す  
    テストしたログ；`testResult/4/4_3/ping.log`  
    テスト結果；`testResult/4/4_3/4_switch_failure_20220213212101.csv`
  - 4_4
    実行したコマンド：`php question4.php 3 2 8`  
    テスト内容：ログファイルが空  
    テストしたログ；`testResult/4/4_4/ping.log`  
    テスト結果；'ログファイルが空です。'が出力される
  - 4_5
    実行したコマンド：`php question4.php 3 2 8`  
    テスト内容：ログファイルが存在しない  
    テストしたログ；-  
    テスト結果；'ログファイルがありません。'が出力される
  - 4_6
    実行したコマンド：`php question4.php 3 2`  
    テスト内容：コマンドで引数が渡されない場合  
    テストしたログ；-  
    テスト結果；'与えられたパラメータが適正ではありません。'が出力される
