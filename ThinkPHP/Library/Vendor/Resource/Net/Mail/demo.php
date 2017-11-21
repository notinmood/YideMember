<?php


use Vendor\Resource\Net\Mail\PHPMailer;

/**
 *  使用QQ邮箱发送时成功的；但163邮箱发送失败。(TODO但发送附件有问题)
 * @param unknown $address
 * @param unknown $title
 * @param unknown $message
 * @param string $fromname
 */
function SendMail($address, $title, $message, $fromname = 'NONE')
{
    $toaddress = 'develope@163.com';
    $fromaddress = '9727005@qq.com';
    $fromname = '我是解大然';
    
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->CharSet = 'utf-8'; // C('MAIL_CHARSET');
    $mail->AddAddress($toaddress);
    $mail->Body = 'hello world!';
    $mail->From = $fromaddress; // C('MAIL_ADDRESS');
    $mail->FromName = $fromname;
    $mail->Subject = '我是解大然，祝福你好';
    $mail->Host = 'smtp.qq.com';
    $mail->SMTPAuth = true; // C('MAIL_AUTH');
                            // $mail->SMTPSecure='ssl';
    $mail->Port = 25;
    $mail->Username = '9727005@qq.com';
    $mail->Password = 'qingdao158416'; // C('MAIL_PASSWORD');
    $mail->IsHTML(false);
    // return $mail->Send();
    $result = $mail->Send();
    dump($result);
}