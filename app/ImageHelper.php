<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Models\Product\Product;

trait ImageHelper
{
    public  function  uploadImage($product_id, $iteration, $File)
    {
        $this->moveToStorage($product_id, "default",  $File);
        $fileNamePreview = $this->moveToStorage($product_id, "detailPreview",  $File);
        $fileName        = $this->moveToStorage($product_id, "gallery",  $File);
        $fileNameThumb   = $this->moveToStorage($product_id, "thumbnail",  $File);

        return [
            'gallery_path'  => $fileName,
            'image_path'    => $fileNameThumb,
            'preview_path'  => $fileNamePreview,
            'product_id'    => $product_id,
            'file_size'     => $File->getSize(),
            'is_default'    => ($iteration == 1) ? 1 : "0",
            'order_by'      => $iteration,
            'status'        => 'published'
        ];
    }

    public function moveToStorage($product_id, $type, $file)
    {
        $fileName    = $file->getFilename();
        $newFilePath = "products/$product_id/$type/" . $fileName;
        if ($type === "default" && str_contains($fileName, '-1')) {
            if (!(Storage::exists("public/" . $newFilePath))) {
                Storage::disk('public')->put($newFilePath, File::get($file));
                $Product = Product::find($product_id);
                $Product->base_image = "public/".$newFilePath;
                $Product->update();
            }
        }
        if ($type !== "default") {
           // if (!(Storage::exists("public/" . $newFilePath))) {
                $this->compressImage($type, $newFilePath, $file);
           // }
        }
        return $newFilePath;
    }

    public function compressImage($type, $newFilePath, $file)
    {
        if ($type === 'thumbnail') {
            $img = Image::make($file)->resize(120, 120)->encode();
        } else {
            $img  = Image::make($file)->resize(1000, 1000)->encode();
        }
        Storage::disk('public')->put($newFilePath, $img);
    }

    public function formatRootFolder($imageFiles)
    {
        $groupedImages = [];
        foreach ($imageFiles as $file) {
            $relativePath = $file->getRelativePath();
            $rootFolder = explode('/', $relativePath)[0]; // Get the root folder name
            $groupedImages[$rootFolder][] = $file;
        }
        return $groupedImages;
    }
}
