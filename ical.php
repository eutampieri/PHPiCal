<?php

class iCalendar{
    private $secsAlarm=900;
    function __constructor($secs=900){
        $secsAlarm=intval($secs);
    }
    private function dt($epoch){
        return gmdate("Ymd\THis\Z",$epoch);
    }
    private function id($evento){
        $id=strval(dechex($evento["start"]));
        $id=$id.str_pad(dechex(($evento["end"]-$evento["start"])%4095),3,"0",STR_PAD_LEFT);
        $md=md5($evento["desc"]);
        $crc=0;
        for($i=0;$i<strlen($md);$i++){
            $crc=$crc+ord($md[$i]);
        }
        $crc=$crc%255;
        $id=$id.str_pad(dechex($crc),2,"0",STR_PAD_LEFT);
        return $id;
    }
    private function interval($secs){
        $s=$secs%60;
        $secs=intval($secs/60);
        $m=$secs%60;
        $secs=intval($secs/60);
        $h=$secs%60;
        $secs=intval($secs/60);
        $d=$secs%60;
        $res="-P";
        if($d!=0){
            $res=$res.strval($d)."D";
        }
        $res=$res."T";
        if($h!=0){
            $res=$res.strval($h)."H";
        }if($m!=0){
            $res=$res.strval($m)."M";
        }
        $res=$res.strval($s)."S";
        return $res;
    }
    private function escape($t){
        return str_replace("\n","\\n",str_replace("\"","\\'",str_replace(",","\\,",str_replace(":","\\:",str_replace(";","\\,",str_replace("\\","\\\\",$t))))));
    }
    private function wrapLine($l,$o){
        $l=$this->escape($l);
        if(strlen($l)>(75-$o)){
            $res=substr($l,0,(75-$o));
            for($i=0;$i<(strlen($l)-76+$o)/75;$i++){
                $res=$res."\r\n ".substr($l,(75-$o+$i*75),74);
            }
            return $res;
        }
        return $l;
    }
    private function evento($e){
        $evento="BEGIN:VEVENT\r\nDTSTAMP:".$this->dt(time());
        $evento=$evento."\r\nUID:".$this->id($e)."\r\n";
        $evento=$evento."SEQUENCE:0\r\n";
        $evento=$evento."DTSTART:".$this->dt($e["start"])."\r\n";
        $evento=$evento."SUMMARY:".$this->wrapLine($e["desc"],8)."\r\n";
        $evento=$evento."DTEND:".$this->dt($e["end"])."\r\nBEGIN:VALARM\r\nTRIGGER:";
        $evento=$evento.$this->interval($this->secsAlarm);
        $evento=$evento."\r\nDESCRIPTION:".$this->wrapLine($e["desc"],13)."\r\nACTION:DISPLAY\r\nEND:VALARM\r\nEND:VEVENT\r\n";
        return $evento;
    }
    private $pid="-//ETSoftware//PHPiCal//IT";
    function ical($eventi){
        $vcal="BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:".$this->pid."\r\n";
        $vcal=$vcal."CALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
        foreach($eventi as $ev){
            $vcal=$vcal.$this->evento($ev);
        }
        $vcal=$vcal."END:VCALENDAR\r\n";
        return $vcal;
    }
}