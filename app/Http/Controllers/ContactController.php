<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\ContactCustomFields;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Helpers\FileHelper;

class ContactController extends Controller
{
    protected $profilePath;
    protected $additionalPath;

    public function __construct() {
        $this->profilePath    = config('constant.CONTACT_PROFILE_PATH');;
        $this->additionalPath = config('constant.CONTACT_ADDITIONAL_FILE_PATH');
    }

    // Contact module page.
    public function contact(Request $request) {

        return view( 'pages.contact.list' );
    }

    // Customer save.
    public function save( Request $request ) {

        $validator = Validator::make( $request->all(), [
            'name'   => ['required'],
            'phone'  => ['required'],
            'email'  => ['required', 'email'],
            'gender' => ['required', 'in:Male,Female'],
        ]);
    
        if ( $validator->fails() ) {
            return response()->json( ['success' => false, 'message' => $validator->errors()->first()], 201 );
        }
    
        $data = [
            'name'   => $request->name, 
            'email'  => $request->email, 
            'phone'  => $request->phone, 
            'gender' => $request->gender, 
        ];

        // If profile image submit then handle.
        if ( $request->hasFile( 'profile_image' ) ) {
            
            $fileResp = FileHelper::uploadFile($request->file('profile_image'), $this->profilePath, 'jpeg,png,jpg,gif,svg', 2048 );

            if( $fileResp['success'] == false ) {
                return response()->json( $fileResp, 201 );
            }

            $data['profile_image'] = $fileResp['fileName'];
        }

        // If additional file submit then handle.
        if ( $request->hasFile( 'additional_file' ) ) {
            
            $fileResp = FileHelper::uploadFile($request->file('additional_file'), $this->additionalPath, 'jpeg,png,jpg,gif,svg,pdf,doc,docx,csv,xml,xsl,txt', 5120 );
            
            if( $fileResp['success'] == false ) {
                return response()->json( $fileResp, 201 );
            }

            $data['additional_file'] = $fileResp['fileName'];
        }

        if ( ! empty( $request->id ) ) { // Update existing contact.
            
            $contactUpate = Contact::where( 'id', $request->id )->update( $data );
            $msg          = "Contact updated successfully.";
            $contactId    = $request->id;
            
            if( $contactUpate ) {

                if ( $request->hasFile( 'profile_image' ) && file_exists(public_path($this->profilePath.$request->cur_profile_image)) ) { 
                    File::delete(public_path($this->profilePath.$request->cur_profile_image));
                }
                
                if ( $request->hasFile( 'additional_file' ) && file_exists(public_path($this->additionalPath.$request->cur_additional_file)) ) { 
                    File::delete(public_path($this->additionalPath.$request->cur_additional_file));
                }
            }
        
        } else { // Add new contact.
            
            $contact   = Contact::create( $data );
            $contactId = $contact->id;
            $msg       = "Contact added successfully.";
        }

        $this->addUpdateCustomFields( $request->all(),$contactId );
    
        return response()->json( ['success' => true, 'message' => $msg], 200 );
    }

    // Add or update custom field
    public function addUpdateCustomFields( $input, $contactId ) {

        if ( isset( $input['custom_fields'] ) &&  ! empty( $input['custom_fields'] ) ) { 

            // Delete existing data
            ContactCustomFields::where( 'contact_id', $contactId )->delete();
    
            // Add new data
            foreach( $input['custom_fields'] as $key=>$value ) {

                ContactCustomFields::create([
                    'contact_id'  => $contactId,
                    'field_name'  => $key,
                    'field_value' => $value['value'],
                    'data_type'   => $value['data_type'],
                ]);
            }
        }

        return true;
    }

