<?php


namespace App\Utils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class UploadFile
 * @package App\Enums
 */
class UploadFile
{
    public static function upload($data, $folder, $defaultExtension = null, $old_link_file = null)
    {
        $validator = Validator::make($data, [
            'file' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($defaultExtension == "base64") {
            $arr = explode(';', $data['file']);

            if (isset($arr[1])) {
                $data = str_replace('base64,', '', $arr[1]);
                $data = str_replace(' ', '+', $data);
            } else {
                $data = $data['file'];
            }

            $data = base64_decode($data);
        } else {
            $data = $data['file'];
        }

        $f         = finfo_open();
        $mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
        $extension = explode('/', $mime_type)[1];

        if ($extension == 'plain') {
            $extension = $defaultExtension;
        }

        $fileName = base64_encode(hash('md5', rand(111111111, 999999999) . time()));
        $s3       = App::make('aws')->createClient('s3');

        //image/jpeg
        //image/png
        //application/pdf
        $contantType = '';

        if (in_array($extension, ['png', 'jpeg', 'jpg', 'gif', 'bmp', 'webp'])) {
            $contantType = 'image/' . $extension;
        } else {
            if (in_array($extension, ['pdf', 'xhtml+xml', 'xml', 'vnd.mspowerpoint', 'pkcs12', 'octet-stream'])) {
                $contantType = 'application/' . $extension;
            }
        }

        $path = $s3->putObject([
            'Bucket'          => config('filesystems.disks.s3.bucket'),
            'ContentEncoding' => $extension,
            'ContentType'     => $contantType,
            'Key'             => $folder . $fileName . '.' . $extension,
            'Body'            => $data,
        ]);

        // Remove old picture
        if ($old_link_file) {
            $old_key = explode($folder, $old_link_file);

            if (isset($old_key[1])) {
                $s3->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key'    => urldecode($folder . $old_key[1]),
                ]);
            }
        }

        return $path['ObjectURL'];
    }

    public static function deleteUpload($file_link, $folder)
    {
        $s3 = App::make('aws')->createClient('s3');

        $old_key = explode($folder, $file_link);

        if (isset($old_key[1])) {
            $s3->deleteObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => urldecode($folder . $old_key[1]),
            ]);
        }
    }
}
