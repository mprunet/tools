<?php

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-Type: text/plain');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function cidrToRegexp($cidrIn, $debug=false) {
    $cidr=explode("/", $cidrIn);
    $ip=$cidr[0];
    if (count($cidr)>1) {
        $mask=intval($cidr[1]);
    } else {
        $mask=32;
    }
    $netmask = ((1<<32) -1) << (32-$mask);
    $inetmask=$netmask ^ ((1<<32) -1);
    if ($debug) {
        echo "INPUT  : ".$ip."/".$mask.PHP_EOL;
    }
    $from=ip2long($ip) & $netmask;
    $ipfrom=long2ip($from);
    $to=$from ^ $inetmask;
    $ipto=long2ip($to);
    if ($debug) {
        echo "FROM   : ".$ipfrom.PHP_EOL;
        echo "TO     : ".$ipto.PHP_EOL;
    }
    $ipfromE=explode(".",$ipfrom);
    $iptoE=explode(".",$ipto);
    $fullSubnet="\\.".computeRegexp(0,255);
    
    if ($mask<8) {
        $regexp=computeRegexp(intval($ipfromE[0]),intval($iptoE[0])).$fullSubnet.$fullSubnet.$fullSubnet;
    } elseif ($mask<16) {
        $regexp=$ipfromE[0]."\\.".computeRegexp(intval($ipfromE[1]),intval($iptoE[1])).$fullSubnet.$fullSubnet;
    } elseif ($mask<24) {
        $regexp=$ipfromE[0]."\\.".$ipfromE[1]."\\.".computeRegexp(intval($ipfromE[2]),intval($iptoE[2])).$fullSubnet;
    } else {
        $regexp=$ipfromE[0]."\\.".$ipfromE[1]."\\.".$ipfromE[2]."\\.".computeRegexp(intval($ipfromE[3]),intval($iptoE[3]));
    }
    if ($debug) {
        echo "REGEXP : ".$regexp.PHP_EOL;
    }
    return $regexp;
} 

function computeRegexp($min, $max, $tour=0) {
    $minInit=$min;
    $maxInit=$max;
    $ret="";
    if ($max<$min) {
        $ret="";
    } else if ($min==$max) {
        if ($min==0 && $tour>0) {
            $ret="";
        } else {
            $ret=strval($min);
        }
    } else {
        $suffix=array();
        $minD=intval($min/10);
        $maxD=intval($max/10);
        $minM=$min%10;
        $maxM=$max%10;
        if ($minD == $maxD) {
            array_push($suffix,computeRegexp($minD,$maxD,$tour+1)."[$minM-$maxM]");
        } else {
            $endpush=null;
            if ($minM != 0) {
                array_push($suffix,computeRegexp($minD,$minD,$tour+1)."[$minM-9]");
                $minD=$minD+1;
                $min=$minD*10;
                $minM=0;
            }
            if ($maxM != 9) {
                $endpush=computeRegexp($maxD,$maxD,$tour+1)."[0-$maxM]";
                $maxD=$maxD-1;
                $max=$maxD*10;
                $maxM=9;
            }
            if ($minD == 0) {
                
                if ($tour==0) {
                    $topush="[0-9]";
                } else {
                    $topush="[1-9]";
                }
                if ( $maxD != 0) {
                    $prefix=computeRegexp($minD+1,$maxD,$tour+1);
                    if ($prefix!="" && $tour==0) {
                        $topush=null;
                        if (substr($prefix,0,1)!=="(" || substr($prefix,-1,1)!==")") {
                            $prefix="(".$prefix.")";
                        }
                        array_push($suffix,$prefix."?[0-9]");    
                    } else {
                        array_push($suffix,$prefix."[0-9]");    
                    }
                } 
                if ($topush!==null) {
                    array_push($suffix,$topush);
                }
            } elseif ($minD<=$maxD) {
                array_push($suffix,computeRegexp($minD,$maxD,$tour+1)."[0-9]");
            }
            if ($endpush!==null) {
                array_push($suffix, $endpush);                
            }
            
        }
        if (count($suffix)>1) {
            $ret="(".implode("|", $suffix).")";
        } else {
            $ret=$suffix[0];
        }
    }
    return $ret;
}
$displayMan=true;
if (array_key_exists('test', $_GET)) {
    $displayMan=false;
    echo "START TEST".PHP_EOL;
    for ($i=0; $i<=255;$i++) {
        echo "$i-x";
        $ok=true;
        for ($j=$i; $j<=255;$j++) {
            $rex=computeRegexp($i,$j);
            for ($x=0; $x<=255;$x++) {
                $match=preg_match("/^".$rex."$/", strval($x));
                if ($match === 1 && ($x < $i || $x > $j)) {
                    echo "Algorithm ERROR $i MATCH $rex".PHP_EOL;
                } else if ($match === 0 && ($x>=$i && $x <= $j)) {
                    echo "Algorithm ERROR $i DONT MATCH $rex".PHP_EOL;;
                }
            }
        }
        if ($ok) {
            echo " OK".PHP_EOL;
        }
        flush();
    }
    echo "END TEST".PHP_EOL;
}
if (array_key_exists('cidr', $_GET)) {
    $displayMan=false;
    cidrToRegexp($_GET['cidr'], true);
} 
if ($displayMan) {
    echo "Man page : Convert a CIDR Address to a REGEXP".PHP_EOL;
    echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."?test=true&cidr=10.10.10.56/28".PHP_EOL;
    echo "test: if specified self test algortihm".PHP_EOL;
    echo "cidr: <MANDATORY> CIDR to convert to REGEXP".PHP_EOL.PHP_EOL;
}