    // Contact list
    public function list(Request $request) {

        $pageSize = $request->input( 'length' );
        $page     = $request->input( 'page' );
        $search   = $request->input( 'search' )['value'];
        
        $contact = Contact::where( 'parent_contact_id', 0 );
        
        if( isset( $request->name ) && ! empty( $request->name ) ) {
            $contact->where( 'name', $request->name );
        }
        if( isset( $request->gender ) && ! empty( $request->gender ) ) {
            $contact->where( 'gender', $request->gender );
        }
        if( isset( $request->email ) && ! empty( $request->email ) ) {
            $contact->where( 'email', $request->email );
        }
        if( isset( $request->custom_field ) && ! empty( $request->custom_field ) ) {
            $custom_field = $request->custom_field;
            $contact->whereHas( 'customFields', function ($query) use ($custom_field) {
                $query->where( 'field_name', $custom_field )
                      ->orWhere( 'field_value', $custom_field );
            });
        }

        $contact = $contact->orderBy( 'created_at', 'DESC' )
                    ->paginate( $pageSize, ['*'], 'page', $page );
        $total = $contact->total(); // Total count of records

        return response()->json([
            'data'  => $contact->items(), // Paginated items
            'total' => $total,
        ]);
    }

    // Contact details.
    public function details(Request $request) {

        if( ! $request->ajax() ) {
            return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
        }

        $contact = Contact::with(['customFields' => function ( $query ) {
                                $query->select( 'contact_id', 'field_name', 'field_value', 'data_type');
                            }])->find($request->id);
                            
                            
        if( ! empty( $contact ) ) {
            
            if( isset( $request->action ) && $request->action == 'view' ) {
                $contact->merged_contacts     = Contact::where('parent_contact_id', $contact->id)->get();
                $contact->profile_image_url   = ! empty( $contact->profile_image ) ? $this->profilePath.$contact->profile_image : '';
                $contact->additional_file_url = ! empty( $contact->additional_file ) ? $this->additionalPath.$contact->additional_file : '';
            }                    
            
            return response()->json( ['success' => true, 'message' => "Contact found successfully.", 'data' => $contact ], 200 );
        } else {
            return response()->json( ['success' => false, 'message' => "Contact not found.", 'data' => [] ], 201 );
        }
    }

    // Contact delete.
    public function delete( Request $request ) {

        if( ! $request->ajax() ) {
            return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
        }
        
        $contact = Contact::find($request->id);
        
        if( ! empty( $contact ) ) {

            // If profile image exists then delete
            if ( ! empty( $contact->profile_image ) &&  file_exists(public_path($this->profilePath.$contact->profile_image)) ) { 
                File::delete(public_path($this->profilePath.$contact->profile_image));
            }
            
            // If additional file exists then delete
            if ( ! empty( $contact->additional_file ) &&  file_exists(public_path($this->additionalPath.$contact->additional_file)) ) { 
                File::delete(public_path($this->additionalPath.$contact->additional_file));
            }

            Contact::where( 'id', $request->id )->delete();
            return response()->json( ['success' => true, 'message' => "Contact deleted successfully."], 200 );
        }
        
        return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
    }

    // Merge contact with concat secondary contact value if custom field same ( befote the add dataType column in contact_custom_fields)
    public function mergeContactWithConcat(Request $request) {

        if( ! $request->ajax() ) {
            return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
        }

        $validator = Validator::make( $request->all(), [
            'master_contact_id'    => 'required',
            'secondary_contact_id' => 'required',
        ], [
            'master_contact_id'    => 'Please select primary contact.',
            'secondary_contact_id' => 'Please select secondary contact.',
        ]);

        if ( $validator->fails() ) {
            return response()->json( ['success' => false, 'message' => $validator->errors()->first()], 201 );
        }

        $masterContactId    = $request->master_contact_id;
        $secondaryContactId = $request->secondary_contact_id;

        $masterContact    = Contact::with( 'customFields' )->find( $masterContactId );
        $secondaryContact = Contact::with( 'customFields' )->find( $secondaryContactId );

        if( empty( $secondaryContact ) || empty( $masterContact ) ) {
            return response()->json( ['success' => false, 'message' => 'Something went to wrong!'], 201 ); 
        }

        if( $masterContact->email != $secondaryContact->email ) {
            
            $emailField = $masterContact->customFields->firstWhere('field_name', 'Secondary Email');

            if( ! empty( $emailField ) ) {
                
                if( $emailField->field_value != $secondaryContact->email ) {
                    $emailField->field_value = $emailField->field_value.', '.$secondaryContact->email;
                    $emailField->save();
                }
                
            } else {

                $masterContact->customFields()->create([
                    'field_name'  => 'Secondary Email',
                    'field_value' => $secondaryContact->email,
                ]);
            }
        }

        if( $masterContact->phone != $secondaryContact->phone ) {
            
            $phoneField = $masterContact->customFields->firstWhere('field_name', 'Secondary Phone');

            if( ! empty( $phoneField ) ) {
                
                if( $phoneField->field_value != $secondaryContact->phone ) {
                    $phoneField->field_value = $phoneField->field_value.', '.$secondaryContact->phone;
                    $phoneField->save();
                }
            } else {

                $masterContact->customFields()->create([
                    'field_name'  => 'Secondary Phone',
                    'field_value' => $secondaryContact->phone,
                ]);
            }
        }

        foreach ( $secondaryContact->customFields as $secondaryField ) {
            
            $matchingField = $masterContact->customFields->firstWhere( 'field_name', $secondaryField->field_name );
    
            if ( $matchingField ) {

                if ( $matchingField->field_value != $secondaryField->field_value ) {
                    $matchingField->field_value = $matchingField->field_value.', '.$secondaryField->field_value;
                    $matchingField->save();
                }
            } else {

                $secondaryField->contact_id = $masterContact->id;
                $secondaryField->save();
            }
        }

        $secondaryContact->parent_contact_id = $masterContact->id;
        $secondaryContact->save();

        return response()->json( ['success' => true, 'message' => "Contact merged successfully."], 200 );
    }

