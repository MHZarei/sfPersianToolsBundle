<?php
/**
 * Copyright (C) 2013  Mohammad Niknam (intuxicated.ir)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Mohammad niknam
 * @author Vahid Sohrablou (IranPHP.org)
 */

namespace Intuxicated\PersianToolsBundle\Lib;

class PersianTools {

    private $pdate_month_name = array('', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند');
    private $pdate_week_name = array('شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنج شنبه', 'جمعه');
    private $pdate_month_days = array(0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    private $persian_digits    =    array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
    private $arabic_digits    =    array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
    private $english_digits    =    array('0','1','2','3','4','5','6','7','8','9');

    private $arabic_letters = array('ۀ','ى','ي','ك');
    private $persian_letters = array('ه','ی','ی','ک');


    function pletter($string){
        $string = str_replace($this->arabic_letters,$this->persian_letters,$string);

        return $string;
    }

    function pnumber($string){
        $string = str_replace($this->english_digits, $this->persian_digits, $string);
        $string = str_replace($this->arabic_digits, $this->persian_digits, $string);

        return $string;
    }


    function pdate($format, $timestamp = NULL)
    {
        $pdate_month_name = $this->pdate_month_name;
        $pdate_week_name = $this->pdate_week_name;
        $pdate_month_days = $this->pdate_month_days;

        if(!$timestamp)
        {
            $timestamp = time();
        }

        # Create need date parametrs
        list($gYear, $gMonth, $gDay, $gWeek) = explode('-', date('Y-m-d-w', $timestamp));
        list($pYear, $pMonth, $pDay) = $this->gregorian_to_jalali($gYear, $gMonth, $gDay);
        $pWeek = ($gWeek + 1);

        if($pWeek >= 7)
        {
            $pWeek = 0;
        }

        if($format == '\\')
        {
            $format = '//';
        }

        $lenghFormat = strlen($format);
        $i = 0;
        $result = '';

        while($i < $lenghFormat)
        {
            $par = $format{$i};
            if($par == '\\')
            {
                $result .= $format{++ $i};
                $i ++;
                continue;
            }
            switch($par)
            {
                # Day
                case 'd':
                    $result .= (($pDay < 10) ? ('0' . $pDay) : $pDay);
                    break;

                case 'D':
                    $result .= substr($pdate_week_name[$pWeek], 0, 2);
                    break;

                case 'j':
                    $result .= $pDay;
                    break;

                case 'l':
                    $result .= $pdate_week_name[$pWeek];
                    break;

                case 'N':
                    $result .= $pWeek + 1;
                    break;

                case 'w':
                    $result .= $pWeek;
                    break;

                case 'z':
                    $result .= $this->DayOfYear($pMonth, $pDay);
                    break;

                case 'S':
                    $result .= 'م';
                    break;

                # Week
                case 'W':
                    $result .= ceil($this->DayOfYear($pMonth, $pDay) / 7);
                    break;

                # Month
                case 'F':
                    $result .= $pdate_month_name[$pMonth];
                    break;

                case 'm':
                    $result .= (($pMonth < 10) ? ('0' . $pMonth) : $pMonth);
                    break;

                case 'M':
                    $result .= substr($pdate_month_name[$pMonth], 0, 6);
                    break;

                case 'n':
                    $result .= $pMonth;
                    break;

                case 't':
                    $result .= (($this->isKabise($pYear) && ($pMonth == 12)) ? 30 : $pdate_month_days[$pMonth]);
                    break;

                # Years
                case 'L':
                    $result .= (int)$this->isKabise($pYear);
                    break;

                case 'Y':
                case 'o':
                    $result .= $pYear;
                    break;

                case 'y':
                    $result .= substr($pYear, 2);
                    break;

                # Time
                case 'a':
                case 'A':
                    if(date('a', $timestamp) == 'am')
                    {
                        $result .= (($par == 'a') ? '.ق.ظ' : 'قبل از ظهر');
                    }
                    else
                    {
                        $result .= (($par == 'a') ? '.ب.ظ' : 'بعد از ظهر');
                    }
                    break;

                case 'B':
                case 'g':
                case 'G':
                case 'h':
                case 'H':
                case 's':
                case 'u':
                case 'i':
                    # Timezone
                case 'e':
                case 'I':
                case 'O':
                case 'P':
                case 'T':
                case 'Z':
                    $result .= date($par, $timestamp);
                    break;

                # Full Date/Time
                case 'c':
                    $result .= ($pYear . '-' . $pMonth . '-' . $pDay . ' ' . date('H:i:s P', $timestamp));
                    break;

                case 'r':
                    $result .= (substr($pdate_week_name[$pWeek], 0, 2) . '، ' . $pDay . ' ' . substr($pdate_month_name[$pMonth], 0, 6) . ' ' . $pYear . ' ' . date('H::i:s P', $timestamp));
                    break;

                case 'U':
                    $result .= $timestamp;
                    break;

                default:
                    $result .= $par;
            }
            $i ++;
        }

        return $result;
    }

    function pstrftime($format, $timestamp = NULL)
    {
        $pdate_month_name = $this->pdate_month_name;
        $pdate_week_name = $this->pdate_week_name;
        $pdate_month_days = $this->pdate_month_days;

        if(!$timestamp)
        {
            $timestamp= time();
        }

        # Create need date parametrs
        list($gYear, $gMonth, $gDay, $gWeek) = explode ('-', date('Y-m-d-w', $timestamp));
        list($pYear, $pMonth, $pDay) = $this->gregorian_to_jalali($gYear, $gMonth, $gDay);
        $pWeek = $gWeek + 1;

        if($pWeek >= 7)
        {
            $pWeek = 0;
        }

        $lenghFormat = strlen($format);
        $i = 0;
        $result = '';

        while($i < $lenghFormat)
        {
            $par = $format{$i};
            if($par == '%')
            {
                $type= $format{++ $i};
                switch($type)
                {
                    # Day
                    case 'a':
                        $result .= substr($pdate_week_name[$pWeek], 0, 2);
                        break;

                    case 'A':
                        $result .= $pdate_week_name[$pWeek];
                        break;

                    case 'd':
                        $result .= (($pDay < 10) ? '0' . $pDay : $pDay);
                        break;

                    case 'e':
                        $result.= $pDay;
                        break;

                    case 'j':
                        $dayinM = $this->DayOfYear($pMonth, $pDay);
                        $result .= (($dayinM < 10) ? '00' . $dayinM : ($dayinM < 100) ? '0' . $dayinM : $dayinM);
                        break;

                    case 'u':
                        $result .= $pWeek + 1;
                        break;

                    case 'w':
                        $result .= $pWeek;
                        break;

                    # Week
                    case 'U':
                        $result .= floor($this->DayOfYear($pMonth, $pDay) / 7);
                        break;

                    case 'V':
                    case 'W':
                        $result .= ceil($this->DayOfYear($pMonth, $pDay) / 7);
                        break;

                    # Month
                    case 'b':
                    case 'h':
                        $result .= substr($pdate_month_name[$pMonth], 0, 6);
                        break;

                    case 'B':
                        $result .= $pdate_month_name[$pMonth];
                        break;

                    case 'm':
                        $result .= (($pMonth < 10) ? '0' . $pMonth : $pMonth);
                        break;

                    # Year
                    case 'C':
                        $result .= ceil($pYear / 100);
                        break;

                    case 'g':
                    case 'y':
                        $result .= substr($pYear, 2);
                        break;

                    case 'G':
                    case 'Y':
                        $result .= $pYear;
                        break;

                    # Time
                    case 'H':
                    case 'I':
                    case 'l':
                    case 'M':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'X':
                    case 'z':
                    case 'Z':
                        $result .= strftime('%' . $type, $timestamp);
                        break;

                    case 'p':
                    case 'P':
                    case 'r':
                        if(date('a', $timestamp) == 'am')
                        {
                            $result .= (($type == 'p') ? 'ق.ظ' : ($type == 'P') ? 'قبل از ظهر' : strftime("%I:%M:%S قبل از ظهر", $timestamp));
                        }
                        else
                        {
                            $result .= (($type == 'p') ? 'ب.ظ' : ($type == 'P') ? 'بعد از ظهر' : strftime("%I:%M:%S بعد از ظهر", $timestamp));
                        }
                        break;

                    # Time and Date Stamps
                    case 'c':
                        $result .= (substr($pdate_week_name[$pWeek], 0, 2) . ' ' . substr($pdate_month_name[$pMonth], 0, 6) . ' ' . $pDay . ' ' . strftime("%T", $timestamp) . ' ' . $pYear);
                        break;

                    case 'D':
                    case 'x':
                        $result .= ((($pMonth < 10) ? '0' . $pMonth : $pMonth) . '-' . (($pDay < 10) ? '0' . $pDay : $pDay) . '-' . substr($pYear, 2));
                        break;

                    case 'F':
                        $result .= ($pYear . '-' . (($pMonth < 10) ? '0' . $pMonth:$pMonth) . '-' . (($pDay < 10) ? '0' . $pDay : $pDay));
                        break;

                    case 's':
                        $result .= $timestamp;
                        break;

                    # Miscellaneous
                    case 'n':
                        $result .= "\n";
                        break;

                    case 't':
                        $result .= "\t";
                        break;

                    case '%':
                        $result .= '%';
                        break;

                    default:
                        $result .= '%' . $type;
                }
            }
            else
            {
                $result .= $par;
            }
            $i ++;
        }

        return $result;
    }

    function DayOfYear($pMonth, $pDay)
    {
        $pdate_month_days = $this->pdate_month_days;
        $days = 0;

        for($i = 1; $i < $pMonth; $i ++)
        {
            $days += $pdate_month_days[$i];
        }

        return ($days + $pDay);
    }

    function isKabise($year)
    {
        $mod = ($year % 33);

        if(($mod == 1) or ($mod == 5) or ($mod == 9) or ($mod == 13) or ($mod == 17) or ($mod == 22) or ($mod == 26) or ($mod == 30))
        {
            return 1;
        }

        return 0;
    }

    function pmktime($hour = 0, $minute = 0, $second = 0, $month = 0, $day = 0, $year = 0, $is_dst = -1)
    {
        if(($hour == 0) && ($minute == 0) && ($second == 0) && ($month == 0) && ($day == 0) && ($year == 0))
        {
            return time();
        }

        list($year, $month, $day) = $this->jalali_to_gregorian($year, $month, $day);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    function pcheckdate($month, $day, $year)
    {
        if(($month < 1) || ($month > 12) || ($year < 1) || ($year > 32767) || ($day < 1))
        {
            return 0;
        }

        $pdate_month_days = $this->pdate_month_days;

        if($day > $pdate_month_days[$month])
        {
            if(($month != 12) || ($day != 30) || !$this->isKabise($year))
            {
                return 0;
            }
        }

        return 1;
    }

    function pgetdate($timestamp = NULL)
    {
        if(!$timestamp)
        {
            $timestamp = mktime();
        }

        list($seconds, $minutes, $hours, $mday, $wday, $mon, $year, $yday, $weekday, $month) = explode('-', pdate('s-i-G-j-w-n-Y-z-l-F', $timestamp));
        return array(0=>$timestamp, 'seconds'=>$seconds, 'minutes'=>$minutes, 'hours'=>$hours, 'mday'=>$mday, 'wday'=>$wday, 'mon'=>$mon, 'year'=>$year, 'yday'=>$yday, 'weekday'=>$weekday, 'month'=>$month);
    }

# Copyright (C) 2000 Roozbeh Pournader and Mohammad Tou'si
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# A copy of the GNU General Public License is available from:
#
# <a href="http://gnu.org/copyleft/gpl.html" target="_blank">http://gnu.org/copyleft/gpl.html</a>

    function div($a, $b)
    {
        return (int)($a / $b);
    }

    function gregorian_to_jalali($g_y, $g_m, $g_d)
    {
        static $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        static $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $g_day_no = (365 * $gy + $this->div($gy + 3, 4) - $this->div($gy + 99, 100) + $this->div($gy + 399, 400));

        for($i = 0; $i < $gm; ++ $i)
        {
            $g_day_no += $g_days_in_month[$i];
        }

        if($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
            # leap and after Feb
            $g_day_no ++;
        $g_day_no += $g_d - 1;
        $j_day_no = $g_day_no - 79;
        $j_np = $this->div($j_day_no, 12053); # 12053 = (365 * 33 + 32 / 4)
        $j_day_no = $j_day_no % 12053;
        $jy = (979 + 33 * $j_np + 4 * $this->div($j_day_no, 1461)); # 1461 = (365 * 4 + 4 / 4)
        $j_day_no %= 1461;

        if($j_day_no >= 366)
        {
            $jy += $this->div($j_day_no - 1, 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }

        for($i = 0; ($i < 11 && $j_day_no >= $j_days_in_month[$i]); ++ $i)
        {
            $j_day_no -= $j_days_in_month[$i];
        }

        return array($jy, $i + 1, $j_day_no + 1);
    }

    function jalali_to_gregorian($j_y, $j_m, $j_d)
    {
        static $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        static $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
        $jy = $j_y - 979;
        $jm = $j_m - 1;
        $j_day_no = (365 * $jy + $this->div($jy, 33) * 8 + $this->div($jy % 33 + 3, 4));

        for($i = 0; $i < $jm; ++ $i)
        {
            $j_day_no += $j_days_in_month[$i];
        }

        $j_day_no += $j_d - 1;
        $g_day_no= $j_day_no + 79;
        $gy = (1600 + 400 * $this->div($g_day_no, 146097)); # 146097 = (365 * 400 + 400 / 4 - 400 / 100 + 400 / 400)
        $g_day_no= $g_day_no % 146097;
        $leap = 1;

        if($g_day_no >= 36525) # 36525 = (365 * 100 + 100 / 4)
        {
            $g_day_no --;
            $gy += (100 * $this->div($g_day_no, 36524)); # 36524 = (365 * 100 + 100 / 4 - 100 / 100)
            $g_day_no= $g_day_no % 36524;
            if($g_day_no >= 365)
            {
                $g_day_no ++;
            }
            else
            {
                $leap = 0;
            }
        }

        $gy += (4 * $this->div($g_day_no, 1461)); # 1461 = (365 * 4 + 4 / 4)
        $g_day_no %= 1461;

        if($g_day_no >= 366)
        {
            $leap = 0;
            $g_day_no --;
            $gy += $this->div($g_day_no, 365);
            $g_day_no = ($g_day_no % 365);
        }

        for($i = 0; $g_day_no >= ($g_days_in_month[$i] + ($i == 1 && $leap)); $i ++)
        {
            $g_day_no -= ($g_days_in_month[$i] + ($i == 1 && $leap));
        }

        return array($gy, $i + 1, $g_day_no + 1);
    }

    /**
     * Check if input string is a valid iranian national code
     *
     * @param string $input National Code
     * @return boolean Input validity as a Iranian national code string
     * @author Ebrahim Byagowi
     * @link http://gist.github.com/ebraminio/5292017
     */
    function isValidIranianNationalCode($input) {
        if (!preg_match("/^\d{10}$/", $input)) {
            return false;
        }

        $check = (int) $input[9];
        $sum = array_sum(array_map(function ($x) use ($input) {
            return ((int) $input[$x]) * (10 - $x);
        }, range(0, 8))) % 11;

        return $sum < 2 && $check == $sum || $sum >= 2 && $check + $sum == 11;
    }

    function pduration($seconds){
        $hours = floor($seconds/3600);
        $seconds = $seconds % 3600;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return  sprintf('%02d:%02d:%02d',$hours, $minutes, $seconds);
    }

    function pdurationText($seconds){
        $hours = floor($seconds/3600);
        $seconds = $seconds % 3600;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        $text = '';
        if($hours > 0){
            $text = "$hours ساعت ";
        }
        if($minutes > 0){
            if(strlen($text) > 0) $text .= 'و';
            $text .= " $minutes دقیقه ";
        }
        if($seconds > 0){
            if(strlen($text) > 0) $text .= 'و';
            $text .= " $seconds ثانیه";
        }
        return $text;
    }

    public function pprice($price, $tomanPostfix = true){
        if($price == 0) return 'رایگان';
        return number_format($price) . ($tomanPostfix ? ' تومان ' : '');
    }
}
