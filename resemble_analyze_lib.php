<?php

$moss_url = 'http://moss.stanford.edu/results';

function parse_index($programmingid, $index_file, $max, $lowest) {
    global $CFG;

    $lines = fetch_by_curl($index_file.'/');
    $s = 0;

    foreach ($lines as $line) {
        $m = array();
        switch ($s) {
        case 0:
            if (preg_match('/<TABLE>/', $line)) {
                $s = 1;
                $c = 0;
            }
            break;
        case 1:
            if (preg_match('/^<TR><TD><A HREF="([^"]*)">(\d*)?-(\d*)\.\w* \((\d*)%\)<\/A>/', $line, $m)) {
                $resemble = new object;
                $resemble->programmingid = $programmingid;

                $resemble->submitid1 = $m[3];
                $resemble->percent1 = $m[4];

                $s = 2;
            }
            break;
        case 2:
            if (preg_match('/<TD><A HREF="([^"]*)">(\d*)?-(\d*)\.\w* \((\d*)%\)<\/A>/', $line, $m)) {
                $resemble->submitid2 = $m[3];
                $resemble->percent2 = $m[4];
                $s = 3;
            }
            break;
        case 3:
            if (preg_match('/<TD ALIGN=right>(\d+)/', $line, $m)) {
                $resemble->matchedcount = $m[1];
				if ($resemble->percent1 > $lowest or $resemble->percent2 > $lowest) {
                    $resemble->matchedlines = parse_lines($index_file.'/match'.$c.'-top.html');
                    if (!insert_record('programming_resemble', $resemble)) {
                        printf("Failed to insert record.\n");
                    }
				}
                $c ++;
				echo ".";
                $s = 1;
            }
            break;
        }
    }
}

function parse_lines($topfile) {
    $lines = fetch_by_curl($topfile);
    $s = 0;
    $c = 0;
    $result = '';
    
    foreach($lines as $line) {
        $m = array();
        switch ($s) {
        case 0:
            if (preg_match('/^<TR><TD><A[^>]*>(\d+-\d+)<\/A>/', $line, $m)) {
                $s = 1;
                if ($result != '') $result .= ';';
                $result .= $m[1].',';
            }
            break;
        case 1:
            if (preg_match('/^<TD><A[^>]*>(\d+-\d+)<\/A>/', $line, $m)) {
                $s = 0;
                $result .= $m[1];
            }
            break;
        }
    }
    return $result;
}

function parse_result($programmingid, $url, $max = 0, $lowest = 0) {
    global $CFG, $moss_url;
	
	// delete old moss result
	execute_sql("DELETE FROM {$CFG->prefix}programming_resemble WHERE programmingid=$programmingid");

    parse_index($programmingid, $url, $max, $lowest);
}

function fetch_by_curl($url) {
    global $CFG;

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_TIMEOUT, 30);
    if (isset($CFG->proxyhost) && isset($CFG->proxyport)) {
        curl_setopt($c, CURLOPT_PROXY, $CFG->proxyhost);
        curl_setopt($c, CURLOPT_PROXYPORT, $CFG->proxyport);
        if (isset($CFG->proxyuser) && isset($CFG->proxypass)) {
            curl_setopt($c, CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypass);
        }
    }
    $ret = curl_exec($c);
    curl_close($c);

    return explode("\n", $ret);
}

?>
