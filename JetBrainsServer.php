<?php

/**
 * 描述...
 * User: qyc <yichao.qin@beibei.com>
 * Date: 2018/8/8 下午10:50
 * @copyright Beidian Limited. All rights reserved.
 */
class JetBrainsServer {

    private $private_key;
    private $public_key;

    public function __construct() {
        $this->private_key = file_get_contents(BASE_PATH . '/rsa_private_key.pem');
        $this->public_key = file_get_contents(BASE_PATH . '/rsa_public_key.pem');
    }

    public function ping($salt) {
        $str = '<PingResponse><message></message><responseCode>OK</responseCode><salt>%s</salt></PingResponse>';

        return $this->sign(sprintf($str, $salt));
    }

    public function obtainTicket($salt) {
        $str = "<ObtainTicketResponse><message></message><prolongationPeriod>607875500</prolongationPeriod><responseCode>OK</responseCode><salt>%s</salt><ticketId>1</ticketId><ticketProperties>licensee=qyc\tlicenseType=0\t</ticketProperties></ObtainTicketResponse>";

        return $this->sign(sprintf($str, $salt));
    }

    public function prolongTicket($salt) {
        $str = '<ProlongTicketResponse><message></message><responseCode>OK</responseCode><salt>%s</salt><ticketId>1</ticketId></ProlongTicketResponse>';

        return $this->sign(sprintf($str, $salt));
    }

    public function releaseTicket($salt) {
        $str = '<ReleaseTicketResponse><message></message><responseCode>OK</responseCode><salt>%s</salt></ReleaseTicketResponse>';

        return $this->sign(sprintf($str, $salt));
    }

    private function sign($str) {
        $signature = $this->rsa_encrypt($str);
        if (!$signature) {
            return '';
        }
        $signatureHex = bin2hex($signature);

        return sprintf("<!-- %s -->\n%s", $signatureHex, $str);
    }

    private function rsa_encrypt($str) {
        $pkeyId = openssl_get_privatekey($this->private_key);
        if (empty ($pkeyId)) {
            return '';
        }
        $signature = '';
        $sign_res = openssl_sign($str, $signature, $pkeyId, OPENSSL_ALGO_MD5);
        //释放
        openssl_free_key($pkeyId);

        return $sign_res ? $signature : '';
    }
}