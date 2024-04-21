<?php
require_once('PHPMailer/src/Exception.php');
require_once('PHPMailer/src/SMTP.php');
require_once('PHPMailer/src/PHPMailer.php');
require_once('db_class.php');
require_once('info.php');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class mail
{
	// メール情報はinfo.phpに記載

	//差出人
	private $fromName = 'オンライン英語';

	//宛先
	private $to = null;
	private $toname = null;

	//件名・本文
	private $subject = '【オンライン英語】';
	private $body = '';

	//宛先を設定するメソッド
	public function setTo($toAddress = '', $toName = null)
	{
		$this->to = $toAddress;
		$this->toname = $toName;
	}

	public function setSubject($newSubject = '')
	{
		$this->subject = $newSubject;
	}

	public function setBody($newBody = '')
	{
		$this->body = $newBody;
	}

	public function send()
	{
		if (empty(MAILFROM)) {
			throw new Exception("送信元アドレスを設定してください。");
		}
		if (empty($this->to)) {
			throw new Exception("宛先アドレスを設定してください。");
		}
		$mail = new PHPMailer(true);
		//メール設定
		$mail->Debugoutput = function ($str, $level) {
		};
		$mail->SMTPDebug = 2; //デバッグ用
		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = MAILHOST;
		$mail->Username = MAILUSERACCOUNT;
		$mail->Password = MAILUSERPASSWORD;
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		$mail->CharSet = "utf-8";
		$mail->Encoding = "base64";
		if (empty($this->fromName)) {
			$this->fromName = "NO NAME";
		}
		$mail->setFrom(MAILFROM, $this->fromName);
		if (empty($this->toname)) {
			$this->toname = "NO NAME";
		}
		$mail->addAddress($this->to, $this->toname);
		$mail->Subject = $this->subject;
		$mail->Body    = $this->body;

		//送信処理
		$mail->send();
	}
	public function sendNewMessage($reciever, $sender, $message)
	{
		$dbConnect = new dbConnect();
		$url = $dbConnect->getURL();

		$body = "こんにちは " . $reciever['name'] . "さん。\n" .
			"メッセージ内容:" . $message . "\n" .
			$url . "\n" .
			"オンライン英語";
		$subject = $sender['name'] . "さんから新着メッセージが届いています。";
		$this->setTo($reciever['mail'], $reciever['name']);
		$this->setSubject($subject);
		$this->setBody($body);
		$this->send();
	}
	public function getSenderMail()
	{
		return MAILFROM;
	}
}
