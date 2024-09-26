<div class="card card-flush py-4">
    <div class="">
        <div class="card-title">
            <h2>Upload Documents</h2>
        </div>
    </div>
    <div class=" pt-0">
        <div class="mb-10">
            <label class="form-label">Shop Proof</label>
            <input type="file" class="form-control mb-2" name="shop_proof" placeholder="Shop Proof"  value="{{ $merchantShopData->shop_proof ?? '' }}"/>
        </div>

        <div class="mb-10">
            <label class="form-label">PAN Card</label>
            <input type="file" class="form-control mb-2" name="pan_card" placeholder="PAN Card"  value="{{ $merchantShopData->pan_card ?? '' }}"/>
        </div>
        <div class="mb-10">
            <label class="form-label">GST Certificate</label>
            <input type="file" class="form-control mb-2" name="gst_certificate" placeholder="GST Certificate"  value="{{ $merchantShopData->gst_certificate ?? '' }}"/>
        </div>
        <div class="mb-10">
            <label class="form-label">Cancelled cheque</label>
            <input type="file" class="form-control mb-2" name="cancelled_cheque" placeholder="Cancelled cheque"  value="{{ $merchantShopData->cancelled_cheque ?? '' }}"/>
        </div>
        <div class="mb-10">
            <label class="form-label">KYC document</label>
            <input type="file" class="form-control mb-2" name="kyc_document" placeholder="KYC document"  value="{{ $merchantShopData->kyc_document ?? '' }}"/>
        </div>
    </div>
</div>
