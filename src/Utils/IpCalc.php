<?php

namespace Frobou\Utils;

class IpCalc {

    private $ip;
    private $start_ip;
    private $end_ip;
    private $mask;
    private $ip_bin;
    private $use_network;
    private $use_bcast;
    private $msk_bin;
    private $wil_bin;
    private $ntw_bin;
    private $bct_bin;
    private $erro;
    private $hosts;

    public function __construct($ip, $mask, $use_all = false)
    {
        $this->ip = $ip;
        $this->mask = $mask;
        if (is_string($use_all)) {
            if ($use_all == 'true' || $use_all == '1') {
                $this->use_network = true;
                $this->use_bcast = true;
            } else {
                $this->use_network = false;
                $this->use_bcast = false;
            }
        } else {
            $this->use_network = $use_all;
            $this->use_bcast = $use_all;
        }
        if ($mask >= 31) {
            $this->use_network = true;
            $this->use_bcast = true;
        }
        //inicializa os dados
        $this->erro = '';
        $this->ipAddr();
        $this->mask();
        $this->network();
        $this->broadCast();
        $this->wildCard();
        $this->useHost();
    }

    private function invalidIp()
    {
        /* 0.0.0.0/8
          10.0.0.0/8
          14.0.0.0/8
          39.0.0.0/8
          127.0.0.0/8
          128.0.0.0/16
          169.254.0.0/16
          172.16.0.0/12
          191.255.0.0/16
          192.0.2.0/24
          192.88.99.0/24
          192.168.0.0/16
          198.18.0.0/15
          223.255.255.0/24
          224.0.0.0/4
          240.0.0.0/4
          255.255.255.255 */
    }

    private function useHost()
    {
        if ($this->erro != '') {
            return "{$this->erro}";
        }
        //TODO: alterar para calculo de todas as faixas
        $res = [];
//         if ($this->use_network && $this->mask <> 32) {
//             $res[] = bindec($this->ip_bin[0]).'.'.bindec($this->ip_bin[1]).'.'.bindec($this->ip_bin[2]).'.'.bindec($this->ip_bin[3]);
//         }
        $ini = bindec($this->ntw_bin[3]);
        if (!$this->use_network) {
            $ini ++;
        }
        $this->start_ip = bindec($this->ntw_bin[0]) . '.' . bindec($this->ntw_bin[1]) . '.' . bindec($this->ntw_bin[2]) . '.' . $ini;
        $fim = bindec($this->bct_bin[3]);
        if (!$this->use_bcast) {
            $fim --;
        }
        $this->end_ip = bindec($this->ntw_bin[0]) . '.' . bindec($this->ntw_bin[1]) . '.' . bindec($this->ntw_bin[2]) . '.' . $fim;
        for ($i = $ini; $i <= $fim; $i++) {
            if ($this->mask == 32) {
                $ip = bindec($this->ip_bin[0]) . '.' . bindec($this->ip_bin[1]) . '.' . bindec($this->ip_bin[2]) . '.' . bindec($this->ip_bin[3]);
                $this->start_ip = $ip;
                $this->end_ip = $ip;
                $res[] = $ip;
                break;
            }
            $res[] = bindec($this->ntw_bin[0]) . '.' . bindec($this->ntw_bin[1]) . '.' . bindec($this->ntw_bin[2]) . '.' . $i;
        }
//         if ($this->use_bcast && $this->mask <> 32) {
//             $res[] = bindec($this->bct_bin[0]).'.'.bindec($this->bct_bin[1]).'.'.bindec($this->bct_bin[2]).'.'.bindec($this->bct_bin[3]);
//         }
        $this->hosts = $res;
    }

    private function invertBit($bin)
    {
        if ($this->erro != '') {
            return false;
        }
        $not = "";
        for ($i = 0; $i < strlen($bin); $i ++) {
            if ($bin[$i] == 0) {
                $not .= '1';
            }
            if ($bin[$i] == 1) {
                $not .= '0';
            }
        }
        return $not;
    }

    private function toDec($ip)
    {
        if ($this->erro != '') {
            return false;
        }
        $res = '';
        foreach ($ip as $value) {
            $oc = bindec($value);
            $res .= "{$oc}.";
        }
        return substr($res, 0, strlen($res) - 1);
    }

