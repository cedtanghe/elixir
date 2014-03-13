<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class CSV
{
    /**
     * @var string
     */
    const FORCE_UTF8 = "\xEF\xBB\xBF";
    
    /**
     * @param string $pCsv
     * @param string $pDelimiter
     * @param string $pEnclosure
     * @param boolean $pAssociate
     * @return array
     */
    public static function CSVToArray($pCsv, $pDelimiter = ';', $pEnclosure = '"', $pAssociate = false)
    {
        $return = array();
        $datas = array();
        
        if(is_file($pCsv))
        {
            if (($handle = fopen($pCsv, 'r')) !== false) 
            {
                while (($data = fgetcsv($handle, 4096, $pDelimiter, $pEnclosure)) !== false) 
                {
                    $datas[] = $data;
                }
                
                fclose($handle);
            }
        }
        else
        {
            $datas = str_getcsv($pCsv, $pDelimiter, $pEnclosure);
        }
        
        $i = 0;
        $len = count($datas);
            
        for($i = 0; $i < $len; ++$i)
        {
            $data = $datas[$i];
            $len2 = count($data);

            for($j = 0; $j < $len2; ++$j)
            {
                if($pAssociate)
                {
                    if($i === 0)
                    {
                        $associate[] = $data[$j];
                    }
                    else
                    {
                        if($j === 0)
                        {
                            $return[$i] = array();
                        }
                        
                        $return[$i][$associate[$j]] = $data[$j];
                    }
                }
                else
                {
                    if($j === 0)
                    {
                        $return[$i] = array();
                    }
                    
                    $return[$i][] = $data[$j];
                }
            }
        }
        
        return $return;
    }
    
    /**
     * @param array $pData
     * @param string $pDelimiter
     * @param string $pEnclosure
     * @param boolean $pAssociate
     * @return string
     */
    public static function arrayToCSV(array $pData, $pDelimiter = ';', $pEnclosure = '"', $pAssociate = false) 
    { 
        $return = '';
        
        if(count($pData) > 0)
        {
            if($pAssociate)
            {
                $columns = array();
                $work = array();

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
