@php
    $letter = $letter ?? null;
    $defaults = $defaults ?? [];
    $isReadOnly = isset($letter) && $letter->status === \App\CnsOfferingLetter::STATUS_APPROVED;
    $ro = $isReadOnly ? 'disabled' : '';
    $val = function ($field, $fallback = '') use ($letter, $defaults) {
        if (old($field) !== null) return old($field);
        if (isset($letter)) return $letter->{$field};
        return $defaults[$field] ?? $fallback;
    };
    $offerDateVal = old('offer_date', isset($letter) && $letter->offer_date ? $letter->offer_date->format('Y-m-d') : ($offerDate ?? date('Y-m-d')));
    $selectedSellerId = (string) old('seller_id', isset($letter) ? ($letter->seller_id ?? '') : '');
    $selectedSellerCode = (string) old('seller_code', isset($letter) ? ($letter->seller_code ?? '') : '');
    $currentPlaceOfIssue = isset($letter) && !empty($letter->place_of_issue)
        ? $letter->place_of_issue
        : ($defaultPlace ?? '-');
    $deliveryTermVal = old('delivery_term', isset($letter) ? $letter->delivery_term : '');
    $hsCodeVal = $val('hs_code', '');
    $hsOptions = $hsCodes ?? ['15119020', '15180090'];
    if ($hsCodeVal && !in_array($hsCodeVal, $hsOptions, true)) {
        $hsOptions[] = $hsCodeVal;
    }
    $qtyRaw = $val('quantity_mt');
    $qtyVal = $qtyRaw !== '' && $qtyRaw !== null ? number_format((float) str_replace(',', '', (string) $qtyRaw), 2, '.', ',') : '';
    $priceRaw = $val('price_per_mt');
    $priceVal = $priceRaw !== '' && $priceRaw !== null ? number_format((float) str_replace(',', '', (string) $priceRaw), 2, '.', ',') : '';
    $loadingPeriodRange = \App\CnsOfferingLetter::parseLoadingPeriodRange(isset($letter) ? $letter->loading_period : ($defaults['loading_period'] ?? ''));
    $loadingPeriodStartVal = old('loading_period_start', $loadingPeriodRange['start']);
    $loadingPeriodEndVal = old('loading_period_end', $loadingPeriodRange['end']);
    $signatoryKeyVal = (string) old('signatory_key', isset($letter) ? $letter->signatory_key : '');
    $signatoryTitleVal = old('signatory_title', isset($letter) ? $letter->signatory_title : '');
