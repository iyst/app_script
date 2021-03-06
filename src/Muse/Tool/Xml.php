<?php


class xml
{
    const FILE_HEADER = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';

    const P_HEADER          = '<plist version="1.0">';
    const P_FOOTER          = '</plist>';
    const P_DICT_HEADER     = '<dict>';
    const P_DICT_FOOTER     = '</dict>';
    const P_C_SF = 'StaffLines';
    const P_C_ST = 'Stem';
    const P_C_BR = 'Bracket';

    const STAFF_LINES_NUM_ONE  = 10;
    const STAFF_LINES_NUM_TWO  = 5;
    const STAFF_LINES_NUM_THREE = 15;
    const STAFF_LINES_NUM_F = 20;

    private static $plist = '';
    private static $spatium = 5;
    private static $system = 0;
    private static $scale = '1.70';
    private static $strokeWidth = 0.40;
    private static $scoreWidth = 0;


    /**
     * @param $data
     * @return mixed
     */
    public static function init($data)
    {
        self::$plist = '';
        $xml        = simplexml_load_string($data,'SimpleXMLElement', LIBXML_PARSEHUGE);
        $xmljson    = json_encode($xml);
        return json_decode($xmljson,true);
    }

    /**
     * 检查指法有问题的曲谱
     * @param $data
     * @return bool
     */
    public static function checkFingering($data)
    {
        $xml = self::init($data);
        $postion = '';

        foreach ($xml['elements']['element'] as $el)
        {
            $muse = $el['@attributes']['measure_no'];
            if (!empty($el['note']['@attributes']))
            {

                if (strlen($el['note']['fingering']) >= 2) {
                    $postion .= '第' .($muse+1).'小节，'.$el['note']['pitch']['step'].$el['note']['pitch']['octave']."\n";
                }
            } else
            {
                if (!is_array($el['note'])) continue;
                foreach ($el['note'] as $note)
                {
                    if (count($note['fingering']) > 1 || strlen($note['fingering']) >= 2) {
                        $postion .= '第' .($muse+1).'小节，'.$note['pitch']['step'].$note['pitch']['octave']."\n";
                    }
                }
            }

        }
        return $postion;
    }

