<style>
    .card .card-header {
        min-height: 42px;
    }
</style>
<div class="row mb-7">
    <div class="col-md-6">
        <div class="fv-row mb-4">
            <label class="required fw-bold fs-6 mb-2">Category Name</label>
            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0"
                placeholder="Category Name" value="{{ $info->name ?? '' }}" />
        </div>
        <div class="fv-row mb-4">
            {{-- <label class="fw-bold fs-6 mb-2"> Is Parent </label>
            <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                <input class="form-check-input" type="checkbox" name="is_parent" id="is_parent" value="1"
                    @if ((isset($info->parent_id) && $info->parent_id == 0) || !isset($info->parent_id)) checked @endif />
            </div> --}}
            <div class="fv-row" id="parent-tab">
                <label class="required fw-bold fs-6 mb-2">Parent Category</label>
                <select name="parent_category" id="parent_category" aria-label="Select a Language"
                    data-control="select2" data-placeholder="Select Parent Category..." class="form-select mb-2">
                    <option value="0">Parent category</option>

                    @foreach ($data as $category)
                        <option value="{{ $category['cid'] }}"
                            @if ((isset($info->parent_id)) && $category['cid'] == $info->parent_id) selected="selected" @endif>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="fv-row mb-4">
            <label class="fw-bold fs-6 mb-2">Tag Line</label>
            <input type="text" name="tag_line" class="form-control form-control-solid mb-3 mb-lg-0"
                placeholder="Tag line" value="{{ $info->tag_line ?? '' }}" />
        </div>
        <div class="fv-row mb-4">
            <label class="fw-bold fs-6 mb-2">Description</label>
            <textarea class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Description" name="description"
                id="description" cols="30" rows="5">{{ $info->description ?? '' }}</textarea>
        </div>
        <div class="fv-row mb-4">
            <label class="required fw-bold fs-6 mb-2">Profit Percentage</label>
            <input type="text" name="profit_margin_percent" class="form-control form-control-solid mb-3 mb-lg-0"
                placeholder="Profit percentage" value="{{ $info->profit_margin_percent ?? '' }}" />
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div class="mb-4 ">
                    <label class="fw-bold fs-6 mb-2"> Published </label>
                    <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                        <input class="form-check-input" type="checkbox" name="status" value="1"
                            @if ((isset($info->status) && $info->status == 'published') || !isset($info->status)) checked @endif />
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="fv-row mb-5">
                    <label class="fw-bold fs-6 mb-2"> Is Show on Menu </label>
                    <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                        <input class="form-check-input" type="checkbox" name="is_home_menu" id="is_home_menu"
                            value="on" @if (isset($info->is_home_menu) && $info->is_home_menu == 'yes') checked @endif />
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="mb-5">
                    <label class="fw-bold fs-6 mb-2">Sorting Order</label>
                    <input type="text" name="order_by"
                        class="form-control numberonly form-control-solid mb-3 mb-lg-0" placeholder="Sorting Order"
                        value="{{ $info->order_by ?? '' }}" min="1" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="fv-row mb-5">
                    <label class="fw-bold fs-6 mb-2"> Is Instrumental Category </label>
                    <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                        <input class="form-check-input" type="checkbox" name="is_instrumental_category"
                            id="is_instrumental_category" value="on"
                            @if (isset($info->is_instrumental_category) && $info->is_instrumental_category == 'yes') checked @endif />
                    </div>
                </div>
            </div>
        </div>
        <div class="fv-row mb-5">
            <label class="fw-bold fs-6 mb-2"> Is Tax </label>
            <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                <input class="form-check-input" type="checkbox" name="is_tax" id="is_tax" value="on"
                    @if (isset($info->tax_id) && !empty($info->tax_id)) checked @endif />
            </div>
            <div class="fv-row @if (isset($info->tax_id) && !empty($info->tax_id)) @else d-none @endif" id="tax-tab">
                <label class="required fw-bold fs-6 mb-2">Taxes</label>
                <select name="tax_id" id="tax_id" class="form-select mb-2">
                    <option value="">--select Tax--</option>
                    @isset($taxAll)
                        @foreach ($taxAll as $item)
                            <option value="{{ $item->id }}" @if (isset($info->tax_id) && $info->tax_id == $item->id) selected @endif>
                                {{ $item->title }} ({{ $item->pecentage }}%)</option>
                        @endforeach
                    @endisset
                </select>
            </div>
        </div>

    </div>
    <div class="col-md-6">
        <div class=" mb-7">
            <div class="fv-row">
                <label class="d-block fw-bold fs-6 mb-5">Image Large</label>
                <div class="form-text">
                    Allowed file types: png, jpg,
                    jpeg. ( 752 * 722 pixels)
                </div>
            </div>
            <input id="image_remove_image" type="hidden" name="image_remove_image" value="no">
            <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                style="background-image: url({{ asset('userImage/no_Image.png') }})">
                @if ($info->image ?? '')
                    @php
                        $catImagePath = 'productCategory/' . $info->id . '/default/' . $info->image;
                        $url = Storage::url($catImagePath);
                        $path = asset($url);
                    @endphp

                    <div class="image-input-wrapper w-125px h-125px manual-image" id="manual-image"
                        style="background-image: url({{ $path }});">
                    </div>
                @else
                    <div class="image-input-wrapper w-125px h-125px manual-image" id="manual-image"
                        style="background-image: url({{ asset('userImage/no_Image.png') }});">
                    </div>
                @endif
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change Icon">
                    <i class="bi bi-pencil-fill fs-7"></i>
                    <input type="file" name="categoryImage" id="readUrl" accept=".png, .jpg, .jpeg" />
                </label>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar1">
                    <i class="bi bi-x fs-2" id="avatar_remove_logo"></i>
                </span>
            </div>
        </div>

        <div class=" mb-7">
            <div class="fv-row">
                <label class="d-block fw-bold fs-6 mb-5">Image Medium</label>
                <div class="form-text">
                    Allowed file types: png, jpg,
                    jpeg. ( 750 * 340 pixels)
                </div>
            </div>
            <input id="image_remove_medium" type="hidden" name="image_remove_medium" value="no">
            <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                style="background-image: url({{ asset('userImage/no_Image.png') }})">
                @if ($info->image_md ?? '')
                    @php
                        $catImagePath = 'productCategory/' . $info->id . '/medium/' . $info->image_md;
                        $url = Storage::url($catImagePath);
                        $path = asset($url);
                    @endphp

                    <div class="image-input-wrapper w-125px h-125px manual-image" id="medium-image"
                        style="background-image: url({{ $path }});">
                    </div>
                @else
                    <div class="image-input-wrapper w-125px h-125px manual-image" id="medium-image"
                        style="background-image: url({{ asset('userImage/no_Image.png') }});">
                    </div>
                @endif
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change Icon">
                    <i class="bi bi-pencil-fill fs-7"></i>
                    <input type="file" name="categoryImageMedium" id="mediumFile" accept=".png, .jpg, .jpeg" />
                </label>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar1">
                    <i class="bi bi-x fs-2" id="medium_remove_logo"></i>
                </span>
            </div>
        </div>

        <div class=" mb-7">
            <div class="fv-row">
                <label class="d-block fw-bold fs-6 mb-5">Image Small</label>
                <div class="form-text">
                    Allowed file types: png, jpg,
                    jpeg. ( 336 * 351 pixels)
                </div>
            </div>
            <input id="image_remove_small" type="hidden" name="image_remove_small" value="no">
            <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                style="background-image: url({{ asset('userImage/no_Image.png') }})">
                @if ($info->image_sm ?? '')
                    @php
                        $catImagePath = 'productCategory/' . $info->id . '/small/' . $info->image_sm;
                        $url = Storage::url($catImagePath);
                        $path = asset($url);
                    @endphp

                    <div class="image-input-wrapper w-125px h-125px manual-image" id="small-image"
                        style="background-image: url({{ $path }});">
                    </div>
                @else
                    <div class="image-input-wrapper w-125px h-125px manual-image" id="small-image"
                        style="background-image: url({{ asset('userImage/no_Image.png') }});">
                    </div>
                @endif
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change Icon">
                    <i class="bi bi-pencil-fill fs-7"></i>
                    <input type="file" name="categoryImageSmall" id="smallImage" accept=".png, .jpg, .jpeg" />
                </label>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar1">
                    <i class="bi bi-x fs-2" id="small_remove_logo"></i>
                </span>
            </div>
        </div>
        <div class=" mb-7">
            <div class="fv-row">
                <label class="d-block fw-bold fs-6 mb-5">Banner Image</label>
                <div class="form-text">
                    Allowed file types: png, jpg,
                    jpeg. ( 1920 * 320 pixels)
                </div>
            </div>
            <input id="image_remove_banner" type="hidden" name="image_remove_banner" value="no">
            <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                style="background-image: url({{ asset('userImage/no_Image.png') }})">
                @if ($info->category_banner ?? '')
                    @php
                        $catImagePath = 'productCategory/' . $info->id . '/banner/' . $info->category_banner;
                        $url = Storage::url($catImagePath);
                        $path = asset($url);
                    @endphp

                    <div class="image-input-wrapper w-125px h-125px manual-image" id="banner-image"
                        style="background-image: url({{ $path }});">
                    </div>
                @else
                    <div class="image-input-wrapper w-125px h-125px manual-image" id="banner-image"
                        style="background-image: url({{ asset('userImage/no_Image.png') }});">
                    </div>
                @endif
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change Icon">
                    <i class="bi bi-pencil-fill fs-7"></i>
                    <input type="file" name="category_banner" id="bannerImage" accept=".png, .jpg, .jpeg" />
                </label>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar1">
                    <i class="bi bi-x fs-2" id="banner_remove_logo"></i>
                </span>
            </div>
        </div>





    </div>
</div>
