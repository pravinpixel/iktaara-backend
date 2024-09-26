<?php

namespace App\Models\Product;

use App\Models\Category\MainCategory;
use App\Models\Master\Brands;
use App\Models\RecentView;
use App\Models\Reviews;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = [
        'image'
    ];
    protected $fillable = [
        'product_name',
        'hsn_code',
        'product_url',
        'sku',
        'price',
        'mrp',
        'discount_option',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'status',
        'quantity',
        'has_video_shopping',
        'stock_status',
        'brand_id',
        'category_id',
        'tag_id',
        'label_id',
        'is_display_home',
        'is_featured',
        'is_brand_featured',
        'is_best_selling',
        'is_new',
        'tax_id',
        'description',
        'technical_information',
        'feature_information',
        'specification',
        'brochure_upload',
        'base_image',
        'approved_by',
        'approved_at',
        'added_by',
        'seller_price'
    ];

    public function productCategory()
    {
        return $this->hasOne(ProductCategory::class, 'id', 'category_id');
    }

    public function productBrand()
    {
        return $this->hasOne(Brands::class, 'id', 'brand_id');
    }

    public function productTag()
    {
        return $this->hasOne(MainCategory::class, 'id', 'tag_id')->where(['slug' => 'product-tags', 'status' => 'published']);
    }

    public function productLabel()
    {
        return $this->hasOne(MainCategory::class, 'id', 'label_id')->where(['slug' => 'product-labels', 'status' => 'published']);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function productMeasurement()
    {
        return $this->hasOne(ProductMeasurement::class, 'product_id', 'id');
    }

    public function productDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id', 'id');
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductWithAttributeSet::class, 'product_id', 'id');
    }

    public function productMappedAttributes()
    {
        return $this->hasMany(ProductMapAttribute::class, 'product_id', 'id');
    }

    public function productMeta()
    {
        return $this->hasOne(ProductMetaTag::class, 'product_id', 'id');
    }

    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'added_by');
    }

    public function productRelated()
    {
        return $this->hasMany(ProductRelatedRelation::class, 'from_product_id', 'id')->select('product_related_relations.*')->join(
            'products',
            'products.id',
            '=',
            'product_related_relations.to_product_id'
        )->whereNull('products.deleted_at');
    }

    public function productCrossSale()
    {
        return $this->hasMany(ProductCrossSaleRelation::class, 'from_product_id', 'id')->select('product_cross_sale_relations.*')->join(
            'products',
            'products.id',
            '=',
            'product_cross_sale_relations.to_product_id'
        )->whereNull('products.deleted_at');
    }

    public function productLinks()
    {
        return $this->hasMany(ProductLink::class, 'product_id', 'id')->where('url_type', '!=', 'video_link');
    }

    public function productAllLinks()
    {
        return $this->hasMany(ProductLink::class, 'product_id', 'id');
    }

    public function productVideoLinks()
    {
        return $this->hasMany(ProductLink::class, 'product_id', 'id')->where('url_type', 'video_link');
    }

    public function ratings()
    {
        return $this->hasMany(Reviews::class, 'product_id', 'id')->where('status', '1');
    }

    public function getImageAttribute()
    {
        if (isset($this->attributes['base_image'])) {
            if (!Storage::exists($this->attributes['base_image'])) {
                return asset('assets/logo/no-img-1.png');
            } else {
                return asset(Storage::url($this->attributes['base_image']));
            }
        } else {
            return '';
        }
    }

    public function productReviews()
    {
        return $this->hasMany(Reviews::class, 'product_id', 'id')->select('reviews.*', 'customers.first_name', 'customers.last_name')
            ->leftJoin('customers', 'customers.id', '=', 'reviews.customer_id')->where('reviews.status', 1);
    }

    public function views(){
        return $this->hasMany(RecentView::class, 'product_id', 'id')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
    }
}
