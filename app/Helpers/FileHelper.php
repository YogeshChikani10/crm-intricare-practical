<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

class FileHelper
{
    // Handle the file upload things
    static function uploadFile( $file, $filePath, $mimes, $size ) {

        $fileValidator = Validator::make( ['file' => $file], [
            'file' => 'mimes:'.$mimes.'|max:'.$size
        ], [
            'file.mimes' => 'Profile image must be a valid image file ('.$mimes.')',
            'file.max'   => 'Uploaded profile image larger then '.($size/1024).'MB in size',
        ]);

        if ( $fileValidator->fails() ) {
            return ['success' => false, 'message' => $fileValidator->errors()->first(), 'fileName' => ''];
        }

        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileName);
        $fileName = $fileName.'_'.time().'.'.$file->getClientOriginalExtension();

        if ( ! file_exists( public_path( $filePath ) ) ) {
            mkdir( public_path( $filePath ), 0777, true );
        }

        $file->move( public_path($filePath ), $fileName );

        return ['success' => true, 'message' => 'File added.', 'fileName' => $fileName];
    }
}