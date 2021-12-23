<?php
    include "conn.php";
    if(isset($_POST['action']) && $_POST['action'] == "getdatabaselist"){
        //$databasequery = "SHOW DATABASES LIKE '%practical_'";
        $databasequery = "SHOW DATABASES";
        $databasedata  = $conn->query($databasequery);
        $output = '';
        $index = 1;        
        if($databasedata->num_rows > 0){
            while($row = $databasedata->fetch_assoc()){
                if(strpos($row['Database'],"practical_") !== false){                
                    $output .= '<tr>
                                <td>'.$index.'</td>
                                <td>'.$row['Database'].'</td>
                                <td><a href="javascript:void(0);" class="btn btn-primary" onClick="DisplayTableList(\'' .$row['Database']. '\')">Show Tables</a></td>
                                </tr>';
                    $index++;
                }
            }
        }
        $logfile = fopen("../log.sql", "a");
        $log = date('Y-m-d h:i:s A')." : ".$databasequery."\n";
        fwrite($logfile, $log);
        if($index == 1){
            echo '<tr><td colspan="3">No database found</td></tr>';
        }else{
            echo $output;
        }                
        $conn->close();
    }else if(isset($_POST['action']) && $_POST['action'] == "getdatabasetablelist"){
        $_SESSION['dbname'] = $_POST['database'];
        $dabaseconn = new mysqli($Host, $User, $Pwd,$_POST['database']);        
        $databasetablesquery = "SHOW TABLES";
        $databasetablesdata  = $dabaseconn->query($databasetablesquery);
        $output = '';
        $index = 1;
        while($row = $databasetablesdata->fetch_assoc()){            
            $output .= '<tr id="tblrow_'.$index.'">
                        <td>'.$index.'</td>
                        <td>'.$row['Tables_in_'.$_POST['database'].''].'</td>
                        <td><a href="javascript:void(0);" class="btn btn-primary" onClick="GettableColumn(\'' .$row['Tables_in_'.$_POST['database'].'']. '\')">Edit</a>
                            <a href="javascript:void(0);" class="btn btn-danger" onClick="DropTable('.$index.',\'' .$row['Tables_in_'.$_POST['database'].'']. '\')">Delete</a>
                        </td>
                        </tr>';
            $index++;
        }
        $logfile = fopen("../log.sql", "a");
        $log = date('Y-m-d h:i:s A')." : ".$databasetablesquery."\n";
        fwrite($logfile, $log);
        echo $output;
        $dabaseconn->close();    
    }else if(isset($_POST['action']) && $_POST['action'] == "createtable"){  
        $responsearray = array();              
        $checktable = $conn->query("SHOW TABLES LIKE '".$_POST['tablename']."'");        
        if($checktable->num_rows == 1){            
            $responsearray['success'] = false;
            $responsearray['message']  = "Table already exist";
        }else{            
            $fields = '';
            for($i=0;$i<count($_POST['fieldname']);$i++){
                if($_POST['datatype'][$i] == "CHAR" || $_POST['datatype'][$i] == "VARCHAR" || $_POST['datatype'][$i] == "INT"){
                    if(count($_POST['fieldname'])-1 == $i){                        
                        $fields .= ''.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].') ';
                    }else{                        
                        $fields .= ''.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].'), ';
                    }                
                }else{
                    if(count($_POST['fieldname'])-1 == $i){
                        $fields .= ''.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].' ';    
                    }else{
                        $fields .= ''.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].',';    
                    }
                }            
            }   
            $createtablequery = "CREATE TABLE ".$_POST['tablename']." (".$fields.")";              
            
            if($conn->query($createtablequery) === TRUE){
                $logfile = fopen("../log.sql", "a");
                $log = date('Y-m-d h:i:s A')." : ".$createtablequery."\n";
                fwrite($logfile, $log);
                $responsearray['success'] = true;
                $responsearray['message']  = "Table created successfully";                
            }else{
                $responsearray['success'] = false;
                $responsearray['message']  = "Table error".$conn->error;  
                $logfile = fopen("../log.sql", "a");
                $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
                fwrite($logfile, $log);              
            }
        }
        $conn->close();        
        
        echo json_encode($responsearray);
    }else if(isset($_POST['action']) && $_POST['action'] == "droptable"){  
        $responsearray = array();
        $droptablequery = "DROP TABLE ".$_POST['tablename']." ";
        if($conn->query($droptablequery) === TRUE){
            $logfile = fopen("../log.sql", "a");
            $log = date('Y-m-d h:i:s A')." : ".$droptablequery."\n";
            fwrite($logfile, $log);
            $responsearray['success'] = true;
            $responsearray['message']  = "Table deleted successfully";                
        }else{
            $logfile = fopen("../log.sql", "a");
            $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
            fwrite($logfile, $log);
            $responsearray['success'] = false;
            $responsearray['message']  = "Table error".$conn->error;                
        }
        $conn->close();
        echo json_encode($responsearray);
    }else if(isset($_POST['action']) && $_POST['action'] == "getalltablecolumn"){          
        $responsearray = array();
        $databasequery = "SHOW FULL COLUMNS FROM ".$_POST['tablename']."";
        $databasedata  = $conn->query($databasequery);
        $output = '';
        $index  = 1;
        $logfile = fopen("../log.sql", "a");
        $log = date('Y-m-d h:i:s A')." : ".$databasequery."\n";
        fwrite($logfile, $log);
        while($row = $databasedata->fetch_assoc()){  
            $length   = '';
            $selected = '<select class="form-control" name="datatype[]" id="datatype_'.$index.'" required onChange="CheckDatatype('.$index.')">
                            <option value="">Select Data Type</option>                            
                        ';          
            if(strpos($row['Type'],"int") !== false){                
                $selected .= '<option value="INT" SELECTED>INT</option>';
                $lengthvalue = explode("(",$row['Type'])[1]; 
                $lengthvalue = explode(")",$lengthvalue)[0];
                $length .= '<input type="text" class="form-control" name="length[]" value="'.$lengthvalue.'" id="length_'.$index.'" required placeholder="Enter length"/>';
            }else{
                $selected .= '<option value="CHINTAR" >INT</option>';
            }            
            if(strpos($row['Type'],"varchar") !== false){ 
                $selected .= '<option value="VARCHAR" SELECTED>VARCHAR</option>';
                $lengthvalue = explode("(",$row['Type'])[1]; 
                $lengthvalue = explode(")",$lengthvalue)[0];
                $length .= '<input type="text" class="form-control" name="length[]" value="'.$lengthvalue.'" id="length_'.$index.'" required placeholder="Enter length"/>';
            }else{
                $selected .= '<option value="VARCHAR" >VARCHAR</option>';
            }            
            
            if(strpos($row['Type'],"date") !== false){ 
                $selected .= '<option value="DATE" SELECTED>DATE</option>';
            }else if(strpos($row['Type'],"text") !== false){ 
                $selected .= '<option value="DATE" >DATE</option>';
            }

            $selected .= '<select>';
            $output .= '<tr id="editthisrow_'.$index.'">
                        <td>'.$index.'</td>
                        <td>
                            <input type="text" class="form-control" name="fieldname[]" value="'.$row['Field'].'" id="fieldname_'.$index.'" required placeholder="Enter column nae"/>
                        </td>
                        <td>
                             '.$selected.'                                                                               
                        </td>
                        <td>
                            '.$length.'
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm" onClick="RemoveEditTr('.$index.')">-</a>
                        </td>
                    </tr>';
            $index++;            
        }
        $conn->close();
        $responsearray['success']       = true;
        $responsearray['data']          = $output;  
        $responsearray['tablename']     = $_POST['tablename'];  
        $responsearray['lastindex']     = $index;
        echo json_encode($responsearray);        
    }else if(isset($_POST['action']) && $_POST['action'] == "updatetable"){  
        $responsearray = array();              
         
        /*$droptablequery = "DROP TABLE IF EXISTS ".$_POST['tablename']." ";
        $conn->query($droptablequery);*/
        $fields = '';
        $databasequery = "SHOW FULL COLUMNS FROM ".$_POST['tablename']."";
        $databasedata  = $conn->query($databasequery);
        $output = '';
        $index  = 1;
        while($row = $databasedata->fetch_assoc()){
            for($i=0;$i<count($_POST['fieldname']);$i++){
                if($_POST['datatype'][$i] == "CHAR" || $_POST['datatype'][$i] == "VARCHAR" || $_POST['datatype'][$i] == "INT"){
                    if($row['Field'] ==  $_POST['fieldname'][$i]){
                        if(count($_POST['fieldname'])-1 == $i){                        
                            $fields .= ' MODIFY '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].') ';
                        }else{                        
                            $fields .= ' MODIFY '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].'), ';
                        }  
                    }else if($row['Field'] !=  $_POST['fieldname'][$i]){
                        if(count($_POST['fieldname'])-1 == $i){
                            $fields .= ' ADD '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].') ';    
                        }else{
                            $fields .= ' ADD '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].'('.$_POST['length'][$i].'),';    
                        }
                    }else{
                        if(count($_POST['fieldname'])-1 == $i){                        
                            $fields .= ' DROP COLUMN '.$row['Field'].' ';
                        }else{                        
                            $fields .= ' DROP COLUMN '.$row['Field'].', ';
                        }  
                    }                                  
                }else{
                    if($row['Field'] ==  $_POST['fieldname'][$i]){
                        if(count($_POST['fieldname'])-1 == $i){
                            $fields .= ' MODIFY '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].' ';    
                        }else{
                            $fields .= ' MODIFY '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].',';    
                        }
                    }else if($row['Field'] !=  $_POST['fieldname'][$i]){
                        if(count($_POST['fieldname'])-1 == $i){
                            $fields .= ' ADD '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].' ';    
                        }else{
                            $fields .= ' ADD '.$_POST['fieldname'][$i].' '.$_POST['datatype'][$i].', ';    
                        }
                    }else{
                        if(count($_POST['fieldname'])-1 == $i){
                            $fields .= ' DROP COLUMN '.$row['Field'].' ';    
                        }else{
                            $fields .= 'DROP COLUMN '.$row['Field'].', ';    
                        }
                    }                    
                }            
            } 
        }          
        $altertablequery = "ALTER TABLE ".$_POST['tablename']." (".$fields.")";              
        echo $altertablequery;die;
        if($conn->query($altertablequery) === TRUE){
            $logfile = fopen("../log.sql", "a");
            $log = date('Y-m-d h:i:s A')." : ".$altertablequery."\n";
            fwrite($logfile, $log);
            $responsearray['success'] = true;
            $responsearray['message']  = "Table updated successfully";                
        }else{
            $responsearray['success'] = false;
            $responsearray['message']  = "Table error".$conn->error;                
            $logfile = fopen("../log.sql", "a");
            $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
            fwrite($logfile, $log);
        }
        
        $conn->close();        
        echo json_encode($responsearray);
    }  
    
?>