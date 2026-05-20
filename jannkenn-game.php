<?php
// セッションを開始して、勝敗数や現在の画面を記憶できるようにする
session_start();

// 手の定義
$hands = [0 => 'グー', 1 => 'チョキ', 2 => 'パー'];

// 初期設定（ゲームをリセットする関数）
function reset_game() {
    $_SESSION['screen'] = 'title'; // 最初の画面
    $_SESSION['wins'] = 0;         // プレイヤーの勝ち数
    $_SESSION['losses'] = 0;       // プレイヤーの負け数
    $_SESSION['player_hand'] = null;
    $_SESSION['com_hand'] = null;
    $_SESSION['result_message'] = '';
}

// セッションが空、またはリセットボタンが押されたら初期化
if (!isset($_SESSION['screen']) || isset($_POST['reset'])) {
    reset_game();
}

// ーーー 画面遷移とアクションの処理 ーーー
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. タイトル画面からゲーム開始
    if (isset($_POST['start_game'])) {
        $_SESSION['screen'] = 'game';
    }

    // 2. じゃんけんがポンと出されたとき
    if (isset($_POST['hand']) && $_SESSION['screen'] === 'game') {
        $player_hand_key = (int)$_POST['hand'];
        $com_hand_key = array_rand($hands);
        
        $_SESSION['player_hand'] = $player_hand_key;
        $_SESSION['com_hand'] = $com_hand_key;

        // 勝敗判定
        if ($player_hand_key === $com_hand_key) {
            $_SESSION['result_message'] = 'あいこです！';
        } elseif (
            ($player_hand_key === 0 && $com_hand_key === 1) || // グー vs チョキ
            ($player_hand_key === 1 && $com_hand_key === 2) || // チョキ vs パー
            ($player_hand_key === 2 && $com_hand_key === 0)    // パー vs グー
        ) {
            $_SESSION['result_message'] = 'あなたの勝ちです！🎉';
            $_SESSION['wins']++;
        } else {
            $_SESSION['result_message'] = 'あなたの負けです...😢';
            $_SESSION['losses']++;
        }

        // 終了条件チェック（例：3回勝つか負けるか）
        if ($_SESSION['wins'] >= 3) {
            $_SESSION['screen'] = 'clear';
        } elseif ($_SESSION['losses'] >= 3) {
            $_SESSION['screen'] = 'over';
        }
    }
}

// 現在の状態を使いやすいように変数に代入
$current_screen = $_SESSION['screen'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PHP本格じゃんけんゲーム</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin-top: 50px; background-color: #f0f4f8; color: #333; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { padding: 12px 24px; font-size: 18px; margin: 5px; cursor: pointer; border: none; border-radius: 5px; background-color: #007bff; color: white; transition: 0.2s; }
        .btn:hover { background-color: #0056b3; }
        .btn-hand { background-color: #e2e8f0; color: #333; font-size: 24px; }
        .btn-hand:hover { background-color: #cbd5e1; }
        .btn-reset { background-color: #dc3545; }
        .btn-reset:hover { background-color: #bd2130; }
        .result-box { margin-top: 20px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; }
        .score { font-size: 18px; font-weight: bold; margin: 20px 0; color: #475569; }
        .screen-clear { color: #28a745; }
        .screen-over { color: #dc3545; }
    </style>
</head>
<body>

<div class="container">

    <!-- 1. タイトル画面 -->
    <?php if ($current_screen === 'title'): ?>
        <h1>✊✌️✋ じゃんけんクエスト</h1>
        <p>3回勝てばゲームクリア！<br>3回負けるとゲームオーバー！</p>
        <form action="" method="post">
            <button type="submit" name="start_game" class="btn">ゲームを始める</button>
        </form>

    <!-- 2. ゲーム画面 -->
    <?php elseif ($current_screen === 'game'): ?>
        <h1>じゃんけん勝負！</h1>
        <p class="score">現在の戦績: <?php echo $_SESSION['wins']; ?> 勝 | <?php echo $_SESSION['losses']; ?> 敗</p>
        <p>出す手を選んでください：</p>

        <!-- 手を選択するフォーム -->
        <form action="" method="post">
            <button type="submit" name="hand" value="0" class="btn btn-hand">✊</button>
            <button type="submit" name="hand" value="1" class="btn btn-hand">✌️</button>
            <button type="submit" name="hand" value="2" class="btn btn-hand">✋</button>
        </form>

        <!-- 今回の勝負の結果表示 -->
        <?php if ($_SESSION['player_hand'] !== null): ?>
            <div class="result-box">
                <p>あなた: <strong><?php echo $hands[$_SESSION['player_hand']]; ?></strong></p>
                <p>相手(COM): <strong><?php echo $hands[$_SESSION['com_hand']]; ?></strong></p>
                <h3><?php echo $_SESSION['result_message']; ?></h3>
            </div>
        <?php endif; ?>

    <!-- 3. ゲームクリア画面 -->
    <?php elseif ($current_screen === 'clear'): ?>
        <h1 class="screen-clear">🎉 GAME CLEAR! 🎉</h1>
        <p>おめでとうございます！見事3勝を達成しました！</p>
        <p class="score">最終戦績: <?php echo $_SESSION['wins']; ?> 勝 <?php echo $_SESSION['losses']; ?> 敗</p>
        <form action="" method="post">
            <button type="submit" name="reset" class="btn">もう一度遊ぶ</button>
        </form>

    <!-- 4. ゲームオーバー画面 -->
    <?php elseif ($current_screen === 'over'): ?>
        <h1 class="screen-over">💀 GAME OVER 💀</h1>
        <p>残念...相手に3勝されてしまいました。</p>
        <p class="score">最終戦績: <?php echo $_SESSION['wins']; ?> 勝 <?php echo $_SESSION['losses']; ?> 敗</p>
        <form action="" method="post">
            <button type="submit" name="reset" class="btn btn-reset">タイトルに戻る</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>
