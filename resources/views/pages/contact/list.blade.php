@extends('layouts.app')

@push('third_party_stylesheets')

@endpush

@section('content')
    
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Contact</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Contact</a></li>
                        <li class="breadcrumb-item active">list</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <form role="form" id="contact-form">
                <div class="card card-teal collapsed-card" id="contact-add-card">
                    <div class="card-header">
                        <h3 class="card-title" id="card-header-text">Contact Add</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-block bg-gradient-white btn-xs" id="add-contact" data-card-widget="collapse">Add <i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body contact-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" value="" class="form-control" id="name" placeholder="Enter name">
                                    <input type="hidden" name="id" value="" class="form-control" id="contact_id" placeholder="">
                                    <meta name="csrf-token" content="{{ csrf_token() }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Gender</label>
                                    <div>
                                        <div class="form-check d-inline">
                                          <input class="form-check-input" type="radio" name="gender" value="Male" checked="">
                                          <label class="form-check-label">Male</label>
                                        </div>
                                        <div class="form-check d-inline">
                                          <input class="form-check-input" type="radio" name="gender" value="Female">
                                          <label class="form-check-label">Female</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="text" name="email" value="" class="form-control" id="email" placeholder="Enter email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" value="" class="form-control" id="phone" pattern="[0-9]{07,15}" placeholder="Enter phone number.">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_image_label">Image</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="profile_image" name="profile_image">
                                            <input type="hidden" class="form-control" id="cur_profile_image" name="cur_profile_image">
                                            <label class="custom-file-label" for="profile_image">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_file_label">Additional File</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="additional_file" name="additional_file">
                                            <input type="hidden" class="form-control" id="cur_additional_file" name="cur_additional_file">
                                            <label class="custom-file-label" for="additional_file">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- /.row -->  
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-sm btn-info float-left" id="add-cust-field">Add Custom Field</button>
                        <button type="submit" class="btn btn-sm btn-primary float-right save-contact">Save</button>
                        <button type="button" class="btn btn-sm btn-secondary float-right" id="reset-form">Reset</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-12">
                    <div class="card card-teal">
                        <div class="card-header">
                            <h3 class="card-title">Contact List</h3>
                            <div class="card-tools">
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="search_name">Name</label>
                                        <input type="text" name="search_name" value="" class="form-control" id="search_name" placeholder="Enter name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search_email">Email</label>
                                        <input type="text" name="search_email" value="" class="form-control" id="search_email" placeholder="Enter email">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="search_gender">Gender</label>
                                        <select class="custom-select" name="search_gender" id="search_gender">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search_custom_field">Custom Filed</label>
                                        <input type="text" name="search_custom_field" value="" class="form-control" id="search_custom_field" placeholder="Enter custom field or value of custom field">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="custom-filter"></label>
                                        <div class="iput-group">
                                            <button type="button" class="btn btn-md btn-success float-right filter-button" id="custom-filter">Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="contact-table" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    
    <!-- .model -->
    <div class="modal fade" id="modal-contact-view">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-header-text">Contact Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- form start -->
                    <div class="card-body">
                        
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm float-right" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.model -->

    <!-- .model -->
    <div class="modal fade" id="modal-contact-merge">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-header-text">Contact Merge</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- form start -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="master_contact_id">Primary Contact</label>
                                    <select class="custom-select" name="master_contact_id" id="master_contact_id">
                                        <option value="">Select Primary Contact</option>
                                    </select>
                                </div>
                                <input type="hidden" name="secondary_contact_id" id="secondary_contact_id" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm merge-model" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-md btn-success float-right merge" id="merge">Merge</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.model -->

@endsection

@push('page_scripts')
    <script src="{{ asset('js/custom/contact.js')}}"></script>
@endpush