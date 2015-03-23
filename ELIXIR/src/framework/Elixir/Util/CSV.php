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
     * @param string $csv
     * @param boolean $withHeaders
     * @param string $delimiter
     * @param string $enclosure
     * @return array
     */
    public static function CSVToArray($csv, $withHeaders = false, $delimiter = ';', $enclosure = '"')
    {
        $return = [];
        $data = [];

        if(is_file($csv))
        {
            if(($handle = fopen($csv, 'r')) !== false) 
            {
                while(($d = fgetcsv($handle, 4096, $delimiter, $enclosure)) !== false) 
                {
                    $data[] = $d;
                }

                fclose($handle);
            }
        }
        else
        {
            $data = str_getcsv($csv, $delimiter, $enclosure);
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
                if($withHeaders)
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
     * @param array $data
     * @param boolean $withHeaders
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    public static function arrayToCSV(array $data, $withHeaders = false, $delimiter = ';', $enclosure = '"') 
    { 
        $return = '';
        
        if(count($data) > 0)
        {
            if($withHeaders)
            {
                $columns = [];
                $work = [];

                foreach($data[0] as $key => $value)
                {
                    $columns[] = $key; 
                }
                
                $work[0] = $columns;
                $i = 1;
                
                foreach($data as $data)
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
                $work = $data;
            }
            
            $fd = tmpfile();
            
            foreach($work as $value)
            {
                fputcsv($fd, $value, $delimiter, $enclosure);
            }
            
            rewind($fd);
            
            $return = stream_get_contents($fd);
            fclose($fd);
        }
        
        return $return;
    } 
}