    private function wildCard()
    {
        if ($this->erro != '') {
            return false;
        }
        if (count($this->wil_bin) > 0) {
            return "{$this->wil_bin[0]}.{$this->wil_bin[1]}.{$this->wil_bin[2]}.{$this->wil_bin[3]}";
        }
        foreach ($this->msk_bin as $key => $value) {
            $this->wil_bin[$key] = $this->invertBit($value);
        }
        return "{$this->wil_bin[0]}.{$this->wil_bin[1]}.{$this->wil_bin[2]}.{$this->wil_bin[3]}";
    }

    private function broadCast()
    {
        if ($this->erro != '') {
            return false;
        }
        if (count($this->bct_bin) > 0) {
            if ($this->mask == 32) {
                return "{$this->bct_bin[0]}.{$this->bct_bin[1]}.{$this->bct_bin[2]}.{$this->bct_bin[3]}";
            }
            return "{$this->bct_bin[0]}.{$this->bct_bin[1]}.{$this->bct_bin[2]}.{$this->bct_bin[3]}";
        }
        foreach ($this->ip_bin as $key => $value) {
            $bc = ($value | $this->invertBit($this->msk_bin[$key]));
            $this->bct_bin[$key] = $bc;
        }
        return "{$this->bct_bin[0]}.{$this->bct_bin[1]}.{$this->bct_bin[2]}.{$this->bct_bin[3]}";
    }

    private function network()
    {
        if ($this->erro != '') {
            return false;
        }
        if (count($this->ntw_bin) > 0) {
            if ($this->mask == 32) {
                return "{$this->ntw_bin[0]}.{$this->ntw_bin[1]}.{$this->ntw_bin[2]}.{$this->ntw_bin[3]}";
            }
            return "{$this->ntw_bin[0]}.{$this->ntw_bin[1]}.{$this->ntw_bin[2]}.{$this->ntw_bin[3]}";
        }
        //retorna erro quando nao vier nada entre os pontos
        $this->ntw_bin = explode('.', $this->ipAddr());
        foreach ($this->ntw_bin as $key => $value) {
            if ($value == '') {
                return false;
            }
            $this->ntw_bin[$key] = $value & $this->msk_bin[$key];
        }
//        echo "{$this->ntw_bin[0]}.{$this->ntw_bin[1]}.{$this->ntw_bin[2]}.{$this->ntw_bin[3]}";die;
        return "{$this->ntw_bin[0]}.{$this->ntw_bin[1]}.{$this->ntw_bin[2]}.{$this->ntw_bin[3]}";
    }

    private function mask()
    {
        if ($this->erro != '') {
            return false;
        }
        if (count($this->msk_bin) > 0) {
            return "{$this->msk_bin[0]}.{$this->msk_bin[1]}.{$this->msk_bin[2]}.{$this->msk_bin[3]}";
        }
        $res = '';
        // verifica se a mascara é numerica
        if (!is_numeric($this->mask)) {
            $this->erro = "<pre>Erro: Máscara de rede deve ser numérica, entre 0 e 32<br>Valor recebido: {$this->mask}</pre>";
            return false;
        }
        // verifica se a mascara esta entre 1 e 32
        if ($this->mask < 0 || $this->mask > 32) {
            $this->erro = "<pre>Erro: Máscara de rede deve estar entre 0 e 32<br>Valor recebido: {$this->mask}</pre>";
            return false;
        }
        /* Créditos: Ícaro */
        // gera uma sequencia de x 1's
        for ($i = 1; $i <= $this->mask; $i ++) {
            $res .= '1';
        }
        // preenche o resultado para 32 bits
        $res = str_pad($res, 32, '0', STR_PAD_RIGHT);
        // preenche cada variavel com sua porcao de dados
        $this->msk_bin[0] = substr($res, 0, 8);
        $this->msk_bin[1] = substr($res, 8, 8);
        $this->msk_bin[2] = substr($res, 16, 8);
        $this->msk_bin[3] = substr($res, 24, 8);
        // formata como xxxxxxxx.xxxxxxxx.xxxxxxxx.xxxxxxxx
        return "{$this->msk_bin[0]}.{$this->msk_bin[1]}.{$this->msk_bin[2]}.{$this->msk_bin[3]}";
    }

