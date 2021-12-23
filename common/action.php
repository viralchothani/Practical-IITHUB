<?php
include "config.php";
//Get Database Listing
if(isset($_POST['request']) && $_POST['request'] == "get_database") {
    $query = "SHOW DATABASES";
    $data = $conn->query($query);
    $html = '';
    $i=1;
    if($data->num_rows > 0) {
        while($row = $data->fetch_assoc()) {
            if(strpos($row['Database'],"practical_") !== false) {
                $html .= '
                <tr>
                <td>'.$i.'</td>
                <td>'.$row['Database'].'</td>
                <td><button type="button" class="btn btn-outline-info btn-sm" onClick="displayTable(\'' .$row['Database']. '\')">Show Tables</button></td>
                </tr>';
                $i++;
            }
        }
    }
    $logfile = fopen("../log.sql", "a");
    $log = date('Y-m-d h:i:s A')." : ".$query."\n";
    fwrite($logfile, $log);
    if($i<2) {
        echo '<tr><td colspan="3">No database found</td></tr>';
    } else {
        echo $html;
    }
    $conn->close();
}

//Create Database
if(isset($_POST['request']) && $_POST['request'] == "create_database") {
    $query = "SHOW DATABASES LIKE '%".$_POST['name']."%'";
    $data = $conn->query($query);
    $html = '';
    $i=1;
    if($data->num_rows > 0) {
        $response['status'] = false;
        $response['message'] = "Database already exist";
    } else {
        $fields = '';
        $sql = "CREATE DATABASE practical_".$_POST['name'];
        if ($conn->query($sql) === TRUE) {
            $log = date('Y-m-d h:i:s A')." : ".$sql."\n";
            $response['status'] = true;
            $response['message'] = "Database created successfully";
        } else {
            $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
            $response['status'] = false;
            $response['message'] = "Database create error : ".$conn->error;
        }
        $logfile = fopen("../log.sql", "a");
        fwrite($logfile, $log);
    }
    $conn->close();
    echo json_encode($response);
}

//Get Database Table List
if(isset($_POST['request']) && $_POST['request'] == "get_table") {
    $conn = new mysqli($host, $username, $password, $_POST['database_name']);
    $query = "SHOW TABLES";
    $data = $conn->query($query);
    $html = '';
    $i=1;
    while($row = $data->fetch_assoc()) {
        $html .= '<tr id="tbl_'.$i.'">
            <td>'.$i.'</td>
            <td>'.$row['Tables_in_'.$_POST['database_name'].''].'</td>
            <td>
            <button type="button" class="btn btn-outline-warning btn-sm" onClick="getTableColumn(\'' .$_POST['database_name']. '\', \'' .$row['Tables_in_'.$_POST['database_name'].'']. '\')">Edit</button>
            <button type="button" class="btn btn-outline-danger btn-sm" onClick="dropTable('.$i.', \'' .$_POST['database_name']. '\', \'' .$row['Tables_in_'.$_POST['database_name'].'']. '\')">Delete</button>
            </td>
            </tr>';
        $i++;
    }
    $logfile = fopen("../log.sql", "a");
    $log = date('Y-m-d h:i:s A')." : ".$query."\n";
    fwrite($logfile, $log);
    if($i<2) {
        echo '<tr><td colspan="3">No table found</td></tr>';
    } else {
        echo $html;
    }
    $conn->close();
}