    /**
     * @param $data
     * @return string
     */
    public static function pos($data)
    {
        $xml = self::init($data);
        self::$plist .= '<key>attributes</key><dict>';
        $attr = $xml['attributes']['time'];

        if(isset($attr))
        {
            if(isset($attr['metronome']))
            {
                self::$plist .= '<key>metronome</key><dict>';

                foreach ( $attr['metronome'] as $key => $val) {
                    if($key =='beat-unit-dot') continue;
                    self::$plist .= '<key>'.$key.'</key>';
                    self::$plist .= '<string>'.$val.'</string>';
                }
                self::$plist .= '</dict>';
            }
            unset($attr['metronome']);

            self::$plist .= '<key>time</key><dict>';
            foreach ( $attr as $key => $val) {
                if($key == '@attributes') continue;
                self::$plist .= '<key>'.$key.'</key>';
                self::$plist .= '<string>'.$val.'</string>';
            }
            self::$plist .= '</dict>';
        }
        self::$plist .= '</dict>';

        self::$plist .='<key>notes</key><dict>';

        $elementArray = array();
        $elementObj = $xml['elements']['element'];

        $elementObjs = $elementObj;
        if($elementObj['note']) {
            unset( $xml['elements']['element']);
            $elementObjs[] = $elementObj;
        }
        foreach ($elementObjs as $el)
        {
            $uniqueId = '';
            if(!empty($el['note']['@attributes']))
            {
                $uniqueId .= $el['note']['@attributes']['uniqueId'].' ';
                self::$plist .='<key>'.$el['note']['@attributes']['uniqueId'].'</key><dict>';
                if(!empty($el['note']['fingering'])) {
                    self::$plist .= '<key>fingering</key>';
                    self::$plist .='<string>'.$el['note']['fingering'].'</string>';
                }
                if(!empty($el['note']['alter'])) {
                    self::$plist .= '<key>alter</key>';
                    self::$plist .= '<string>'.$el['note']['alter'].'</string>';
                }
                if(!empty($el['note']['midiValue'])) {
                    self::$plist .= '<key>midiValue</key>';
                    self::$plist .= '<string>'.$el['note']['midiValue'].'</string>';
                }
                if(!empty($el['note']['pitch']['step'])) {
                    self::$plist .= '<key>step</key>';
                    self::$plist .= '<string>'.$el['note']['pitch']['step'].'</string>';
                }
                if(!empty($el['note']['pitch']['octave'])) {
                    self::$plist.='<key>octave</key>';
                    self::$plist.='<string>'.$el['note']['pitch']['octave'].'</string>';
                }
                if(!empty($el['note']['pitch']['alter'])){
                    self::$plist.='<key>alter</key>';
                    self::$plist.='<string>'.$el['note']['pitch']['alter'].'</string>';
                }
                if(!empty($el['note']['type'])) {
                    self::$plist.='<key>type</key>';
                    self::$plist.='<string>'.$el['note']['type'].'</string>';
                }
                if(!empty($el['note']['duration'])) {
                    self::$plist.='<key>duration</key>';
                    self::$plist.='<string>'.$el['note']['duration'].'</string>';
                }
                if(!empty($el['note']['stem'])) {
                    self::$plist.='<key>stem</key>';
                    self::$plist.='<string>'.$el['note']['stem'].'</string>';
                }
                if(!empty($el['note']['staff'])) {
                    self::$plist.='<key>staff</key>';
                    self::$plist.='<string>'.$el['note']['staff'].'</string>';
                }
                if(!empty($el['note']['voice'])) {
                    self::$plist.='<key>voice</key>';
                    self::$plist.='<string>'.$el['note']['voice'].'</string>';
                }
//                if(!empty($el['note']['isgrace'])) {
//                    self::$plist.='<key>isgrace</key>';
//                    self::$plist.='<string>'.$el['note']['isgrace'].'</string>';
//                }
                if(!empty($el['note']['isGraceBefore'])) {
                    self::$plist.='<key>isGraceBefore</key>';
                    self::$plist.='<string>'.$el['note']['isGraceBefore'].'</string>';
                }
                if(!empty($el['note']['isGraceAfter'])) {
                    self::$plist.='<key>isGraceAfter</key>';
                    self::$plist.='<string>'.$el['note']['isGraceAfter'].'</string>';
                }
                if(!empty($el['note']['isTrill'])) {
                    self::$plist.='<key>isTrill</key>';
                    self::$plist.='<string>'.$el['note']['isTrill'].'</string>';
                }
                if(!empty($el['note']['trillDuration'])) {
                    self::$plist.='<key>trillDuration</key>';
                    self::$plist.='<string>'.$el['note']['trillDuration'].'</string>';
                }
                if(!empty($el['note']['isMordent'])) {
                    self::$plist.='<key>isMordent</key>';
                    self::$plist.='<string>'.$el['note']['isMordent'].'</string>';
                }
                if(!empty($el['note']['isTurn'])) {
                    self::$plist.='<key>isTurn</key>';
                    self::$plist.='<string>'.$el['note']['isTurn'].'</string>';
                }
                if(!empty($el['note']['isStaccato'])) {
                    self::$plist.='<key>isStaccato</key>';
                    self::$plist.='<string>'.$el['note']['isStaccato'].'</string>';
                }
                if(!empty($el['note']['isFermata'])) {
                    self::$plist.='<key>isFermata</key>';
                    self::$plist.='<string>'.$el['note']['isFermata'].'</string>';
                }
                if(!empty($el['note']['hand'])) {
                    self::$plist.='<key>hand</key>';
                    self::$plist.='<string>'.$el['note']['hand'].'</string>';
                }
                if(!empty($el['note']['dynamics'])) { // 力度
                    self::$plist.='<key>dynamics</key>';
                    self::$plist.='<string>'.$el['note']['dynamics'].'</string>';
                }
                if(!empty($el['note']['all_duration'])) {
                    self::$plist.='<key>all_duration</key>';
                    self::$plist.='<string>'.$el['note']['all_duration'].'</string>';
                }
                self::$plist.='</dict>';
            }else{
                if(!is_array($el['note'])) continue;
                foreach ($el['note'] as  $note)
                {
                    $uniqueId .= $note['@attributes']['uniqueId'].' ';
                    //var_dump($note);exit;
                    self::$plist.='<key>'.$note['@attributes']['uniqueId'].'</key>';
                    self::$plist.='<dict>';
                    foreach ($note as $_n_k => $_note)
                    {
                        if(!in_array($_n_k,array('@attributes','beam')))
                        {
                            if($_n_k == 'pitch')
                            {
                                /////update
                                if(!empty($el['note']['isGraceBefore'])) {
                                    self::$plist.='<key>isGraceBefore</key>';
                                    self::$plist.='<string>'.$el['note']['isGraceBefore'].'</string>';
                                }
                                if(!empty($el['note']['isGraceAfter'])) {
                                    self::$plist.='<key>isGraceAfter</key>';
                                    self::$plist.='<string>'.$el['note']['isGraceAfter'].'</string>';
                                }
                                ///////////////update
                                if(!empty($_note['step']))
                                {
                                    self::$plist.='<key>step</key>';
                                    self::$plist.='<string>'.$_note['step'].'</string>';
                                }
                                if(!empty($_note['octave']))
                                {
                                    self::$plist.='<key>octave</key>';
                                    self::$plist.='<string>'.$_note['octave'].'</string>';
                                }
                                if(!empty($_note['alter']))
                                {
                                    self::$plist.='<key>alter</key>';
                                    self::$plist.='<string>'.$_note['alter'].'</string>';
                                }
                            }else{
                                self::$plist .= '<key>'.$_n_k.'</key>';
                                self::$plist .= '<string>' . $_note . '</string>';
                            }
                        }
                    }
                    self::$plist.='</dict>';
                }
            }
            $elementArray[$el['@attributes']['id']] = rtrim($uniqueId);
        }
        self::$plist.= '</dict>';

        self::$plist.= '<key>events</key>';
        self::$plist.= '<dict>';
        $event_key = 0;
        $eventObj = $xml['events']['event'];
        if( $eventObj['@attributes'] ){
            $eventObjs[] = $eventObj;
        }else{
            $eventObjs = $eventObj;
        }
        foreach ($eventObjs as $event)
        {

            $elid =$event['@attributes']['elid'];
            self::$plist.='<key>'.$event_key.'</key>';
            self::$plist.='<dict>';
            self::$plist.='<key>elid</key>';
            self::$plist.='<string>'.$elid.'</string>';
            self::$plist.='<key>position</key>';
            self::$plist.='<string>'.$event['@attributes']['position'].'</string>';
            if(!empty($event['@attributes']['sysno']))
            {
                self::$plist.='<key>sysno</key>';
                self::$plist.='<string>'.$event['@attributes']['sysno'].'</string>';
            }
            //update
            $measureNo = (int)$event['@attributes']['measure_no'];
            if(isset($measureNo))
            {
                self::$plist.='<key>measure_no</key>';
                self::$plist.='<string>'.$event['@attributes']['measure_no'].'</string>';
            }
            if(!empty($event['@attributes']['type']))
            {
                self::$plist.='<key>type</key>';
                self::$plist.='<string>'.$event['@attributes']['type'].'</string>';
            }

            self::$plist.='<key>notes</key>';
            self::$plist.='<string>'.$elementArray[$elid].'</string>';
            self::$plist.='</dict>';
            $event_key++;
        }
        self::$plist.='</dict>';

        self::$plist .= '<key>metronomes</key>';
        self::$plist .= '<dict>';
        $metronomes = $xml['metronomes'];
        if(isset($metronomes))
        {
            foreach ($metronomes as $item)
            {
                foreach ($item as $it)
                {
                    self::$plist .= '<key>' . $it['@attributes']['postion'] . '</key>';
                    self::$plist .= '<dict>';
                    self::$plist .= '<key>type</key>';
                    self::$plist .= '<string>' . $it['@attributes']['type'] . '</string>';
                    self::$plist .= '</dict>';
                }
            }
        }
        self::$plist .= '</dict>';

        return self::_form_plist();
    }

