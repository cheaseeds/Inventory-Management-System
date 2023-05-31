<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Inventory Management System</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="styles/index.css">
        <link rel="stylesheet" href="node_modules/tablesorter/dist/css/theme.default.min.css"> 
    </head>

    <body>
        <header class="page-header">
            <a href="home.php" style="color: inherit; text-decoration: none;">
                <h1 id="prod-name">Inventory Management System</h1>
            </a>
        </header>
        
        <div class="container">
                <input type="text" id="search-input" onkeyup="searchFunction()" placeholder="Search by item name">
                <button class="add-button" onclick="addItem()">Add Item</button>
        </div>
        
        <main class="page-content">
            <table id="inventory-table" class="tablesorter">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Item Link</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>


                <script>
                    //function that creates the table from the response data
                    function renderTable(inventory) {
                        var table = document.getElementById("inventory-table");
                        var tbody = document.createElement("tbody");
                        table.appendChild(tbody);

                        inventory.forEach(function(item) {
                            var row = tbody.insertRow();

                            var itemNameCell = row.insertCell();
                            itemNameCell.id = "itemName";
                            itemNameCell.textContent = item.item_name;

                            var quantityCell = row.insertCell();
                            quantityCell.id = "quantity";
                            quantityCell.textContent = item.quantity;

                            var urlCell = row.insertCell();
                            urlCell.id = "url";
                            var link = document.createElement("a");
                            link.href = item.url;
                            link.textContent = "Item Link";
                            urlCell.appendChild(link);

                            var lastUpdateCell = row.insertCell();
                            lastUpdateCell.id = "last_update";
                            lastUpdateCell.textContent = item.last_update;

                            var editCell = row.insertCell();
                            editCell.id = "edit-button";
                            var editButton = document.createElement("button");
                            editButton.textContent = "Edit";
                            editButton.className = "add-button";
                            editCell.appendChild(editButton);
                            editButton.addEventListener("click", function() {
                                editItem(item.id, inventory);
                            });

                            var deleteCell = row.insertCell();
                            deleteCell.id = "delete-button";
                            var deleteButton = document.createElement("button");
                            deleteButton.textContent = "Delete";
                            deleteButton.className = "add-button";
                            deleteCell.appendChild(deleteButton);
                            deleteButton.addEventListener("click", function() {
                                deleteItem(item.id);
                            });

                        });
                    }

                    //request to get the data from the database and call renderTable()
                    var req = new XMLHttpRequest();
                    req.open("GET", "actions.php", true);
                    req.onload = function() {
                        if (req.status === 200) {
                            var inventoryData = JSON.parse(req.responseText);
                            renderTable(inventoryData);
                            $("#inventory-table").tablesorter();
                        }
                    };
                
                    req.send();
                </script>
            </table>
        </main> 
        
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Item</h2>
                <form id="editForm">
                    <div>
                        <label>Item Name:</label>
                        <input type="text" id="update-itemName">
                    </div>
                    <div>
                        <label>Quantity:</label>
                        <input type="number" id="update-quantity">
                    </div>
                    <div>
                        <label>Item Link:</label>
                        <input type="text" id="update-url">
                    </div>
                    <button type="submit" class="add-button">Save</button>
                </form>
            </div>
        </div>

        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="add-close">&times;</span>
                <h2>Add Item</h2>
                <form id="addForm">
                    <div>
                        <label>Item Name: </label>
                        <input type="text" id="add-itemName" required>
                    </div>
                    <div>
                        <label>Quantity: </label>
                        <input type="number" id="add-quantity" required>
                    </div>
                    <div>
                        <label>Item Link: </label>
                        <input type="text" id="add-url" required>
                    </div>
                    <button type="submit" class="add-button">Save</button>
                </form>
            </div>
        </div>

        <footer class="page-footer">
            &copy; 2023 Alexander Chea
        </footer>
        
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="node_modules/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
        <script>
            function editItem(id, inventoryData) {
                //Form adapted from https://www.w3schools.com/howto/howto_css_modals.asp
                var modal = document.getElementById("editModal");
                var span = document.getElementsByClassName("close")[0];
                
                modal.style.display = "block";
                
                span.onclick = function() {
                    modal.style.display = "none";
                }
                
                window.onclick = function(event) {
                    if (event.target == modal) {
                    modal.style.display = "none";
                    }
                }
                
                //form data
                var selectedItem = inventoryData.find(function(item) {
                    return item.id === id;
                });

                //modal form field selectors
                var itemNameField = document.getElementById("update-itemName");
                var quantityField = document.getElementById("update-quantity");
                var urlField = document.getElementById("update-url");

                //set form field values
                //if we update an item without any values, it could erase whatever we have in the database's entry
                itemNameField.value = selectedItem.item_name;
                quantityField.value = selectedItem.quantity;
                urlField.value = selectedItem.url;

                // Handle form submission
                var editForm = document.getElementById("editForm");
                editForm.addEventListener("submit", function(event) {
                    event.preventDefault();

                    //new values after being set by the form for submission
                    var itemName = itemNameField.value;
                    var quantity = quantityField.value;
                    var url = urlField.value;

                    var edreq = new XMLHttpRequest();
                    var data = "&itemName=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantity) + "&url=" + encodeURIComponent(url);
                    edreq.open("PUT", "actions.php?id="+ encodeURIComponent(id) + data, true);
                    edreq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    edreq.onload = function() {
                        if (edreq.status === 200) {
                            console.log("success");
                            modal.style.display = "none";
                        } else {
                            console.log("not status: 200");
                        }
                    };
                    edreq.send(data);
                    location.reload();
                    modal.style.display = "none";
                });
            }
            function deleteItem(id) {
                var delreq = new XMLHttpRequest();
                delreq.open("DELETE", "actions.php?id=" + encodeURIComponent(id), true);
                delreq.onload = function() {
                    if (delreq.status === 200) {
                        console.log("success")
                    } else {
                        console.log("not status: 200")
                    }
                };
                delreq.send();
                location.reload();

            }
            function addItem() {
                
                var modal = document.getElementById("addModal");
                var span = document.getElementsByClassName("add-close")[0];
                
                modal.style.display = "block";
                
                span.onclick = function() {
                    modal.style.display = "none";
                }
                
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }

                //modal form field selectors
                var itemNameField = document.getElementById("add-itemName");
                var quantityField = document.getElementById("add-quantity");
                var urlField = document.getElementById("add-url");


                // Handle form submission
                var addForm = document.getElementById("addForm");
                addForm.addEventListener("submit", function(event) {
                    event.preventDefault();

                    //new values after being set by the form for submission
                    var itemName = itemNameField.value;
                    var quantity = quantityField.value;
                    var url = urlField.value;

                    var addreq = new XMLHttpRequest();
                    var data = "itemName=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantity) + "&url=" + encodeURIComponent(url);
                    addreq.open("POST", "actions.php?" + data, true);
                    addreq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    addreq.onload = function() {
                        if (addreq.status === 200) {
                            console.log("success");
                            modal.style.display = "none";
                        } else {
                            console.log("not status: 200");
                        }
                    };
                    addreq.send(data);
                    location.reload();
                    modal.style.display = "none";
                });
            }
            
        </script>
        <script>
            //Adapted from https://www.w3schools.com/howto/howto_js_filter_lists.asp
            function searchFunction() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("search-input");
                filter = input.value.toUpperCase();
                table = document.getElementById("inventory-table");
                tr = table.getElementsByTagName("tr");

                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[0];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
            
        </script>
    </body>
</html> 