@endphp

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terdapat kesalahan pengisian data:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="row">
    <div class="col-md-4 form-group">
        <label>No. Offering Letter</label>
        <input type="text" id="offer_no_preview" class="text-black form-control" value="{{ isset($letter) ? $letter->offer_no : $previewNo }}" readonly>
    </div>
    <div class="col-md-4 form-group">
        <label>Tanggal Offer <span class="text-danger">*</span></label>
        <input type="date" name="offer_date" id="offer_date" class="text-black form-control @error('offer_date') is-invalid @enderror" value="{{ $offerDateVal }}" {{ $ro }} required>
        <div class="invalid-feedback" id="err_offer_date">@error('offer_date') {{ $message }} @else Tanggal offer wajib diisi. @enderror</div>
    </div>
    <div class="col-md-4 form-group">
        <label>Lokasi Penerbitan</label>
        <input type="text" id="place_of_issue_preview" class="text-black form-control" value="{{ $currentPlaceOfIssue }}" readonly>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label>Seller <span class="text-danger">*</span></label>
        <select name="seller_id" id="seller_id" class="text-black form-control @error('seller_id') is-invalid @enderror" {{ $ro }} required>
            <option value="">-- Pilih Seller --</option>
            @foreach($sellers as $seller)
                @php
                    $isSelected = ($selectedSellerId !== '' && (string)$selectedSellerId === (string)$seller->id)
                        || ($selectedSellerId === '' && $selectedSellerCode !== '' && $selectedSellerCode === $seller->code);
                @endphp
                <option value="{{ $seller->id }}" data-code="{{ $seller->code }}" data-city="{{ $seller->place_of_issue }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $seller->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err_seller_id">@error('seller_id') {{ $message }} @else Seller wajib dipilih. @enderror</div>
    </div>
    <div class="col-md-6 form-group">
        <label>Buyer (Master Consignee) <span class="text-danger">*</span></label>
        <select name="buyer_id" id="buyer_id" class="text-black form-control @error('buyer_id') is-invalid @enderror" {{ $ro }} required>
            <option value="">-- Pilih Buyer --</option>
            @foreach($buyers as $buyer)
                <option value="{{ $buyer->ID }}" {{ (string)$val('buyer_id') === (string)$buyer->ID ? 'selected' : '' }}>
                    {{ $buyer->Company_Name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err_buyer_id">@error('buyer_id') {{ $message }} @else Buyer wajib dipilih. @enderror</div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold mb-3">Detail Penawaran</h6>

<div class="row">
    <div class="col-md-6 form-group">
        <label>Product <span class="text-danger">*</span></label>
        <input type="text" name="product" id="product" class="text-black form-control @error('product') is-invalid @enderror" value="{{ $val('product') }}" {{ $ro }} required>
        <div class="invalid-feedback" id="err_product">@error('product') {{ $message }} @else Product wajib diisi. @enderror</div>
    </div>
    <div class="col-md-3 form-group">
        <label>HS Code</label>
        <select name="hs_code" id="hs_code" class="text-black form-control" {{ $ro }}>
            <option value="">-- Pilih HS Code --</option>
            @foreach($hsOptions as $code)
                <option value="{{ $code }}" {{ (string) $hsCodeVal === (string) $code ? 'selected' : '' }}>{{ $code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 form-group">
        <label>Certificate</label>
        <input type="text" name="certificate" class="text-black form-control" value="{{ $val('certificate') }}" {{ $ro }}>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header py-2 font-weight-bold">Guaranteed Specification</div>
    <div class="card-body py-2">
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Free Fatty Acids (ISO660)</label>
                <input type="text" name="spec_ffa" class="text-black form-control" value="{{ $val('spec_ffa') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>MnI (ASTM D 6304-C; ISO 663)</label>
                <input type="text" name="spec_mni" class="text-black form-control" value="{{ $val('spec_mni') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Unsaponifiable</label>
                <input type="text" name="spec_unsaponifiable" class="text-black form-control" value="{{ $val('spec_unsaponifiable') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Iodine Value (AOCS Cd_Id_92)</label>
                <input type="text" name="spec_iodine_value" class="text-black form-control" value="{{ $val('spec_iodine_value') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Sulphur (ASTM D 5453)</label>
                <input type="text" name="spec_sulphur" class="text-black form-control" value="{{ $val('spec_sulphur') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Total Metals</label>
                <input type="text" name="spec_total_metals" class="text-black form-control" value="{{ $val('spec_total_metals') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Phosphorus</label>
                <input type="text" name="spec_phosphorus" class="text-black form-control" value="{{ $val('spec_phosphorus') }}" {{ $ro }}>
            </div>
            <div class="col-md-3 form-group">
                <label>Total Chloride</label>
                <input type="text" name="spec_total_chloride" class="text-black form-control" value="{{ $val('spec_total_chloride') }}" {{ $ro }}>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label>Quantity (MT) <span class="text-danger">*</span></label>
        <input type="text" inputmode="decimal" name="quantity_mt" id="quantity_mt" class="text-black form-control cns-decimal @error('quantity_mt') is-invalid @enderror" value="{{ $qtyVal }}" placeholder="0.00" {{ $ro }} required>
        <div class="invalid-feedback" id="err_quantity_mt">@error('quantity_mt') {{ $message }} @else Quantity (MT) wajib diisi. @enderror</div>
    </div>
    <div class="col-md-4 form-group">
        <label>Keterangan Quantity</label>
        <input type="text" name="quantity_note" class="text-black form-control @error('quantity_note') is-invalid @enderror" value="{{ $val('quantity_note') }}" {{ $ro }}>
        @error('quantity_note')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 form-group">
        <label>Currency</label>
        <select name="price_currency" class="text-black form-control @error('price_currency') is-invalid @enderror" {{ $ro }}>
            @foreach(['USD', 'EUR', 'SGD', 'IDR'] as $curr)
                <option value="{{ $curr }}" {{ $val('price_currency', 'USD') === $curr ? 'selected' : '' }}>{{ $curr }}</option>
            @endforeach
        </select>
        @error('price_currency')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 form-group">
        <label>Price / MT <span class="text-danger">*</span></label>
        <input type="text" inputmode="decimal" name="price_per_mt" id="price_per_mt" class="text-black form-control cns-decimal @error('price_per_mt') is-invalid @enderror" value="{{ $priceVal }}" placeholder="0.00" {{ $ro }} required>
        <div class="invalid-feedback" id="err_price_per_mt">@error('price_per_mt') {{ $message }} @else Price / MT wajib diisi. @enderror</div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label>Loading Period (Start) <span class="text-danger">*</span></label>
        <input type="date" name="loading_period_start" id="loading_period_start" class="text-black form-control @error('loading_period_start') is-invalid @enderror" value="{{ $loadingPeriodStartVal }}" {{ $ro }} required>
        <div class="invalid-feedback" id="err_loading_period_start">@error('loading_period_start') {{ $message }} @else Loading Period (Start) wajib diisi. @enderror</div>
    </div>
    <div class="col-md-3 form-group">
        <label>Loading Period (End) <span class="text-danger">*</span></label>
        <input type="date" name="loading_period_end" id="loading_period_end" class="text-black form-control @error('loading_period_end') is-invalid @enderror" value="{{ $loadingPeriodEndVal }}" {{ $ro }} required>
        <div class="invalid-feedback" id="err_loading_period_end">@error('loading_period_end') {{ $message }} @else Loading Period (End) wajib diisi. @enderror</div>
    </div>
    <div class="col-md-3 form-group">
        <label>Delivery Term <span class="text-danger">*</span></label>
        <select name="delivery_term" id="delivery_term" class="text-black form-control @error('delivery_term') is-invalid @enderror" {{ $ro }} required>
            <option value="">-- Pilih Delivery Term --</option>
            <option value="FOB Surabaya" {{ $deliveryTermVal === 'FOB Surabaya' ? 'selected' : '' }}>FOB Surabaya</option>
            <option value="CIF" {{ $deliveryTermVal === 'CIF' ? 'selected' : '' }}>CIF + Destination Port</option>
        </select>
        <div class="invalid-feedback" id="err_delivery_term">@error('delivery_term') {{ $message }} @else Delivery Term wajib dipilih. @enderror</div>
    </div>
    <div class="col-md-3 form-group" id="destination_port_group" style="{{ $deliveryTermVal === 'CIF' ? '' : 'display:none;' }}">
        <label>Destination Port (CIF) <span class="text-danger">*</span></label>
        <input type="text" name="destination_port" id="destination_port" class="text-black form-control @error('destination_port') is-invalid @enderror" placeholder="Contoh: Rotterdam" value="{{ $val('destination_port') }}" {{ $ro }}>
        <div class="invalid-feedback" id="err_destination_port">@error('destination_port') {{ $message }} @else Destination Port wajib diisi untuk delivery CIF. @enderror</div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label>Packaging</label>
        <input type="text" name="packaging" class="text-black form-control" value="{{ $val('packaging') }}" {{ $ro }}>
    </div>
    <div class="col-md-6 form-group">
        <label>Load Port Independent Inspector</label>
        <input type="text" name="load_port_inspector" class="text-black form-control" value="{{ $val('load_port_inspector') }}" {{ $ro }}>
    </div>
</div>

<div class="form-group">
    <label>Final Quantity and Quality</label>
    <textarea name="final_qty_quality" rows="2" class="text-black form-control" {{ $ro }}>{{ $val('final_qty_quality') }}</textarea>
</div>

<div class="form-group">
    <label>Payment Terms</label>
    <textarea name="payment_terms" rows="5" class="text-black form-control" {{ $ro }}>{{ $val('payment_terms') }}</textarea>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label>Penandatangan <span class="text-danger">*</span></label>
        <select name="signatory_key" id="signatory_key" class="text-black form-control @error('signatory_key') is-invalid @enderror" {{ $ro }} required>
            <option value="">-- Pilih Penandatangan --</option>
            @foreach($signatoryUsers as $sigUser)
                <option value="{{ $sigUser->id }}" data-position="{{ $sigUser->Position ?? '' }}" {{ $signatoryKeyVal === (string) $sigUser->id ? 'selected' : '' }}>
                    {{ $sigUser->Name }}{{ $sigUser->Position ? ' (' . $sigUser->Position . ')' : '' }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err_signatory_key">@error('signatory_key') {{ $message }} @else Penandatangan wajib dipilih. @enderror</div>
    </div>
    <div class="col-md-4 form-group">
        <label>Jabatan Penandatangan</label>
        <input type="text" name="signatory_title" id="signatory_title" class="text-black form-control @error('signatory_title') is-invalid @enderror" value="{{ $signatoryTitleVal }}" {{ $ro }}>
        @error('signatory_title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 form-group">
        <label>Offer Validity</label>
        <input type="text" class="text-black form-control" value="{{ isset($letter) && $letter->valid_until ? $letter->valid_until->format('d F Y') . ' (+14 hari kalender dari approve)' : 'Otomatis +14 hari kalender setelah Approved' }}" readonly>
    </div>
</div>

@if(!$isReadOnly)
<style>
    .is-invalid + .select2-container .select2-selection {
        border-color: #dc3545 !important;
    }
</style>
<script>
    $(document).ready(function () {
        var form = $('#offer_date').closest('form');
        form.attr('novalidate', 'novalidate');

        function refreshNumber() {
            var selected = $('#seller_id').find(':selected');
            var sellerId = $('#seller_id').val();
            var city = selected.data('city') || '-';
            $('#place_of_issue_preview').val(city);

            $.get("{{ route('cns-offering-letter.next-number') }}", {
                seller_id: sellerId,
                seller_code: selected.data('code'),
                offer_date: $('#offer_date').val()
            }, function (res) {
                if (res && res.offer_no) {
                    $('#offer_no_preview').val(res.offer_no);
                    if (res.place_of_issue) {
                        $('#place_of_issue_preview').val(res.place_of_issue);
                    }
                }
            }).fail(function (xhr) {
                console.warn('Gagal memuat nomor offer terbaru', xhr.responseJSON || xhr.responseText);
            });
        }

        function toggleDestinationPort() {
            if ($('#delivery_term').val() === 'CIF') {
                $('#destination_port_group').show();
                $('#destination_port').prop('required', true);
            } else {
                $('#destination_port_group').hide();
                $('#destination_port').prop('required', false);
                $('#destination_port').removeClass('is-invalid');
                $('#err_destination_port').hide();
            }
        }

        if ($.fn.select2) {
            $('#hs_code').select2({ width: '100%', tags: true });
            $('#signatory_key').select2({ width: '100%', placeholder: '-- Pilih Penandatangan --' });
        }

        $('#seller_id, #offer_date').on('change', refreshNumber);
        $('#delivery_term').on('change', toggleDestinationPort);
        $('#signatory_key').on('change', function () {
            var position = $(this).find(':selected').data('position');
            $('#signatory_title').val(position || '');
            if ($(this).val()) {
                $(this).removeClass('is-invalid');
                $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid').css('border-color', '');
                $('#err_signatory_key').hide();
            }
        });

        // Real-time clearance saat user mengisi field
        $('#offer_date, #seller_id, #buyer_id, #product, #quantity_mt, #price_per_mt, #loading_period_start, #loading_period_end, #delivery_term, #destination_port, #signatory_key').on('input change', function () {
            var el = $(this);
            var val = el.val();
            if (val && $.trim(val)) {
                el.removeClass('is-invalid');
                if (el.attr('id') === 'signatory_key') {
                    el.next('.select2-container').find('.select2-selection').removeClass('is-invalid').css('border-color', '');
                }
                el.closest('.form-group').find('.invalid-feedback').hide();
                if ($('.is-invalid').length === 0) {
                    $('#clientValidationAlert').fadeOut(200, function () { $(this).remove(); });
                }
            }
        });

        // Validasi client-side instan tanpa reload halaman
        form.on('submit', function (e) {
            var isValid = true;
            var firstInvalid = null;

            var validations = [
                { id: '#offer_date', errId: '#err_offer_date', msg: 'Tanggal Offer wajib diisi.' },
                { id: '#seller_id', errId: '#err_seller_id', msg: 'Seller wajib dipilih.' },
                { id: '#buyer_id', errId: '#err_buyer_id', msg: 'Buyer wajib dipilih.' },
                { id: '#product', errId: '#err_product', msg: 'Product wajib diisi.' },
                { id: '#quantity_mt', errId: '#err_quantity_mt', msg: 'Quantity (MT) wajib diisi.' },
                { id: '#price_per_mt', errId: '#err_price_per_mt', msg: 'Price / MT wajib diisi.' },
                { id: '#loading_period_start', errId: '#err_loading_period_start', msg: 'Loading Period (Start) wajib diisi.' },
                { id: '#loading_period_end', errId: '#err_loading_period_end', msg: 'Loading Period (End) wajib diisi.' },
                { id: '#delivery_term', errId: '#err_delivery_term', msg: 'Delivery Term wajib dipilih.' },
                { id: '#signatory_key', errId: '#err_signatory_key', msg: 'Penandatangan wajib dipilih.' }
            ];

            if ($('#delivery_term').val() === 'CIF') {
                validations.push({
                    id: '#destination_port',
                    errId: '#err_destination_port',
                    msg: 'Destination Port wajib diisi untuk delivery CIF.'
                });
            }

            validations.forEach(function (item) {
                var el = $(item.id);
                var val = el.val();
                if (!val || !$.trim(val)) {
                    isValid = false;
                    el.addClass('is-invalid');
                    if (item.id === '#signatory_key') {
                        el.next('.select2-container').find('.select2-selection').addClass('is-invalid').css('border-color', '#dc3545');
                    }
                    $(item.errId).text(item.msg).show();
                    if (!firstInvalid) {
                        firstInvalid = el;
                    }
                } else {
                    el.removeClass('is-invalid');
                    if (item.id === '#signatory_key') {
                        el.next('.select2-container').find('.select2-selection').removeClass('is-invalid').css('border-color', '');
                    }
                    $(item.errId).hide();
                }
            });

            var startDate = $('#loading_period_start').val();
            var endDate = $('#loading_period_end').val();
            if (startDate && endDate && endDate < startDate) {
                isValid = false;
                $('#loading_period_end').addClass('is-invalid');
                $('#err_loading_period_end').text('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.').show();
                if (!firstInvalid) {
                    firstInvalid = $('#loading_period_end');
                }
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();

                $('#clientValidationAlert').remove();
                var alertHtml = '<div id="clientValidationAlert" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">' +
                    '<strong>Validasi Gagal:</strong> Mohon lengkapi seluruh field wajib yang ditandai merah.' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>';
                form.prepend(alertHtml);

                if (firstInvalid && firstInvalid.length) {
                    firstInvalid.focus();
                    $('html, body').animate({
                        scrollTop: firstInvalid.offset().top - 120
                    }, 200);
                }
                return false;
            }

            $('#clientValidationAlert').remove();
            return true;
        });

        toggleDestinationPort();
    });
</script>
@endif
