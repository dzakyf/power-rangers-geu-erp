@php
    $isEdit = $isEdit ?? false;
    $method = $vs->Shipping_Method ?? '';
    $isContainer = $method === 'CONTAINER';
    $isVessel = $method === 'VESSEL';
    $isTruck = $method === 'TANK_TRUCK';
    $dateValue = function ($value) {
        return $value ? date('Y-m-d', strtotime($value)) : '';
    };
    $dateTimeValue = function ($value) {
        return $value ? date('Y-m-d\TH:i', strtotime($value)) : '';
    };
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">{{ $isEdit ? 'VESSEL SCHEDULE' : 'NEW VESSEL SCHEDULE' }}</div>
    <div class="card">
        <div class="card-body">
            <form id="VesselScheduleForm">
                @csrf
                @if($isEdit)
                    <input type="hidden" name="id" id="id" value="{{ $vs->id }}" />
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Contract_ID">Pilih Kontrak</label>
                            <select name="Contract_ID" id="Contract_ID" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Kontrak --</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->ID }}"
                                            data-incoterms="{{ $contract->Incoterms }}"
                                            data-container-count="{{ $contract->Container_Count }}"
                                            data-container-size="{{ $contract->Container_Size }}"
                                            {{ (string) ($vs->Contract_ID ?? '') === (string) $contract->ID ? 'selected' : '' }}>
                                        {{ $contract->Contract_No }} - {{ $contract->Buyer_Name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya kontrak dengan status Active yang ditampilkan.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Incoterms">Incoterms</label>
                            <input type="text" id="Incoterms" class="form-control shadow-sm" value="{{ $vs->Incoterms ?? '' }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Shipping_Method">Shipping Method</label>
                            <select name="Shipping_Method" id="Shipping_Method" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="CONTAINER" {{ $isContainer ? 'selected' : '' }}>Container</option>
                                <option value="VESSEL" {{ $isVessel ? 'selected' : '' }}>Vessel</option>
                                <option value="TANK_TRUCK" {{ $isTruck ? 'selected' : '' }}>Tank Truck</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="ContainerSection" style="display: {{ $isContainer ? 'block' : 'none' }};">
                    <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Container</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Container_Booking_ID">Nomor Booking Confirmation</label>
                                <select name="Container_Booking_ID" id="Container_Booking_ID" class="form-control shadow-sm">
                                    <option value="">-- Pilih Booking --</option>
                                    @foreach($bookings as $booking)
                                        <option value="{{ $booking->ID }}"
                                                data-file="{{ $booking->Booking_File ? asset($booking->Booking_File) : '' }}"
                                                data-file-name="{{ $booking->Booking_File_Name }}"
                                                {{ (string) ($vs->Booking_ID ?? '') === (string) $booking->ID ? 'selected' : '' }}>
                                            {{ $booking->Label }} - {{ $booking->Booking_No }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="BookingFileLink" class="mt-1"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_Qty1">Quantity</label>
                                <input type="text" inputmode="decimal" name="Container_Qty1" id="Container_Qty1" class="form-control shadow-sm cns-decimal" value="{{ $isContainer && ($vs->Qty1 ?? '') !== '' ? number_format((float) $vs->Qty1, 2, '.', ',') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_Qty2">Type &amp; Size</label>
                                <select name="Container_Qty2" id="Container_Qty2" class="form-control shadow-sm">
                                    <option value="">-- Pilih Type & Size --</option>
                                    @foreach(['20 FT', '40 FT', '20 ISOTANK'] as $size)
                                        <option value="{{ $size }}" {{ $isContainer && (string) ($vs->Qty2 ?? '') === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Container_Vessel">Vessel</label>
                                <input type="text" name="Container_Vessel" id="Container_Vessel" class="form-control shadow-sm" value="{{ $isContainer ? ($vs->Vessel ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Container_POL">PoL</label>
                                <input type="text" name="Container_POL" id="Container_POL" class="form-control shadow-sm" value="{{ $isContainer ? ($vs->POL ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Container_POD">PoD</label>
                                <input type="text" name="Container_POD" id="Container_POD" class="form-control shadow-sm" value="{{ $isContainer ? ($vs->POD ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_Stuffing">Stuffing Date</label>
                                <input type="date" name="Container_Stuffing" id="Container_Stuffing" class="form-control shadow-sm" value="{{ $isContainer ? $dateValue($vs->Stuffing) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_Closing_CY">Closing CY</label>
                                <input type="date" name="Container_Closing_CY" id="Container_Closing_CY" class="form-control shadow-sm" value="{{ $isContainer ? $dateValue($vs->Closing_CY) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_ETD">ETD</label>
                                <input type="date" name="Container_ETD" id="Container_ETD" class="form-control shadow-sm" value="{{ $isContainer ? $dateValue($vs->ETD) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Container_ETA">ETA</label>
                                <input type="date" name="Container_ETA" id="Container_ETA" class="form-control shadow-sm" value="{{ $isContainer ? $dateValue($vs->ETA) : '' }}" />
                            </div>
                        </div>
                    </div>
                </div>

                <div id="VesselSection" style="display: {{ $isVessel ? 'block' : 'none' }};">
                    <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Vessel</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_Qty1">Quantity (MT)</label>
                                <input type="text" inputmode="decimal" name="Vessel_Qty1" id="Vessel_Qty1" class="form-control shadow-sm cns-decimal" value="{{ $isVessel && ($vs->Qty1 ?? '') !== '' ? number_format((float) $vs->Qty1, 2, '.', ',') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_POL">PoL</label>
                                <input type="text" name="Vessel_POL" id="Vessel_POL" class="form-control shadow-sm" value="{{ $isVessel ? ($vs->POL ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_POD">PoD</label>
                                <input type="text" name="Vessel_POD" id="Vessel_POD" class="form-control shadow-sm" value="{{ $isVessel ? ($vs->POD ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_Name">Nama Kapal</label>
                                <input type="text" name="Vessel_Name" id="Vessel_Name" class="form-control shadow-sm" value="{{ $isVessel ? ($vs->Vessel ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_Laycan_Start">Laycan Start</label>
                                <input type="date" name="Vessel_Laycan_Start" id="Vessel_Laycan_Start" class="form-control shadow-sm" value="{{ $isVessel ? $dateValue($vs->Laycan_Start) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_Laycan_End">Laycan End</label>
                                <input type="date" name="Vessel_Laycan_End" id="Vessel_Laycan_End" class="form-control shadow-sm" value="{{ $isVessel ? $dateValue($vs->Laycan_End) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_ETD">ETD</label>
                                <input type="date" name="Vessel_ETD" id="Vessel_ETD" class="form-control shadow-sm" value="{{ $isVessel ? $dateValue($vs->ETD) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Vessel_ETA">ETA</label>
                                <input type="date" name="Vessel_ETA" id="Vessel_ETA" class="form-control shadow-sm" value="{{ $isVessel ? $dateValue($vs->ETA) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Q88_File">Upload File Q88 (PDF)</label>
                                <input type="file" name="Q88_File" id="Q88_File" class="form-control-file" accept=".pdf" />
                                @if($isVessel && $q88)
                                    <a href="{{ asset('filevendor/' . $q88->Path) }}" target="_blank" class="d-inline-block mt-1">
                                        Lihat Q88 saat ini{{ $q88->Filename ? ' (' . $q88->Filename . ')' : '' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div id="TruckSection" style="display: {{ $isTruck ? 'block' : 'none' }};">
                    <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Tank Truck</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Truck_Supplier_ID">Truck / Vendor</label>
                                <select name="Truck_Supplier_ID" id="Truck_Supplier_ID" class="form-control shadow-sm">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}" {{ (string) ($vs->Truck_Supplier_ID ?? '') === (string) $vendor->ID ? 'selected' : '' }}>
                                            {{ $vendor->Company_Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Truck_Schedule">Schedule (optional)</label>
                                <input type="datetime-local" name="Truck_Schedule" id="Truck_Schedule" class="form-control shadow-sm" value="{{ $isTruck ? $dateTimeValue($vs->Truck_Schedule) : '' }}" />
                                <small class="text-muted">Kosongkan jika masih menunggu informasi Buyer.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Truck_Qty1">Quantity</label>
                                <input type="text" inputmode="decimal" name="Truck_Qty1" id="Truck_Qty1" class="form-control shadow-sm cns-decimal" value="{{ $isTruck && ($vs->Qty1 ?? '') !== '' ? number_format((float) $vs->Qty1, 2, '.', ',') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Truck_Destination">Destination</label>
                                <input type="text" name="Truck_Destination" id="Truck_Destination" class="form-control shadow-sm" value="{{ $isTruck ? ($vs->Truck_Destination ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Truck_Shipment_Data">Shipment Data / Note</label>
                                <textarea name="Truck_Shipment_Data" id="Truck_Shipment_Data" rows="2" class="form-control shadow-sm">{{ $isTruck ? ($vs->Truck_Shipment_Data ?? '') : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-success mr-2" id="SubmitVesselSchedule">Submit Data</button>
                    <button type="button" class="btn btn-secondary" onclick="$('#divlistOpportunity').html('');">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
