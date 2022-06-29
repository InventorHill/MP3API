<?php
    class Rsa extends BaseController 
    {
        public function rsa256_encrypt($data, $public_key_e, $public_key_n)
        {
            $hash = hash('SHA256', $data);
            return $hash;
            $padded = strval(hexdec($hash));

            $ciphertext = bcpow($padded, $public_key_e);

            $ciphertext = bcmod($ciphertext, $public_key_n);

            echo $ciphertext;

            return dechex(intval($ciphertext));
        }

        public function rsa256_decrypt($data, $private_key, $public_key_n, $signature)
        {
            $hash = hash('SHA256', $data);

            return $hash == $signature;

            $ciphertext = strval(hexdec($signature));

            $padded = bcpow($ciphertext, $private_key);

            $padded = bcmod($padded, $public_key_n);

            $padded = dechex(intval($padded));

            $padded = $data;

            return dechex(intval($padded));
        }

        public function rsa()
        {
            $p_max = isset($_GET['pMax']) ? trim($_GET['pMax']) : 10007;
            $q_max = isset($_GET['qMax']) ? trim($_GET['qMax']) : 10007;

            $p = strval($this->prime($p_max, 5));
            $q = strval($this->prime($q_max, 5));

            $n = bcmul($p, $q);

            $lambda = $this->lcm(bcsub($p, 1), bcsub($q, 1));

            $e = strval($this->eCalc($lambda));

            $d = $this->dCalc($e, $lambda);

            $responseData = "p: $p, q: $q, n: $n, λ(n): $lambda, e: $e, d: $d";



            $this->sendOutput(json_encode(array($arrIndex => $responseData)), array('Content-Type: application/json', $strHeader));
        }

        public function dCalc($a, $b)
        {
            $a = strval($a);
            $b = strval($b);
            $swapped = false;

            if(bccomp($b, $a) == 1)
            {
                $swapped = true;
                $tmp = $b;
                $b = $a;
                $a = $tmp;
            }

            $q_arr = array(0, 0);
            $r_arr = array($a, $b);
            $s_arr = array(1, 0);
            $t_arr = array(0, 1);

            $dDone = false;
            $index = 1;

            while (!$dDone)
            {
                $q = bcdiv($r_arr[$index - 1], $r_arr[$index]);
                $r = bcsub($r_arr[$index - 1], bcmul($q, $r_arr[$index]));
                $s = bcsub($s_arr[$index - 1], bcmul($q, $s_arr[$index]));
                $t = bcsub($t_arr[$index - 1], bcmul($q, $t_arr[$index]));

                $q_arr[] = $q;
                $r_arr[] = $r;
                $s_arr[] = $s;
                $t_arr[] = $t;

                $dDone = $r == '0';
                $index++;
            }

            $index--;

            $coeff1 = $s_arr[$index];
            $coeff2 = $t_arr[$index];

            return $swapped ? $coeff2 : $coeff1;

        }

        public function eCalc($lambda)
        {
            $eDone = false;
            $e = 0;

            while (!$eDone)
            {
                $e = rand(2, bccomp($lambda, 100001) == -1 ? intval($lambda) - 1 : 100000);

                $eDone = $this->gcd($e, $lambda) == '1';
            }

            return $e;
        }

        public function prime($max, $k)
        {
            $prime = false;
            $n = 0;

            $tried = array();

            while (!$prime)
            {
                $prime = true;
                $n = $this->BigRandomNumber('20000000000000000000000000000000000000000', $max);

                $s = 0;
                $d = bcsub($n, '1');

                while(bcmod($d, 2) == 0)
                {
                    $s++;
                    $d = bcdiv($d, '2');
                }

                for($i = 0; $i < $k; $i++)
                {
                    $a = $this->BigRandomNumber('2', $n);
                    $prime = $prime && $this->trial_composite($a, $d, $n, $s);
                }
            }

            return $n;
        }

        public function trial_composite($a, $d, $n, $s)
        {
            $x = bcpowmod($a, $d, $n);
            if ($x == '1')
                return true;
            
            for ($i = 0; $i < $s; $i++)
            {
                $x = bcpowmod($a, bcmul($d, bcpow('2', $i)), $n);
                if ($x == bcsub($n, '1'))
                    return true;
            }

            return false;
        }

        public function BigRandomNumber($min, $max) 
        {
            $min_length = strlen($min) + 1;
            $max_length = strlen($max) - 1;

            if($min_length > $max_length)
            {
                $tmp = $max_length;
                $max_length = $min_length;
                $min_length = $tmp;
            }

            $length = rand($min_length, $max_length) - 1;

            $num = strval(rand(1, 9));

            for($i = 0; $i < $length; $i++)
            {
                $num .= strval(rand(0, 9));
            }

            return $num;
        }

        public function lcm($u, $v)
        {
            $u = strval($u);
            $v = strval($v);

            $dividend = bcmul($u, $v);

            $dividend = bcmul(bccomp($dividend, 0) == -1 ? -1 : 1, $dividend);

            $dividend = bcmul($dividend, bccomp($dividend, '0') < 0 ? '-1' : '1');

            return bcdiv($dividend, $this->gcd($u, $v));
        }

        public function gcd($u, $v)
        {
            if ($u == '0')
                return $v;
            else if ($v == '0')
                return $u;
            else if (!$u || !$v)
                return false;
            
            $i = $this->trailing_zeroes($u);
            $j = $this->trailing_zeroes($v);
            $k = $i <= $j ? $i : $j;

            $i_divisor = bcpow('2', $i);
            $j_divisor = bcpow('2', $j);
            $k_multiplier = bcpow('2', $k);

            $u = bcdiv($u, $i_divisor);
            $v = bcdiv($v, $j_divisor);

            while (true)
            {
                if (bccomp($u, $v) == 1)
                {
                    $tmp = $v;
                    $v = $u;
                    $u = $tmp;
                }

                $v = bcsub($v, $u);

                if ($v == '0')
                    return bcmul($u, $k_multiplier);

                $v_divisor = bcpow('2', $this->trailing_zeroes($v));
                $v = bcdiv($v, $v_divisor);
            }
        }

        public function trailing_zeroes($n)
        {
            if(!$n)
                return false;

            if(bcmod($n, 2) == 1)
                return 0;

            $count = 0;
            while (bcmod($n, 2) == 0)
            {
                $count++;

                $n = bcdiv($n, 2);
            }

            return strval($count);
        }
    }
?>