    /**
     * @param $data
     * @return string
     */
    public static function svg($data)
    {
        $xml = self::init($data);

        $typeVal  = false;

        //判断是否多行
        foreach ($xml['path'] as $path) {
            if($path['@attributes']['class'] == self::P_C_BR)  $typeVal = true;
        }

        $staffLinesCount = 0;
        $staffPoints = array();

        foreach ( $xml['polyline'] as $polyline)
        {
            $obj = $polyline['@attributes'];
            if($obj['class'] == self::P_C_SF)
            {
                self::$scoreWidth = explode(' ',explode(',', $obj['points'])[1])[1];
                $staffLinesCount++;
                if($staffLinesCount == 1)
                {
                    self::$strokeWidth = $obj['stroke-width']; //得到行高
                    $oneY = explode(' ',explode(',', $obj['points'])[1])[0];
                }
                if($staffLinesCount == 2)
                {
                    $twoY = explode(' ',explode(',', $obj['points'])[1])[0];
                }
                $staffPoints [] = $obj['points'];
            }
        }

        //计算间距
        self::$spatium = $twoY - $oneY;
        //10行 和 5行
        self::$system = $typeVal ?  ($staffLinesCount/self::STAFF_LINES_NUM_ONE) :
            ($staffLinesCount/self::STAFF_LINES_NUM_TWO);

        $staffs = $typeVal ? self::STAFF_LINES_NUM_ONE : self::STAFF_LINES_NUM_TWO;

        //针对3行乐谱
        // self::$system = ($staffLinesCount/self::STAFF_LINES_NUM_THREE);
        // $staffs = self::STAFF_LINES_NUM_THREE;

        //4行曲谱
        // self::$system = ($staffLinesCount/self::STAFF_LINES_NUM_F);
        //$staffs = self::STAFF_LINES_NUM_F;
        self::$plist .= '<key>scoreWidth</key><string>'.self::$scoreWidth.'</string>';
        self::$plist .= '<key>scale</key><string>'.self::$scale.'</string>';
        self::$plist .= '<key>stafflines</key>';
        self::$plist .= '<dict>';
        echo self::$system;
        //计算每个system的by坐标
        for($j =0; $j <= self::$system-1; $j ++)
        {
            foreach ($staffPoints as $key => $itme)
            {
                $firstIndex =   ($j*$staffs)+1;
                $lastIndex  =   ($j*$staffs)+$staffs;
                $index = ($key+1);

                if($index == $firstIndex)
                {
                    $systemChild['x'] = explode(',', $itme)[0];
                    $systemChild['y'] = explode(' ',explode(',', $itme)[1])[0];
                }
                $systemChild['bx'] =$systemChild['x'];

                if(  $index  == $lastIndex )
                {
                    $lastY = explode(' ',explode(',', $itme)[1])[0];
                }
                //todo
                $systemChild['by'] = ($systemChild['y']+(($lastY - $systemChild['y']) / 2 )) - self::$strokeWidth;

                $systemChild['spatium'] = self::$spatium;
            }
            $system[$j+1] =  $systemChild;
        }

        //for stafflines
        for($i = 1; $i <= self::$system; $i++)
        {
            self::$plist .= '<key>'.$i.'</key>';
            self::$plist .= '<dict>';
            foreach ($system[$i] as $key => $sys)
            {
                self::$plist .= '<key>'.$key.'</key>';
                self::$plist .= '<string>'.$sys.'</string>';
            }
            self::$plist .= '</dict>';
        }
        self::$plist .= '</dict>';

        self::$plist .= '<key>stems</key>';
        self::$plist .='<dict>';

        foreach ( $xml['polyline'] as $polyline)
        {
            $obj = $polyline['@attributes'];
            if ($obj['class'] == self::P_C_ST) {
                self::$plist.='<key>'.$obj['notes'].'</key>';
                self::$plist.='<dict>';
                self::$plist.='<key>x1</key>';
                self::$plist.='<string>'.$obj['x1'].'</string>';
                self::$plist.='<key>y1</key>';
                self::$plist.='<string>'.$obj['y1'].'</string>';
                self::$plist.='<key>x2</key>';
                self::$plist.='<string>'.$obj['x2'].'</string>';
                self::$plist.='<key>y2</key>';
                self::$plist.='<string>'.$obj['y2'].'</string>';
                self::$plist.='<key>height</key>';
                self::$plist.='<string>'.$obj['height'].'</string>';
                self::$plist.='</dict>';
            }
        }

        self::$plist.='</dict>';
        return self::_form_plist();
    }

    public static function checkSvg($data)
    {
        $xml = self::init($data);
        return  $xml['polyline'] ? true : false;

    }

    public static function checkSvgColor($data)
    {
        if(strpos($data,'fill="#ff0000"'))return true;
        if(strpos($data,'fill="#148a07"')) return true;
        if(strpos($data,'stroke="#148a07"')) return true;
        if(strpos($data,'stroke="#ff0000"')) return true;
        return false;
    }

    public static function replaceSvg($data)
    {
        $data = str_replace('fill="#ff0000"','fill="#000000"',$data);
        $data = str_replace('fill="#148a07"','fill="#000000"',$data);
        $data = str_replace('stroke="#148a07"','stroke="#000000"',$data);
        $data = str_replace('stroke="#ff0000"','stroke="#000000"',$data);
        return $data;
    }


    public static function _form_plist()
    {
        return self::FILE_HEADER.self::P_HEADER.self::P_DICT_HEADER. self::$plist. self::P_DICT_FOOTER. self::P_FOOTER;
    }

}