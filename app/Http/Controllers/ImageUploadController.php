<?php

namespace App\Http\Controllers;

use App\ImageHelper;
use Illuminate\Support\Facades\File;
use App\Models\Product\ProductImage;
use App\Models\Product\Product;

class ImageUploadController extends Controller
{
    use ImageHelper;
    public function index()
    {
        $sourceFolderPath = public_path('bulk_images');
        if (File::isDirectory($sourceFolderPath)) {
            $imageFiles       = File::allFiles($sourceFolderPath);
            $formatRootFolder = $this->formatRootFolder($imageFiles);
            foreach ($formatRootFolder as $folderRootName =>  $files) {
                $Product        = Product::where('sku', $folderRootName)->first();
                $product_id     = $Product->id;
                $insertedImages = [];
                foreach ($files as $key =>  $file) {
                    $insertedImages[] =  $this->uploadImage($product_id, $key, $file);
                }
                if (!empty($insertedImages)) {
                    ProductImage::insert($insertedImages);
                }
            }
            dd('Image Uploaded Success');
        } else {
            dd("There no bulk image folder");
        }
    }
}