//Create New Table
if(isset($_POST['request']) && $_POST['request'] == "create_table") {
    $conn = new mysqli($host, $username, $password, $_POST['database_name']);
    $response = array();
    $checktable = $conn->query("SHOW TABLES LIKE '".$_POST['table_name']."'");
    if($checktable->num_rows == 1) {
        $response['status'] = false;
        $response['message'] = "Table already exist";
    } else {
        $fields = '';
        for($i=0; $i<count($_POST['name']); $i++) {
            if($_POST['type'][$i] == "CHAR" || $_POST['type'][$i] == "VARCHAR" || $_POST['type'][$i] == "INT") {
                $fields .= $_POST['name'][$i].' '.$_POST['type'][$i].'('.$_POST['length'][$i].')';
            } else {
                $fields .= $_POST['name'][$i].' '.$_POST['type'][$i];
            }
            $fields .= (count($_POST['name'])-1 == $i) ? '' : ', ';
        }
        $query = "CREATE TABLE ".$_POST['table_name']." (".$fields.")";
        
        if($conn->query($query) === TRUE) {
            $log = date('Y-m-d h:i:s A')." : ".$query."\n";
            $response['status'] = true;
            $response['database_name'] = $_POST['database_name'];
            $response['message'] = "Table created successfully";
        } else {
            $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
            $response['status'] = false;
            $response['message'] = "Table create error ".$conn->error;
        }
        $logfile = fopen("../log.sql", "a");
        fwrite($logfile, $log);
    }
    $conn->close();
    echo json_encode($response);
}

//Drop Table
if(isset($_POST['request']) && $_POST['request'] == "drop_table") {
    $conn = new mysqli($host, $username, $password, $_POST['database_name']);
    $response = array();
    $query = "DROP TABLE ".$_POST['table_name']." ";
    if($conn->query($query) === TRUE) {
        $log = date('Y-m-d h:i:s A')." : ".$query."\n";
        $response['success'] = true;
        $response['message']  = "Table deleted successfully";                
    } else {
        $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
        $response['success'] = false;
        $response['message']  = "Table delete error ".$conn->error;                
    }
    $logfile = fopen("../log.sql", "a");
    fwrite($logfile, $log);
    $conn->close();
    echo json_encode($response);
}

//Get Table Column Detail
if(isset($_POST['request']) && $_POST['request'] == "get_table_column"){
    $conn = new mysqli($host, $username, $password, $_POST['database_name']);
    $response = array();
    $query = "SHOW FULL COLUMNS FROM ".$_POST['table_name']."";
    $data = $conn->query($query);
    $html = '';
    $i = 1;
    $logfile = fopen("../log.sql", "a");
    $log = date('Y-m-d h:i:s A')." : ".$query."\n";
    fwrite($logfile, $log);
    while($row = $data->fetch_assoc()){
        $length = '';
        $lengthvalue = '';
        
        if(strpos($row['Type'],"char") !== false || strpos($row['Type'],"varchar") !== false || strpos($row['Type'],"int") !== false) {
            $lengthvalue = explode("(",$row['Type'])[1];
            $lengthvalue = explode(")",$lengthvalue)[0];
        }

        $char_selected = (strpos($row['Type'],"char") !== false) ? 'SELECTED' : '';
        $varchar_selected = (strpos($row['Type'],"varchar") !== false) ? 'SELECTED' : '';
        $text_selected = (strpos($row['Type'],"text") !== false) ? 'SELECTED' : '';
        $date_selected = (strpos($row['Type'],"date") !== false) ? 'SELECTED' : '';
        $int_selected = (strpos($row['Type'],"int") !== false) ? 'SELECTED' : '';

        $selected = '<select class="form-control" name="type[]" id="type_'.$i.'" required onChange="checkType('.$i.')">';
        $selected .= '<option>Select Data Type</option>';
        $selected .= '<option value="CHAR" '.$char_selected.'>CHAR</option>';
        $selected .= '<option value="VARCHAR" '.$varchar_selected.'>VARCHAR</option>';
        $selected .= '<option value="TEXT" '.$text_selected.'>TEXT</option>';
        $selected .= '<option value="DATE" '.$date_selected.'>DATE</option>';
        $selected .= '<option value="INT" '.$int_selected.'>INT</option>';
        $selected .= '</select>';

        if($i==1) {
            $button = '<button type="button" class="btn btn-outline-success btn-sm" onClick="addEditTableRow()">+</button>';
        } else {
            $button = '<button type="button" class="btn btn-outline-danger btn-sm" onClick="removeEditTableRow('.$i.')">-</button>';
        }

        $length_readonly = empty($lengthvalue) ? 'readonly' : '';
        $html .= '<tr id="tblrowedit_'.$i.'">
                <td>
                    <input type="hidden" name="old_name[]" value="'.$row['Field'].'" id="old_name_'.$i.'">
                    <input type="text" name="name[]" value="'.$row['Field'].'" id="name_'.$i.'" required class="form-control" placeholder="Enter name">
                </td>
                <td>
                    '.$selected.'
                </td>
                <td>
                    <input type="text" name="length[]" value="'.$lengthvalue.'" '.$length_readonly.' id="length_'.$i.'" required class="form-control" placeholder="Enter length"/>
                </td>
                <td>'.$button.'</td>
            </tr>';
        $i++;
    }
    $conn->close();
    $response['success'] = true;
    $response['data'] = $html;
    $response['table_name'] = $_POST['table_name'];
    $response['lastindex'] = $i;
    echo json_encode($response);
}