    private function ipAddr()
    {
        if ($this->erro != '') {
            return false;
        }
        if (count($this->ip_bin) > 0) {
            return "{$this->ip_bin[0]}.{$this->ip_bin[1]}.{$this->ip_bin[2]}.{$this->ip_bin[3]}";
        }
        // quebra o valor informado em partes separadas por '.'
        $_ip = explode('.', $this->ip);
        // verifica se a quantidade de partes é exatamente 4
        if (count($_ip) != 4) {
            $this->erro = "<pre>Erro: Ip deve conter 4 octetos com valores de 0 a 255, separados pelo caracter \".\" (ponto)!<br>Ex: xxx.xxx.xxx.xxx</pre>";
            return false;
        }
        $ct = 0;
        foreach ($_ip as $ip) {
            $ct ++;
            // verifica se cada parte é numerica
            if (!is_numeric($ip)) {
                $this->erro = "<pre>Erro: Octeto deve ser numérico!<br>Valor recebido no octeto {$ct}: {$ip}</pre>";
                return false;
            }
            // verifica se cada parte está entre 0 e 255
            if ($ip < 0 || $ip > 255) {
                $this->erro = "<pre>Erro: Octeto deve estar entre 0 e 255!<br>Valor recebido no octeto {$ct}: {$ip}</pre>";
                return false;
            }
        }
        // coloca cada octeto em sua posicao, adicionando 0 a esquerda até 8 digitos
        $this->ip_bin[0] = sprintf("%08d", decbin($_ip[0]));
        $this->ip_bin[1] = sprintf("%08d", decbin($_ip[1]));
        $this->ip_bin[2] = sprintf("%08d", decbin($_ip[2]));
        $this->ip_bin[3] = sprintf("%08d", decbin($_ip[3]));
        // retorna o ip montado em formato binario
        return "{$this->ip_bin[0]}.{$this->ip_bin[1]}.{$this->ip_bin[2]}.{$this->ip_bin[3]}";
    }

    public function getWildCardBin()
    {
        return $this->wildCard();
    }

    public function getWildCardDec()
    {
        return $this->toDec($this->wil_bin);
    }

    public function getIpBin()
    {
        return $this->ipAddr();
    }

    public function getIpDec()
    {
        return $this->toDec($this->ip_bin);
    }

    public function getMaskBin()
    {
        return $this->mask();
    }

    public function getMaskDec()
    {
        return $this->toDec($this->msk_bin);
    }

    public function getNetworkBin()
    {
        return $this->network();
    }

    public function getNetworkDec()
    {
        return $this->toDec($this->ntw_bin);
    }

    public function getBCastBin()
    {
        return $this->broadCast();
    }

    public function getBCastDec()
    {
        return $this->toDec($this->bct_bin);
    }

    public function getNumberHosts()
    {
        if ($this->erro != '') {
            return false;
        }
        $max = pow(2, 32 - $this->mask);
        if (!$this->use_network) {
            $max --;
        }
        if (!$this->use_bcast) {
            $max --;
        }
        return $max;
    }

    public function getHosts()
    {
        return $this->hosts;
    }

    public function getClass()
    {
        $ip = bindec($this->ip_bin[0]);
        if ($ip <= 127) {
            return 'A';
        } else if ($ip <= 191) {
            return 'B';
        } else if ($ip <= 222) {
            return 'C';
        } else if ($ip <= 239) {
            return 'D';
        } else {
            return 'E';
        }
    }

    public function getStartIp()
    {
        return $this->start_ip;
    }

    public function getEndIp()
    {
        return $this->end_ip;
    }

    public function validate()
    {
        return empty($this->erro);
    }

    public function getError()
    {
        return $this->erro;
    }

    public function getAllData($get_hosts = true)
    {
        $out = [];
        $out['class'] = $this->getClass();
        $out['mask'] = $this->getMaskDec();
        $out['network'] = $this->getNetworkDec();
        $out['broadcast'] = $this->getBCastDec();
        $out['start_ip'] = $this->getStartIp();
        $out['end_ip'] = $this->getEndIp();
        $out['total_hosts'] = $this->getNumberHosts();
        if ($get_hosts) {
            $out['hosts'] = $this->getHosts();
        }
        return $out;
    }

}
