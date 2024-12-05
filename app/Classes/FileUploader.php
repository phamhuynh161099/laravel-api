<?php
namespace App\Classes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Driver;


class FileUploader{

    protected $baseDir;
    protected $email;
    protected ImageManager $imageManager;


    public function __construct($email){
        $this->baseDir = config('uploader.baseDir');
        $this->email = $email;
        $this->imageManager = new ImageManager(['driver' => 'imagick']);
    }



    public function uploadFile(UploadedFile $file, string $type = 'image', ?array $customFolder = null): string{

        $this->validateFile($file, $type);


        $folderStructure = $this->createFolderStructure($type, $customFolder);
        $filename = $this->generateFilename($file);
        $path = $folderStructure[$type] . '/' . $filename;


        Storage::putFileAs($folderStructure[$type], $file, $filename);

        // if($type === 'image' && isset($folderStructure['thumb'])){
        //    $this->createThumbnails($folderStructure, $filename);
        // }

        return $path;

    }


    protected function validateFile(UploadedFile $file, string $type): void{
        $validator = Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:' . implode(',', config('uploader.allowed_types.' . $type)),
                    'max:' . config('uploader.max_file_size')
                ]
            ]
        );
        if($validator->fails()){
            throw new ValidationException($validator);
        }
    }


    protected function createFolderStructure(string $type, ?array $customFolder = null): array{

        $emailPrefix = Str::before($this->email, '@');
        $basePath = 'public/'.$emailPrefix;
        $defaultFolder = config('uploader.default_folder.'. $type);
        $folder = [
            'main' => $basePath
        ];

        foreach($defaultFolder as $key => $subFolder){
            $folder[$subFolder] = $basePath . '/' . $subFolder;
            if($customFolder){
                foreach($customFolder as $subCustomFolder){
                    $folder[$subFolder] = $folder[$subFolder] . '/' . $subCustomFolder;
                }
            }
        }

        foreach($folder as $f){
            if(!Storage::exists($f)){
                Storage::makeDirectory($f);
            }
        }

        return $folder;

    }

    protected function generateFilename(UploadedFile $file){
        return  Str::uuid(). '.' .  $file->clientExtension();
    }


    // thiếu 1 vài module, sẽ cập nhật sau
    protected function createThumbnails(array $folderStructure, string $filename): void{
        $originalPath = storage_path('app/' . $folderStructure['image'] . '/' . $filename);

        dd($this->imageManager);
        $image = $this->imageManager->make($originalPath);

        $imageWidth = $image->width();
        $imageHeight = $image->height();

        $aspecratio = $imageWidth/$imageHeight;


        foreach(config('uploader.thumb_size') as $size => $dimension){
            $thumbPath = storage_path('app/' . $folderStructure['thumb'] . '/' . $size . '_' . $filename);

            $image->resize($dimension['width'], $dimension['height'], function($constraint){
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($thumbPath);
        }

    }
}
