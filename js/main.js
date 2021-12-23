$(document).on({
	ajaxStart: function(){
		$("body").addClass("loading"); 
	},
	ajaxStop: function(){ 
		$("body").removeClass("loading"); 
	}
});

//Call Database Listing Function On Page Load
databaseList();

//Get Database Listing
function databaseList() {
    $.ajax({
        type:'POST',
        url:'common/action.php',
        data:{'request':"get_database"},
        success:function(response) {
            $('#database_list').html(response);
            $(".showTableList").hide();
        }
    });
}

//Create Database
function createDatabase() {
    var isvalidate = $("#database_form").valid();
    if(isvalidate) {
        $.ajax({
            type:'POST',
            url:'common/action.php',
            data:$("#database_form").serialize(),
            success:function(response) {                    
                var response = JSON.parse(response);
                if(response.status == true) {
                    databaseList();
                    $("#createDatabase").modal('hide');
                    toastr.success(response.message);        
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }
}

//Get Database Table List
function displayTable(dbname) {
    $(".tableListTitle").text("Table List: " +dbname);
    $(".database_name").val(dbname);
    $(".showTableList").show();
    $.ajax({
        type:'POST',
        url:'common/action.php',
        data:{'request':"get_table","database_name":dbname},
        success:function(response) {
            $('#table_list').html(response);
        }
    });
}

//Check Selected Data Type
function checkType(id) {
    var type = $("#type_"+id).val();
    if(type == "CHAR" || type == "VARCHAR" || type == "INT") {
        $("#length_"+id).prop("readonly", false);
    } else {
        $("#length_"+id).prop("readonly", true);
    }
}

//Add New Table Row
var k = 1;
function addTableRow() {
    var tbody = $('.table_row'); 
    var html = '<tr id="tblrow_'+ k +'">'
            +'<td><input type="text" name="name[]" id="name_'+ k +'" required class="form-control" placeholder="Enter name"></td>'
            +'<td>'
            +'<select class="form-control" name="type[]" id="type_'+ k +'" required onChange="checkType('+ k +')">'
            +'<option selected>Select Type</option>'
            +'<option value="CHAR">CHAR</option>'
            +'<option value="VARCHAR">VARCHAR</option>'
            +'<option value="TEXT">TEXT</option>'
            +'<option value="DATE">DATE</option>'
            +'<option value="INT">INT</option>'
            +'</select>'
            +'</td>'
            +'<td><input type="text" class="form-control" name="length[]" id="length_'+ k +'" required placeholder="Enter length"/></td>'
            +'<td><button type="button" class="btn btn-outline-danger btn-sm" onClick="removeTableRow('+ k +')" title="Remove">-</button></td>'
            +'</tr>';
    $("#length_"+k).hide();
    k++;
    $(tbody).append(html);
}

//Remove Table Row
function removeTableRow(id) {
    $("#tblrow_"+id).remove();
}

//Create New Table
function createTable() {
    var isvalidate = $("#table_form").valid();
    if(isvalidate) {
        $.ajax({
            type:'POST',
            url:'common/action.php',
            data:$("#table_form").serialize(),
            success:function(response) {
                var response = JSON.parse(response);
                if(response.status == true) {
                    displayTable(response.database_name);
                    $("#createTable").modal('hide');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }
}

//Drop Table
function dropTable(index, database_name, table_name) {
    if(confirm("Are you sure want to delete this table?")) {
        $.ajax({
            type:'POST',
            url:'common/action.php',
            data:{'request':"drop_table", "database_name":database_name, "table_name":table_name},
            success:function(response) {
                var response = JSON.parse(response);
                if(response.success == true) {
                    $("#tbl_"+index).remove();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }
}

//Get Table Column Detail
var j;
function getTableColumn(database_name, table_name) {
    $.ajax({
        type:'POST',
        url:'common/action.php',
        data:{'request':"get_table_column", "database_name":database_name, "table_name":table_name},
        success:function(response) {
            $("#editTable").modal('show');
            var response = JSON.parse(response);
            if(response.success == true) {
                $(".edit_table_row").empty();
                $(".edit_table_row").append(response.data)
                $("#edit_table_name").val(response.table_name)
                j = response.lastindex;
            }
        }
    });
}
function removeEditTableRow(id) {
    $("#tblrowedit_"+id).remove();
}
function addEditTableRow() {
    var index = j;        
    var tbody = $('.edit_table_row'); 
    var html = '<tr id="tblrowedit_'+ j +'">'
            +'<td><input type="text" name="new_name[]" id="name_'+ j +'" required class="form-control" placeholder="Enter name"></td>'
            +'<td>'
            +'<select class="form-control" name="new_type[]" id="type_'+ j +'" required onChange="checkType('+ j +')">'
            +'<option selected>Select Type</option>'
            +'<option value="CHAR">CHAR</option>'
            +'<option value="VARCHAR">VARCHAR</option>'
            +'<option value="TEXT">TEXT</option>'
            +'<option value="DATE">DATE</option>'
            +'<option value="INT">INT</option>'
            +'</select>'
            +'</td>'
            +'<td><input type="text" class="form-control" name="new_length[]" id="length_'+ j +'" required placeholder="Enter length"/></td>'
            +'<td><a class="btn btn-outline-danger btn-sm" onClick="removeEditTableRow('+j+')">-</a></td>'
            +'</tr>';
    $("#length_"+j).hide();
    j++;
    $(tbody).append(html);
}

//Edit Table
function editTable() {
    var isvalidate = $("#edit_table_from").valid();
    if(isvalidate) {
        $.ajax({
            type:'POST',
            url:'common/action.php',
            data:$("#edit_table_from").serialize(),
            success:function(response) {
                var response = JSON.parse(response);
                if(response.success == true) {
                    displayTable(response.database_name);
                    $("#editTable").modal('hide');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message)
                }
            }
        });
    }
}