    // Merge contact and keep primary contact value if custom fields same ( after the add dataType column in contact_custom_fields)
    public function mergeContact(Request $request) {

        if( ! $request->ajax() ) {
            return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
        }

        $validator = Validator::make( $request->all(), [
            'master_contact_id'    => 'required',
            'secondary_contact_id' => 'required',
        ], [
            'master_contact_id'    => 'Please select primary contact.',
            'secondary_contact_id' => 'Please select secondary contact.',
        ]);

        if ( $validator->fails() ) {
            return response()->json( ['success' => false, 'message' => $validator->errors()->first()], 201 );
        }

        $masterContactId    = $request->master_contact_id;
        $secondaryContactId = $request->secondary_contact_id;

        $masterContact    = Contact::with( 'customFields' )->find( $masterContactId );
        $secondaryContact = Contact::with( 'customFields' )->find( $secondaryContactId );

        if( empty( $secondaryContact ) || empty( $masterContact ) ) {
            return response()->json( ['success' => false, 'message' => 'Something went to wrong!'], 201 ); 
        }

        if( $masterContact->email != $secondaryContact->email ) {
            
            $emailField = $masterContact->customFields->firstWhere('field_name', 'Secondary Email');

            if( empty( $emailField ) ) {
                
                $masterContact->customFields()->create([
                    'field_name'  => 'Secondary Email',
                    'field_value' => $secondaryContact->email,
                ]);
            }
        }

        if( $masterContact->phone != $secondaryContact->phone ) {
            
            $phoneField = $masterContact->customFields->firstWhere('field_name', 'Secondary Phone');

            if( empty( $phoneField ) ) {
                
                $masterContact->customFields()->create([
                    'field_name'  => 'Secondary Phone',
                    'field_value' => $secondaryContact->phone,
                ]);
            }
        }

        foreach ( $secondaryContact->customFields as $secondaryField ) {
            
            $matchingField = $masterContact->customFields->firstWhere( 'field_name', $secondaryField->field_name );
    
            if ( empty( $matchingField ) ) {

                $secondaryField->contact_id = $masterContact->id;
                $secondaryField->save();
            }
        }

        $secondaryContact->parent_contact_id = $masterContact->id;
        $secondaryContact->save();

        return response()->json( ['success' => true, 'message' => "Contact merged successfully."], 200 );
    }

    // Primary Contact List
    public function primaryContactList(Request $request) {

        if( ! $request->ajax() ) {
            return response()->json( ['success' => false, 'message' => "Something went to wrong."], 404 );
        }
        
        $contact = Contact::where('id', '!=', $request->secondary_id)
                        ->where('parent_contact_id', 0)
                        ->get()
                        ->toArray();

        return response()->json( ['success' => true, 'message' => "Contact list found successfully.", 'data' => $contact], 200 );
    }

}
