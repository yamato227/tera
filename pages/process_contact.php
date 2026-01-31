<?php
// ここに受信したいメールアドレスを設定してください
$to_email = "your-receiving-email@example.com"; // ★★★ ここをご自身のメールアドレスに変更 ★★★

// メールの件名
$subject = "蓮通寺 お問い合わせフォームからの連絡";

// 送信元メールアドレスと名前（任意：サーバー設定によっては動作しないこともあります）
// より確実に送信元を制御したい場合は、ホスティングサービスのSMTP設定を確認してください
$from_name = "蓮通寺ウェブサイト";
$from_email = "noreply@your-domain.com"; // ★★★ あなたのドメインのメールアドレスに変更（存在しなくても可の場合あり） ★★★

// 送信元ヘッダー（重要：Fromをフォーム入力者のメールアドレスにすると、迷惑メール判定される可能性が高まります）
// Reply-Toで返信先を指定するのが一般的です。
$headers = "From: " . mb_encode_mimeheader($from_name, 'UTF-8') . " <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $_POST['email'] . "\r\n"; // フォームに入力されたメールアドレスを返信先に設定
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// POSTデータの取得とサニタイズ（基本的なセキュリティ対策）
// HTMLタグなどを無効化し、安全な文字列として扱います
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$name    = isset($_POST['name']) ? h($_POST['name']) : '';
$phone   = isset($_POST['phone']) ? h($_POST['phone']) : '';
$email   = isset($_POST['email']) ? h($_POST['email']) : '';
$message = isset($_POST['message']) ? h($_POST['message']) : '';
$privacy_agree = isset($_POST['privacy-agree']) ? h($_POST['privacy-agree']) : '同意なし';

// バリデーション（必須項目のチェック）
if (empty($name) || empty($email) || empty($message) || $privacy_agree !== '同意済み') {
    // 必須項目が不足している場合、エラーメッセージを表示するか、前のページに戻す
    echo "必須項目が入力されていません。ブラウザの戻るボタンで戻って入力してください。";
    exit;
}

// メール本文の作成
$mail_body = "お問い合わせがありました。\n\n";
$mail_body .= "--------------------------------------------------\n";
$mail_body .= "お名前: " . $name . "\n";
$mail_body .= "電話番号: " . $phone . "\n";
$mail_body .= "メールアドレス: " . $email . "\n";
$mail_body .= "プライバシーポリシー同意: " . $privacy_agree . "\n";
$mail_body .= "お問い合わせ内容:\n" . $message . "\n";
$mail_body .= "--------------------------------------------------\n";

// 日本語メールのための文字エンコーディング設定
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// メール送信
// mail()関数の第4引数にヘッダー、第5引数に-fオプションを指定すると、
// サーバーによってはFromアドレスの書き換えができる場合があります。
// ただし、この部分はサーバー設定に大きく依存します。
$mail_sent = mb_send_mail($to_email, $subject, $mail_body, $headers);

// 送信結果に応じた処理
if ($mail_sent) {
    // 送信成功
    header("Location: contact_thanks.html"); // サンクスページへリダイレクト
    exit;
} else {
    // 送信失敗
    echo "メールの送信に失敗しました。お手数ですが、もう一度お試しいただくか、お電話でお問い合わせください。";
    // エラーログの記録など、詳細なデバッグ情報を残すことも検討
    error_log("メール送信失敗: " . $to_email . "へのメール送信に失敗しました。");
    exit;
}
?>
