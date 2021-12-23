<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/toastr.css">
    <link rel="stylesheet" href="css/style.css">

    <title>Practical - Database Management</title>
  </head>
  <body>
    <div class="overlay"></div>
    <div class="content">
        <!-- Database Listing -->
        <div class="container">
            <h1 class="mb-3">Database List</h1>
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" data-toggle="modal" data-target="#createDatabase">Create Database</button>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                        <th>No</th>
                        <th scope="col">Database Name</th>
                        <th scope="col">Table</th>
                        </tr>
                    </thead>
                    <tbody id="database_list"></tbody>
                </table>
            </div>
        </div><!-- /.Database Listing -->

        <!-- Table Listing -->
        <div class="container mt-5 showTableList">
            <h1 class="mb-3 tableListTitle">Table List</h1>
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" data-toggle="modal" data-target="#createTable">Create Table</button>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                        <th>No</th>
                        <th scope="col">Table Name</th>
                        <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="table_list"></tbody>
                </table>
            </div>
        </div>
    </div><!-- /.Table Listing -->

    <!-- Database Create Popup Modal -->
    <div class="modal fade" id="createDatabase" tabindex="-1" role="dialog" aria-labelledby="createDatabase" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDatabase">Create New Database</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="database_form">
                    <div class="form-group">
                        <label for="name" class="col-form-label">Database Name:</label>
                        <input type="text" name="name" required class="form-control" id="name">
                    </div>
                    <input type="hidden" name="request" value="create_database"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onClick="createDatabase()">Submit</button>
            </div>
            </div>
        </div>
    </div><!-- /.Database Create Popup Modal -->
    
    <!-- Table Create Popup Modal -->
    <div class="modal fade" id="createTable" tabindex="-1" role="dialog" aria-labelledby="createTable" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Table</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="table_form">
                    <div class="form-group">
                        <label for="name" class="col-form-label">Table Name:</label>
                        <input type="text" name="table_name" required class="form-control" id="table_name">
                    </div>
                    
                    <div class="form-group">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>                             
                                        <th>Type</th>                             
                                        <th>Length/Values</th>                             
                                        <th>Action</th>                             
                                    </tr>
                                </thead>
                                <tbody class="table_row">
                                    <tr>
                                        <td>
                                            <input type="text" name="name[]" id="name_0" required class="form-control" placeholder="Enter name">
                                        </td>
                                        <td>
                                            <select class="form-control" name="type[]" id="type_0" required onChange="checkType(0)">
                                                <option selected>Select Type</option>
                                                <option value="CHAR">CHAR</option>
                                                <option value="VARCHAR">VARCHAR</option>
                                                <option value="TEXT">TEXT</option>
                                                <option value="DATE">DATE</option>
                                                <option value="INT">INT</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="length[]" id="length_0" required placeholder="Enter length"/>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm" onClick="addTableRow()" title="Add">+</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" name="request" value="create_table"/>
                    <input type="hidden" name="database_name" value="" class="database_name"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onClick="createTable()">Submit</button>
            </div>
            </div>
        </div>
    </div><!-- /.Table Create Popup Modal -->
    
    <!-- Table Edit Popup Modal -->
    <div class="modal fade" id="editTable" tabindex="-1" role="dialog" aria-labelledby="editTable" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit New Table</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="edit_table_from">
                    <div class="form-group">
                        <label for="name" class="col-form-label">Table Name:</label>
                        <input type="text" name="table_name" required readonly class="form-control" id="edit_table_name">
                    </div>
                    <div class="form-group">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>                             
                                        <th>Type</th>                             
                                        <th>Length/Values</th>                             
                                        <th>Action</th>                             
                                    </tr>
                                </thead>
                                <tbody class="edit_table_row"></tbody>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" name="request" value="edit_table"/>
                    <input type="hidden" name="database_name" value="" class="database_name"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onClick="editTable()">Submit</button>
            </div>
            </div>
        </div>
    </div><!-- /.Table Edit Popup Modal -->

    <!-- JS -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
	<script src="js/jquery.validate.js"></script>
    <script src="js/toastr.min.js"></script>
  </body>
</html>