<?php
// Zabbix Pluggable datasource for PHP Weathermap 0.9
// - read a pair of values from a database, and return it
// Actually the plugin look only in history_uint table
// TARGET zabbix:in:out

class WeatherMapDataSource_zabbix extends WeatherMapDataSource {

        function Init(&$map)
        {
                if(! function_exists("mysql_real_escape_string") ) return FALSE;
                if(! function_exists("mysql_connect") ) return FALSE;

                return(TRUE);
        }

        function Recognise($targetstring)
        {

         if(preg_match("/^zabbix#(.*)#(.*)$/",$targetstring,$matches))

                {
                        return TRUE;
                }
                else
                {
                        return FALSE;
                }
        }

        function ReadData($targetstring, &$map, &$item)
        {
                $data[IN] = NULL;
                $data[OUT] = NULL;
                $data_time = 0;

                if(preg_match("/^zabbix#(.*)#(.*)$/",$targetstring,$matches))
                {
                        $database_user = 'user';
                        $database_pass = 'pass';
                        $database_name = 'database';
                        $database_host = 'localhost';

                        $raw_in = $matches[1];
                        $raw_out= $matches[2];

                        debug ("Found for IN Value :  $raw_in \n");
                        debug ("Found for OUT Value :  $raw_out \n");

  		#en raw_in e raw_out temos un HOST:KEY, Necesitamos o itemid da DB 
			$in_=explode(':',$raw_in);
			$out_=explode(':',$raw_out);
			
			mysql_connect($database_host,$database_user,$database_pass);
			mysql_select_db($database_name);
			$resultado_in=mysql_query("select itemid from items inner join hosts on items.hostid=hosts.hostid where hosts.host='".$in_[0]."' and items.key_='".$in_[1]."';");
                        $resultado_out=mysql_query("select itemid from items inner join hosts on items.hostid=hosts.hostid where hosts.host='".$out_[0]."' and items.key_='".$out_[1]."';");

			$raw_in = mysql_result($resultado_in, 0);
			$raw_out = mysql_result($resultado_out, 0);


                        $SQL_IN = "(select value from history_uint where itemid=".$raw_in." order by clock desc limit 1) union (select value from history where itemid=".$raw_in." order by clock desc limit 1)";
                        $SQL_OUT = "(select value from history_uint where itemid=".$raw_out." order by clock desc limit 1) union (select value from history where itemid=".$raw_out." order by clock desc limit 1)";

                        if(mysql_connect($database_host,$database_user,$database_pass))
                        {
                                if(mysql_select_db($database_name))
                                {
                                        $result_IN = mysql_query($SQL_IN);
                                        if (!$result_IN)
                                        {
                                            warn("Zabbix ReadData: Invalid query for IN Value: " . mysql_error()."\n");
                                        }
                                        else
                                        {
                                                $row_IN = mysql_fetch_assoc($result_IN);
                                                $data[IN] = $row_IN['value'];
                                        }
                                        $result_OUT = mysql_query($SQL_OUT);
                                        if (!$result_OUT)
                                        {
                                            warn("Zabbix ReadData: Invalid query for OUT Value: " . mysql_error()."\n");
                                        }
                                        else
                                        {
                                                $row_OUT = mysql_fetch_assoc($result_OUT);
                                                $data[OUT] = $row_OUT['value'];
                                                $data_time = $row_OUT['clock'];
                                        }



                                }

                                else
                                {
                                        warn("Zabbix ReadData: failed to select database ($database_name): ".mysql_error()."\n");
                                }
                        }
                        else
                        {
                                warn("Zabbix ReadData: failed to connect to database server: ".mysql_error()."\n");
                        }

                //      $data_time = now();
                }


                debug ("RRD ReadData: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[IN]).",$data_time)\n");

                return( array($data[IN], $data[OUT], $data_time) );
        }
}
?>
