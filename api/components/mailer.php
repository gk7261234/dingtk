<?php
class Mailer
{
    private $host,$username,$password,$port,$from;
    public function __construct($host,$username,$password,$port,$from)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->from = $from;
    }

    public function send($toSend,$subject,$content)
    {
        $transport = Swift_SmtpTransport::newInstance($this->host,$this->port)->setUsername($this->username)->setPassword($this->password);
        //创建mailer对象
        $mailer = Swift_Mailer::newInstance($transport);
        //创建message对象
        $message = Swift_Message::newInstance();
        $message->setSubject($subject)->setBody($content,"text/html","utf-8"); //可自定义样式 略。。。

        //添加附件
//    $attachment = Swift_Attachment::fromPath('xx','file_type')->setFilename('rename');
//    $message->attach($attachment);

        //设置收件人地址
        $message->setTo($toSend);
        //设置发送人地址
        $message->setFrom($this->from);

        //添加抄送人
//    $message->setBcc(['chaosongren@qq.com'=>'Bcc']);
        //回执
//    $message->setReadReceiptTo('receipt@163.com');
        try{
            $result = $mailer->send($message);   //返回结果 true
        }catch (Swift_SwiftException $e){
            $result = $e->getMessage();
        }
        return $result;
    }
}