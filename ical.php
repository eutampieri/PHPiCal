<?php

class iCalendar{
    private $secsAlarm=900;
    function __constructor($secs=900){
        $secsAlarm=intval($secs);
    }
    private function dt($epoch){
        return gmdate("Ymd\THis\Z",$epoch);
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
        $evento=$evento."\r\nUID:".strval(dechex($e["start"]))."00000\r\n";
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
/*
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Computer\, Inc//iCal 1.5//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH*/
/*
BEGIN:VEVENT
DTSTAMP:20040617T213331Z
UID:id
SEQUENCE:0
DTSTART:20040402T150000Z
SUMMARY:Listen to the same song over and over 100 times. Charge iPod. Ma
 ke new playlists in iTunes.
DTEND:20040402T194500
BEGIN:VALARM
TRIGGER:-PT15M
DESCRIPTION:Event reminder
ACTION:DISPLAY
END:VALARM
END:VEVENT
END:VCALENDAR
*/
$cal=new iCalendar();
header("Content-Type: test/calendar");
echo $cal->ical(json_decode('[{"start":1508432795,"end":1508436395,"desc":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse porttitor tincidunt mi posuere.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse."}]',true));