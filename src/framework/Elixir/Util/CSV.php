<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSV
{
    /**
     * @var string
     */
    const FORCE_UTF8 = "\xEF\xBB\xBF";
    
    /**
     * @param string $pCsv
     * @param boolean $pWithHeaders
     * @param string $pDelimiter
     * @param string $pEnclosure
     * @return array
     */
    public static function CSVToArray($pCsv, $pWithHeaders = false, $pDelimiter = ';', $pEnclosure = '"')
    {
        $return = [];
        $data = [];

        if(is_file($pCsv))
        {
            if(($handle = fopen($pCsv, 'r')) !== false) 
            {
                while(($d = fgetcsv($handle, 4096, $pDelimiter, $pEnclosure)) !== false) 
                {
                    $data[] = $d;
                }

                fclose($handle);
            }
        }
        else
        {
            $data = str_getcsv($pCsv, $pDelimiter, $pEnclosure);
        }

        $i = 0;
        $len = count($data);
        $names = [];

        for($i = 0; $i < $len; ++$i)
        {
            $d = $data[$i];
            $len2 = count($d);

            for($j = 0; $j < $len2; ++$j)
            {
                if($pWithHeaders)
                {
                    if($i === 0)
                    {
                        $names[] = $d[$j];
                    }
                    else
                    {
                        if($j === 0)
                        {
                            $return[$i] = [];
                        }

                        $return[$i][$names[$j]] = $d[$j];
                    }
                }
                else
                {
                    if($j === 0)
                    {
                        $return[$i] = [];
                    }

                    $return[$i][] = $d[$j];
                }
            }
        }

        return $return;
    }
    
    /**
     * @param array $pData
     * @param boolean $pWithHeaders
     * @param string $pDelimiter
     * @param string $pEnclosure
     * @return string
     */
    public static function arrayToCSV(array $pData, $pWithHeaders = false, $pDelimiter = ';', $pEnclosure = '"') 
    { 
        $return = '';
        
        if(count($pData) > 0)
        {
            if($pWithHeaders)
            {
                $columns = [];
                $work = [];

                foreach ($pData[0] as $key => $value)
                {
                    $columns[] = $key; 
                }
                
                $work[0] = $columns;
                $i = 1;
                
                foreach($pData as $data)
                {
                    foreach($columns as $column)
                    {
                        foreach($data as $key => $value)
                        {
                            if($key === $column)
                            {
                                $work[$i][] = $value;
                                break;
                            }
                        }
                    }
                    
                    ++$i;
                }
            }
            else
            {
                $work = $pData;
            }
            
            $fd = tmpfile();
            
            foreach($work as $value)
            {
                fputcsv($fd, $value, $pDelimiter, $pEnclosure);
            }
            
            rewind($fd);
            
            $return = stream_get_contents($fd);
            fclose($fd);
        }
        
        return $return;
    } 
}
