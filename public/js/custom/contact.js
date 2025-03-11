$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function(){

    $('#contact-form').submit(function(event) {
        event.preventDefault();
    });

    $('#contact-form').validate({
        rules: {
            name: {
                required: true,
            },
            email: {
                required: true,
            },
            phone: {
                required: true
            }
        },
        messages: {
            name: "Name is required!",
            email: "Enter a valid email!",
            phone: "Enter a valid phone number!",
        },
        errorElement: 'span',
        
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {

            var fieldNames   = [];
            var customFields = {};
            var isValid      = true;
            var isDuplicate  = false;
            var isDate       = true;
            var isNumber     = true;
            var isText       = true;
            
            $('.custom-field-row').each(function () {
                
                const fieldName  = $(this).find('input[name*="[field_name]"]').val();
                const fieldValue = $(this).find('input[name*="[field_value]"]').val();
                const dataType   = $(this).find('select[name*="[data_type]"]').val();

                if (!fieldName || !fieldValue) {
                    isValid = false;
                    $(this).find('input').addClass('is-invalid');
                } else {
                    $(this).find('input').removeClass('is-invalid');
                }

                if( fieldNames.includes(fieldName) ) {
                    isDuplicate = true;
                    $(this).find('input[name*="[field_name]"]').addClass('is-invalid');
                } else {
                    $(this).find('input[name*="[field_name]"]').removeClass('is-invalid');
                    fieldNames.push(fieldName);
                    customFields[fieldName] = {
                        value : fieldValue,
                        data_type : dataType
                    };
                }

                if (dataType === 'number') {
                    if (isNaN(fieldValue) || fieldValue.trim() === '') {
                        isNumber = false;
                        $(this).find('input[name*="[field_value]"]').addClass('is-invalid');
                    } else {
                        $(this).find('input[name*="[field_value]"]').removeClass('is-invalid');
                    }
                } else if (dataType === 'date') {
                    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
                    if (!datePattern.test(fieldValue)) {
                        isDate = false;
                        $(this).find('input[name*="[field_value]"]').addClass('is-invalid');
                    } else {
                        $(this).find('input[name*="[field_value]"]').removeClass('is-invalid');
                    }
                } else if (dataType === 'text') {
                    if (fieldValue.trim() === '') {
                        isText = false;
                        $(this).find('input[name*="[field_value]"]').addClass('is-invalid');
                    } else {
                        $(this).find('input[name*="[field_value]"]').removeClass('is-invalid');
                    }
                }

            });

            if (!isValid) {
                toastr.error('Please fill out all custom fields!');
                return false;
            }

            if (isDuplicate) {
                toastr.error('Duplicate field names are not allowed!');
                return false;
            }

            if (!isNumber) {
                toastr.error('Datatype number field value is not valide.');
                return false;
            }
            
            if (!isDate) {
                toastr.error('Datatype date field value is not valide. It should be in format of YYYY-MM-DD! Ex: 2025-03-10');
                return false;
            }

            if (!isText) {
                toastr.error('Datatype text field value is not valide.');
                return false;
            }

            const formData = new FormData(form);
            
            var keysDelete = [];
            for (var keys of formData.entries()) {
                if (keys[0].includes('custom_fields[') && ( keys[0].includes('][field_') || keys[0].includes('][data_') )) {
                    keysDelete.push(keys[0]);
                }
            }

            keysDelete.forEach(key => formData.delete(key));

            Object.keys(customFields).forEach(fieldName => {
                formData.append('custom_fields['+fieldName+'][value]', customFields[fieldName].value);
                formData.append('custom_fields['+fieldName+'][data_type]', customFields[fieldName].data_type);
            });

            $.ajax({
                url         : base_url + '/contact/save',
                method      : 'POST',
                processData : false, 
                contentType : false,
                data        : formData,
                beforeSend: function() {
                    swal.fire({
                        title: 'Please Wait..!',
                        text: 'Is working..',
                        onOpen: function() {
                            swal.showLoading()
                        }
                    })  
                },
                success: function(response) {
                    console.log(response);
                    if( response.success == true ) {
                        dataTable(false);
                        toastr.success(response.message);
                        $("#add-contact").trigger('click');
                        $("#contact-form")[0].reset();
                        $('.custom-field-row').remove();
                        $("#contact_id").val("");
                        $('#card-header-text').text('Contact Add');
                    } else {
                        toastr.error(response.message);
                    }
                },
                complete: function() {
                    swal.hideLoading();
                    swal.close();
                },
                error: function(xhr, status, error) {
                    swal.hideLoading();
                    swal.close();
                    toastr.error(error.message);
                }
            });
            return false;
        }
    });

    // reset form
    $(document).on('click', '#reset-form', function () { 
        $("#contact-form")[0].reset();
        $("#contact_id").val("");
        $('#card-header-text').text('Contact Add');
    } );

    // Datatable show
    var contactDataTable = null;

    dataTable(false);

    function dataTable(isFilter) {

        if (contactDataTable !== null) {
            contactDataTable.destroy();
        }

        contactDataTable = $('#contact-table').DataTable({
            "paging"       : true,
            "lengthChange" : false,
            "searching"    : false,
            "ordering"     : true,
            "info"         : true,
            "autoWidth"    : false,
            "responsive"   : true,
            "processing"   : true,
            "serverSide"   : true,
            "pageLength"   : 10,
            "ajax": {
                "url"        : base_url + "/contact/list",
                "type"       : "POST",
                "data"       : function (d) {
                    d.page         = Math.floor(d.start / d.length) + 1;
                    d.length       = d.length;
                    d.name         = isFilter == true ? $("#search_name").val() : '';
                    d.email        = isFilter == true ? $("#search_email").val() : '';
                    d.gender       = isFilter == true ? $("#search_gender").val() : '';
                    d.custom_field = isFilter == true ? $("#search_custom_field").val() : '';
                },
                "dataSrc": function(response) {
                    response.recordsTotal    = response.total;
                    response.recordsFiltered = response.total;
                    return response.data;
                }
            },
            "columns": [
                { 
                    "data"   : "profile_image", 
                    "render" : function(data) {
                        if (data && data !== null && data.trim() !== "") {
                            return "<img src='/uploads/contact/profile_image/" + data + "' class='profile-img img-fluid' style='width: 100%; height: 50px;' />";
                        } else {
                            return "<img src='/images/default/image_not_available.png' class='profile-img img-fluid' style='max-width: 100px; height: 50px;' />";
                        }
                    }
                },
                { "data" : "name" },
                { "data" : "email" },
                { "data" : "phone" },
                { "data" : "gender" },
                {
                    "data"   : null,
                    "render" : function(data, type, row) {
                        return "<div class='btn-group'>" +
                            "<button class='btn btn-sm btn-success view-contact' data-id='"+row.id+"'><i class='far fa-eye'></i></button>" +
                            "<button class='btn btn-sm btn-info edit-contact' data-id='"+row.id+"'><i class='far fa-edit'></i></button>" +
                            "<button class='btn btn-sm btn-warning merge-contact' data-id='"+row.id+"'><i class='fas fa fa-sync-alt'></i></button>" +
                            "<button class='btn btn-sm btn-danger delete-contact' data-id='"+row.id+"'><i class='far fa-trash-alt'></i></button>" +
                            "</div>";
                    }
                }
            ]
        });
        
    }

    // Custom filter.
    $(document).on('click', '#custom-filter', function () {
        dataTable(true);
    });

    // Get contact details for edit.
    $(document).on('click', '.edit-contact', function () {
        getContactDetails($(this).data('id'), 'edit');
    });

    // Common function for get details from sever
    function getContactDetails(contactId, action) {
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });  
        $.ajax({
            url      : base_url+'/contact/details',
            type     : 'POST',
            data     : {id: contactId, action : action},
            dataType : 'json',
            beforeSend: function() {
                swal.fire({
                    title: 'Please Wait..!',
                    text: 'Is working..',
                    onOpen: function() {
                        swal.showLoading()
                    }
                })  
            },
            success : function(response) {
                if( response.success == true ) {
                    
                    var data = response.data;

                    if( action == 'edit' ) {
                        editContact(data);
                    } else {
                        viewContact(data);
                    }

                    return data;
                } else {
                    toastr.error(response.message);
                }
            },
            complete: function() {
                swal.hideLoading();
                swal.close();
            },
            error: function(xhr, status, error) {
                swal.hideLoading();
                swal.close();
                toastr.error(error.message);
            }
        });
    }

    // Edit contact form data.
    function editContact(data) {

        $('#contact_id').val(data.id);
        $('#name').val(data.name);
        $('#email').val(data.email);
        $('#phone').val(data.phone);
        $('input[type="radio"][value="'+data.gender+'"]').prop('checked', true);
        $('#cur_profile_image').val(data.profile_image);
        $('#cur_additional_file').val(data.additional_file);

        if (data.custom_fields && data.custom_fields.length > 0) { 
            $('.custom-field-row').remove();
            var count = 1;
            data.custom_fields.forEach(field => {
                var uniqueId = Date.now()+count; // uniqueid
                appendCustomFields(field.field_name,field.field_value,field.data_type, uniqueId);
                count++;
            });
        }

        $('#card-header-text').text('Contact Edit');
        if( $('#contact-add-card').hasClass('collapsed-card') ) {
            $("#add-contact").trigger('click');
        }
    }

    // View contact details get.
    $(document).on('click', '.view-contact', function () {
        getContactDetails($(this).data('id'), 'view');
    });

    function viewContact(data) {
        
        $("#modal-contact-view .card-body").empty();
    
        var mainContactTable = `
            <h5>Contact Details</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr><th>Name</th><td>`+data.name+`</td></tr>
                    <tr><th>Email</th><td>`+data.email+`</td></tr>
                    <tr><th>Phone</th><td>`+data.phone+`</td></tr>
                    <tr><th>Gender</th><td>`+data.gender+`</td></tr>`;

        if (data.profile_image_url) {
            mainContactTable += `
                <tr>
                    <th>Profile Image</th>
                    <td>
                        <a href="`+data.profile_image_url+`" download>Download Profile Image</a>
                    </td>
                </tr>`;
        }
    
        if (data.additional_file_url) {
            mainContactTable += `
                <tr>
                    <th>Additional File</th>
                    <td>
                        <a href="`+data.additional_file_url+`" download>Download Additional File</a>
                    </td>
                </tr>`;
        }

        mainContactTable += `</tbody>
            </table>`;
    
        if (data.custom_fields && data.custom_fields.length > 0) {
            mainContactTable += `
                <br/><br/><h5>Custom Fields</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Field Name</th><th>Data Type</th><th>Field Value</th></tr>
                    </thead>
                    <tbody>
            `;
    
            data.custom_fields.forEach(field => {
                mainContactTable += `<tr><td>`+field.field_name+`</td><td>`+field.data_type.charAt(0).toUpperCase() + field.data_type.slice(1)+`</td><td>`+field.field_value+`</td></tr>`;
            });
    
            mainContactTable += `</tbody></table>`;
        }
    
        $("#modal-contact-view .card-body").append(mainContactTable);
    
        if (data.merged_contacts && data.merged_contacts.length > 0) {
            var mergedContactsTable = `
                <br/><br/><h5>Merged Contacts</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
    
            data.merged_contacts.forEach(contact => {
                mergedContactsTable += `
                    <tr>
                        <td>`+contact.name+`</td>
                        <td>`+contact.email+`</td>
                        <td>`+contact.phone+`</td>
                        <td>`+contact.gender+`</td>
                    </tr>
                `;
            });
    
            mergedContactsTable += `</tbody></table>`;
    
            $("#modal-contact-view .card-body").append(mergedContactsTable);
        }
    
        $("#modal-contact-view").modal('show');
    }
    
    
    // View contact data.
    // function viewContact(data) {
        
    // }

    // Delete contact
    $(document).on('click', '.delete-contact', function () {
        
        var id = $(this).data('id');

        swal.fire({
            title: 'Are you sure to delete?',
            text: "Contact and related all records will be delete. And action can't be Undone!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
            
        }).then(function(result) { 

            if (result.value) { 

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });  
                $.ajax({
                    url      : base_url+'/contact/delete',
                    type     : 'POST',
                    data     : {id: id},
                    dataType : 'json',
                    beforeSend: function() {
                        swal.fire({
                            title: 'Please Wait..!',
                            text: 'Is working..',
                            onOpen: function() {
                                swal.showLoading()
                            }
                        })            
                    },
                    success : function(response) { 
                        if( response.success == true ) {
                            toastr.success(response.message);
                            dataTable(false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    complete: function() {
                        swal.hideLoading();
                        swal.close();
                    },
                    error: function(xhr, status, error) {
                        swal.hideLoading();
                        swal.close();
                        toastr.error(error.message);
                    }
                });
            }
        } );
    });

    // Add custom field
    $(document).on('click', '#add-cust-field', function () {
        var fieldName = '';
        var fieldValue = '';
        var dataType = 'text';
        var uniqueId = Date.now();
        appendCustomFields(fieldName,fieldValue,dataType,uniqueId);
    });

    function appendCustomFields(fieldName,fieldValue, dataType, uniqueId) {

        $('.contact-card-body').append(`
            <div class="row custom-field-row" data-id="`+uniqueId+`">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="field_name_`+uniqueId+`">Field Name</label>
                        <input type="text" name="custom_fields[`+uniqueId+`][field_name]" 
                               class="form-control" id="field_name_`+uniqueId+`" value="`+fieldName+`" 
                               placeholder="Enter Field Name">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="data_type_`+uniqueId+`">Field Name</label>
                        <select class="custom-select" name="custom_fields[`+uniqueId+`][data_type]" id="data_type_`+uniqueId+`">
                            <option value="text" ${dataType === 'text' ? 'selected' : ''}>Text</option>
                            <option value="number" ${dataType === 'number' ? 'selected' : ''}>Number</option>
                            <option value="date" ${dataType === 'date' ? 'selected' : ''}>Date</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="field_value_`+uniqueId+`">Field Value</label>
                        <div class="input-group">
                        <input type="text" name="custom_fields[`+uniqueId+`][field_value]" 
                               class="form-control" id="field_value_`+uniqueId+`"  value="`+fieldValue+`"
                               placeholder="Enter Field Value">
                               <div class="input-group-append">
                                <span class="btn btn-danger remove-field" data-id="`+uniqueId+`">Remove</span>
                            </div>
                            </div>
                    </div>
                </div>
                
            </div>
        `);
    }

    // Remove custom field
    $(document).on('click', '.remove-field', function () {
        const fieldId = $(this).data('id');
        $('.custom-field-row[data-id="'+fieldId+'"]').remove();
    });

    // Merge contact
    $(document).on('click', '.merge-contact', function () {
        
        var contactId = $(this).data('id');
        $("#secondary_contact_id").val(contactId);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });  
        $.ajax({
            url      : base_url+'/contact/list/primary',
            type     : 'POST',
            data     : {secondary_id: contactId},
            dataType : 'json',
            beforeSend: function() {
                swal.fire({
                    title: 'Please Wait..!',
                    text: 'Is working..',
                    onOpen: function() {
                        swal.showLoading()
                    }
                })  
            },
            success : function(response) {
                if( response.success == true ) {
                    
                    var data = response.data;

                    $("#master_contact_id").empty();
                    $("#master_contact_id").append('<option value="">Select Primary Contact</option>');

                    data.forEach(function(contact) {
                        $("#master_contact_id").append(
                            '<option value="' + contact.id + '">' + contact.name + '</option>'
                        );
                    });

                    $("#modal-contact-merge").modal('show');

                } else {
                    toastr.error(response.message);
                }
            },
            complete: function() {
                swal.hideLoading();
                swal.close();
            },
            error: function(xhr, status, error) {
                swal.hideLoading();
                swal.close();
                toastr.error(error.message);
            }
        });
    });

    $(document).on('click', '#merge', function () { 

        var secondary_contact_id = $("#secondary_contact_id").val();
        var master_contact_id    = $("#master_contact_id").val();

        if (!master_contact_id) {
            toastr.error('Please select primary contact!');
            return false;
        }

        swal.fire({
            title: 'Are you sure to want merge?',
            text: "After merge action can't be undo!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
            
        }).then(function(result) { 

            if (result.value) {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });  
                $.ajax({
                    url      : base_url+'/contact/merge',
                    type     : 'POST',
                    data     : {
                        secondary_contact_id: secondary_contact_id,
                        master_contact_id: master_contact_id
                    },
                    dataType : 'json',
                    beforeSend: function() {
                        swal.fire({
                            title: 'Please Wait..!',
                            text: 'Is working..',
                            onOpen: function() {
                                swal.showLoading()
                            }
                        })  
                    },
                    success : function(response) {
                        if( response.success == true ) {
                            toastr.success(response.message);
                            $(".merge-model").trigger('click');
                            dataTable(false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    complete: function() {
                        swal.hideLoading();
                        swal.close();
                    },
                    error: function(xhr, status, error) {
                        swal.hideLoading();
                        swal.close();
                        toastr.error(error.message);
                    }
                });
            }
        });

    });

});