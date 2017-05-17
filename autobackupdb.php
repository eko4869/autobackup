<?php 
	
    class BackupDB
    {
        
        function backup($nama_file,$tables = '')
        {
            $server = "localhost";
            $username = "username_anda";
            $password = "password_anda";
            $database = "databse_anda";
             
            mysql_connect($server,$username,$password) or die("Koneksi gagal");
            mysql_select_db($database) or die("Database tidak bisa dibuka");
                            

            if($tables == '')
            {

                $tables = array();
                $result = @mysql_list_tables($database);


                while($row = @mysql_fetch_row($result))
                {
                    $tables[] = $row[0];
                }
            }else{
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            $return = '';

            foreach($tables as $table)
            {
                $result  = @mysql_query('SELECT * FROM '.$table);
                $num_fields = @mysql_num_fields($result);

                //menyisipkan query drop table untuk nanti hapus table yang lama
                $return .= "DROP TABLE IF EXISTS ".$table.";";
                $row2    = @mysql_fetch_row(mysql_query('SHOW CREATE TABLE  '.$table));
                $return .= "\n\n".$row2[1].";\n\n";

                for ($i = 0; $i < $num_fields; $i++)
                {
                    while($row = @mysql_fetch_row($result))
                    {
                        $return.= 'INSERT INTO '.$table.' VALUES(';

                        for($j=0; $j<$num_fields; $j++)
                        {
                            $row[$j] = @addslashes($row[$j]);
                            $row[$j] = @ereg_replace("\n","\\n",$row[$j]);
                            if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                            if ($j<($num_fields-1)) { $return.= ','; }
                        }
                        $return.= ");\n";
                    }
                }
                $return.="\n\n\n";
            }

            $nama_file;

            
            $handle = fopen($this->back_dir.$nama_file,'w+');

            fwrite($handle, $return);
            fclose($handle);

            $this->sendBackupDBToEmail();
        }

        function sendBackupDBToEmail()
        {
        	$file     = 'tmp/'.'db_'.date('Y-m-d').'.sql';
		    $filename = 'db_'.date('Y-m-d').'.sql';

		    $mailto  = 'ndalemapplication@gmail.com';
		    $subject = 'BackupDB Tanggal '.date('Y-m-d');
		    $message = 'Database telah di backup tanggal '.date('Y-m-d');
		    
		    $content = file_get_contents($file);
		    $content = chunk_split(base64_encode($content));
		 
		    $separator = md5(time());

		    $eol = "\r\n";
		  
		    $headers = "From: ndalem.id <no_reply@ndalem.id>" . $eol;
		    $headers .= "MIME-Version: 1.0" . $eol;
		    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
		    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
		    $headers .= "This is a MIME encoded message." . $eol;

		   
		    $body = "--" . $separator . $eol;
		    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
		    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
		    $body .= $message . $eol;

		  
		    $body .= "--" . $separator . $eol;
		    $body .= "Content-Type: application/sql; name=\"" . $filename . "\"" . $eol;
		    $body .= "Content-Transfer-Encoding: base64" . $eol;
		    $body .= "Content-Disposition: attachment" . $eol;
		    $body .= $content . $eol;
		    $body .= "--" . $separator . "--";

		    if(mail($mailto, $subject, $body, $headers)){
            unlink($file);
            header('Location :'.$_SERVER['REQUEST_URI']);
            }else{
                echo "***ERROR***";
            } 
		
        }


    }
    $nama_file = 'db_'.date('Y-m-d').'.sql';
    $obj = new BackupDB;
    $obj->backup($nama_file);
 ?>
