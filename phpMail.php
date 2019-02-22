<?php

/*****************************************************/
/*                                                   */
/*                  PHPMAIL V1.0                     */
/*              ReCaptchaV2 by Google                */
/*                                                   */
/*                 ** Def Names **                   */
/*                                                   */
/*               Nombre  -> username                 */
/*               Tlf     -> usertlf                  */
/*               Email   -> useremail                */
/*               Mensaje -> usermessage              */
/*                                                   */
/*****************************************************/

class phpMail {

    private $config, $to, $subject, $msg, $from, $name, $demoMode, $dualSend, $oldSend;

    function __construct()
    {
        $this->config = [
            'captcha'           => 1,
            'reCaptcha'         => [
                'secret'        => '',
                'response'      => (isset( $_POST['g-recaptcha-response'])) ? $_POST['g-recaptcha-response']: '',
                'remoteip'      => $_SERVER['REMOTE_ADDR']
            ],
            'developer'         => [
                'demoMode'      => 0,
                'email'         =>'mail@domain.tld',
            ],
            'user'              => [
                'name'          => 'User Name',
                'email'         => 'mail@domain.tld'
            ]
        ];
        $this->demoMode = $this->config['developer']['demoMode'];
        $this->dualSend = 0;
    }

    function __destruct()
    {
        unset(
            $this->config,
            $this->to,
            $this->subject,
            $this->msg,
            $this->from,
            $this->name,
            $this->demoMode,
            $this->dualSend,
            $this->oldSend
        );
    }

    public function setDualSend($dualSend)
    {
        $this->dualSend = $dualSend;
        return $this;
    }

    public function setDemo($demo)
    {
        $this->demoMode = $demo;
        return $this;
    }

    public function userName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    private function checkGoogleReCaptcha()
    {
        $status = 0;
        $ch = curl_init();
        $url = "https://www.google.com/recaptcha/api/siteverify?secret=". $this->config['reCaptcha']['secret'] ."&response=" . $this->config['reCaptcha']['response'] . "&remoteip=" . $this->config['reCaptcha']['remoteip'];
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        $file_contents = json_decode($file_contents);
        if($file_contents->success == false){ $status = 1; }
        return $status;
    }

    private function getHeaders()
    {
        $remite = (empty($this->from)) ? $_POST['useremail']: $this->from;
        $eol="\r\n";
        $headers = "MIME-Version: 1.0".$eol;
        $headers .= "Content-type: text/html; charset=UTF-8".$eol;
        $headers .= "From: $remite" .$eol;
        $headers .= "Return-Path: $remite".$eol;
        return $headers;
    }

    public function message($msg = null)
    {
        if ($msg === null)
        {
            $date = getdate();
            $date = $date['mday']."/".$date['mon']."/".$date['year']." - ".$date['hours'].":".$date['minutes'].":".$date['seconds'];
            $domain = 'http://'.$_SERVER['HTTP_HOST'];
            $msg  = "<b>Nombre :</b> ".$_POST['username']." ".$_POST['userlastname']."<br>";
            $msg .= "<b>Email   :</b> ".$_POST['useremail']."<br>";
            $msg .= "<b>Asunto   :</b> ".$_POST['subject']."<br>";
            $msg .= "<b>Mensaje :</b> ".$_POST['usermessage']."<br>";
            $msg .= "<b>Enviado desde :</b> ".$domain."<br>";
            $msg .= "<b>Fecha envio   :</b> ".$date;
        }
        $this->msg = $msg;
        return $this;
    }

    private function prepare()
    {
        $user = (!empty($this->name)) ? $this->name: $this->config['user']['name'];
        if (
            $this->config['developer']['demoMode'] === 1 && $this->config['developer']['email'] !== '' ||
            !empty($this->demoMode) && $this->demoMode === 1 && $this->config['developer']['email'] !== ''
        ){ $to = $this->config['developer']['email']; }
        else { $to = (!empty($this->to)) ? $this->to: $this->config['user']['email']; }
        $subject = (!empty($this->subject)) ? $this->subject: "[$user] Contacto desde la Web ".$_POST['username'];
        $headers = $this->getHeaders();
        if (empty($this->msg)) { $this->message(); }
        $msg = $this->msg;
        $return = [
            'to'        => $to,
            'subject'   => $subject,
            'headers'   => $headers,
            'msg'       => $msg
        ];
        return $return;
    }

    private function showAlert($status, $msg)
    {
        require_once 'includes/alert.php';
    }

    public function send($alert = 0)
    {
        $status = 0;
        if ($this->config['captcha'] === 1) { $status = $this->checkGoogleReCaptcha(); }
        if ($status === 0 || $this->demoMode === 1 || $this->dualSend === 1 && $this->oldSend === 0)
        {
            $params = $this->prepare();
            $status = (mail($params['to'],$params['subject'],$params['msg'],$params['headers'])) ? 0: 1;
        }
        $this->oldSend = $status;
        switch ($status)
        {
            case 0:
                $msg = ($alert === 0) ? 'Formulario enviado con éxito': $status;
            break;

            case 1:
                $msg = ($alert === 0) ?'Error de validación': $status;
            break;
        }
        if ($alert === 0) { $this->showAlert($status,$msg); }
        else { return $status; }
    }

}