if(isset($_POST['request']) && $_POST['request'] == "edit_table") {
    $conn = new mysqli($host, $username, $password, $_POST['database_name']);
    $response = array();
    $fields = '';
    $query = "SHOW FULL COLUMNS FROM ".$_POST['table_name']."";
    $data = $conn->query($query);
    $output = '';
    $i=0;
    while($row = $data->fetch_assoc()) {
        if(isset($_POST['name'][$i]) && ($_POST['type'][$i] == "CHAR" || $_POST['type'][$i] == "VARCHAR" || $_POST['type'][$i] == "INT")) {
            if($row['Field'] == $_POST['name'][$i]) {
                $fields .= ' CHANGE '.$_POST['old_name'][$i].' '.$_POST['name'][$i].' '.$_POST['type'][$i].'('.$_POST['length'][$i].')';
                $fields .= (count($_POST['name'])-1 == $i) ? '' : ', ';
            } else {
                $fields .= ', DROP COLUMN '.$row['Field'];
            }
        } else {
            if(isset($_POST['name'][$i]) && $row['Field'] == $_POST['name'][$i]) {
                $fields .= ' CHANGE '.$_POST['old_name'][$i].' '.$_POST['name'][$i].' '.$_POST['type'][$i];
                $fields .= (count($_POST['name'])-1 == $i) ? '' : ', ';
            } else {
                $fields .= ', DROP COLUMN '.$row['Field'];
            }
        }
        $i++;
    }
    
    if(isset($_POST['new_name'])) {
        for($i=0; $i<count($_POST['new_name']); $i++) {
            $fields .= ', ';
            if($_POST['new_type'][$i] == "CHAR" || $_POST['new_type'][$i] == "VARCHAR" || $_POST['new_type'][$i] == "INT") {
                $fields .= ' ADD '.$_POST['new_name'][$i].' '.$_POST['new_type'][$i].'('.$_POST['new_length'][$i].')';
            } else {
                $fields .= ' ADD '.$_POST['new_name'][$i].' '.$_POST['new_type'][$i];
            }
        }
    }
    
    $alterQuey = "ALTER TABLE ".$_POST['table_name']." ".$fields;
    if($conn->query($alterQuey) === TRUE) {
        $log = date('Y-m-d h:i:s A')." : ".$alterQuey."\n";
        $response['success'] = true;
        $response['database_name'] = $_POST['database_name'];
        $response['message'] = "Table updated successfully";
    } else {
        $log = date('Y-m-d h:i:s A')." : ".$conn->error."\n";
        $response['success'] = false;
        $response['message'] = "Table update error ".$conn->error;
    }
    $logfile = fopen("../log.sql", "a");
    fwrite($logfile, $log);
    $conn->close();
    echo json_encode($response);
}
?>