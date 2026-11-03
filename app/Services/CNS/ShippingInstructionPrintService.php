<?php

namespace App\Services\CNS;

use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Support\Facades\DB;

class ShippingInstructionPrintService
{
    /** @var array */
    private $companyCache = [];

    /** @var bool */
    private static $ffmpegChecked = false;

    /** @var string|null */
    private static $ffmpegPath;

    public function resolve(ShipmentInstruction $shipment): array
    {
        $contract = $this->resolveContract($shipment);
        $schedule = $this->resolveSchedule($shipment);
        $buyerSupplier = $contract && $contract->Supplier_ID
            ? DB::table('supplier')->where('ID', $contract->Supplier_ID)->first()
            : null;

        $shipperRow = $this->companyByCode($shipment->Shipper);
        $consigneeRow = $this->companyByCode($shipment->Consignee)
            ?: $buyerSupplier;
        $notifyRow = $this->companyByCode($shipment->Notify);
        $destinationRow = $this->companyByCode($shipment->Destination_Letter);

        $consignee = $consigneeRow
            ? $this->companyArray($consigneeRow)
            : $this->companyFromContract($contract);

        $shipper = $shipperRow
            ? $this->companyArray($shipperRow)
            : $this->tbdCompany();

        $notify = $notifyRow ? $this->companyArray($notifyRow) : $consignee;
        $destination = $destinationRow ? $this->companyArray($destinationRow) : $consignee;

        $contractQuantity = $this->contractQuantity($contract);
        $quantity = $this->first([
            $shipment->Qty,
            $contractQuantity,
            $schedule ? $schedule->Qty : null,
        ]);

        $packageType = $this->first([
            $shipment->Qty2,
            $schedule ? $schedule->Qty2 : null,
            $contract ? $contract->Container_Size : null,
        ], '');

        $isFinal = $shipment->Status === 'FINAL' && !empty($shipment->Final_SI_No);
        $siDate = $isFinal && !empty($shipment->Final_SI_Date)
            ? $shipment->Final_SI_Date
            : $shipment->SI_Date;

        return [
            'shipment' => $shipment,
            'contract' => $contract,
            'schedule' => $schedule,
            'shipper' => $shipper,
            'consignee' => $consignee,
            'notify' => $notify,
            'destination' => $destination,
            'is_final' => $isFinal,
            'values' => [
                'status_label' => $shipment->Status === 'FINAL' ? 'FINAL' : 'DRAFT',
                'si_no' => $isFinal ? $shipment->Final_SI_No : $shipment->SI_No,
                'si_date' => $this->formatDate($siDate),
                'contract_no' => $this->first([
                    $shipment->Contract_No,
                    $contract ? $contract->Contract_No : null,
                ]),
                'delivery_terms' => $this->first([
                    $shipment->Delivery_Terms,
                    $contract ? $contract->Incoterms : null,
                ]),
                'quantity' => $quantity,
                'voyage' => $this->first([
                    $shipment->Voyage,
                    $schedule ? $schedule->Vessel : null,
                ]),
                'shipped_by' => $this->first([
                    $shipment->Shipped_By,
                    $schedule ? $schedule->Vessel : null,
                ]),
                'etd' => $this->formatDate($this->first([
                    $shipment->ETD,
                    $schedule ? $schedule->ETD : null,
                ], '')),
                'eta' => $this->formatDate($this->first([
                    $shipment->ETA,
                    $schedule ? $schedule->ETA : null,
                ], '')),
                'stuffing' => $this->formatDate($this->first([
                    $shipment->Stuffing,
                    $schedule ? $schedule->Stuffing : null,
                    $contract ? $contract->Loading_Start : null,
                ], '')),
                'peb_no' => $this->first([$shipment->PEB_No]),
                'peb_date' => $this->formatDate($shipment->PEB_Date),
                'bl_no' => $this->first([$shipment->BL_No]),
                'pol' => $this->first([
                    $shipment->POL,
                    $schedule ? $schedule->POL : null,
                ]),
                'pod' => $this->first([
                    $shipment->POD,
                    $schedule ? $schedule->POD : null,
                ]),
                'product_name' => $this->first([$shipment->Product_Name]),
                'hs_code' => $this->first([$shipment->HS_Code]),
                'package_type' => $packageType,
            ],
        ];
    }

    private function resolveContract(ShipmentInstruction $shipment)
    {
        if ($shipment->Contract_ID) {
            return DB::table('cns_buyer_contracts')->where('ID', $shipment->Contract_ID)->first();
        }

        if ($shipment->Contract_No) {
            return DB::table('cns_buyer_contracts')->where('Contract_No', $shipment->Contract_No)->first();
        }

        return null;
    }

    private function resolveSchedule(ShipmentInstruction $shipment)
    {
        if ($shipment->Order_No) {
            $schedule = VesselSchedule::where('Order_No', $shipment->Order_No)->orderBy('id')->first();

            if ($schedule) {
                return $schedule;
            }
        }

        if ($shipment->SI_No) {
            $orderNo = VesselScheduleDtl::active()
                ->where('SI_No', $shipment->SI_No)
                ->whereNotNull('Order_No')
                ->value('Order_No');

            if ($orderNo) {
                return VesselSchedule::where('Order_No', $orderNo)->orderBy('id')->first();
            }
        }

        $contract = $this->resolveContract($shipment);

        if ($contract) {
            $schedules = VesselSchedule::where('Contract_ID', $contract->ID)
                ->where('Shipping_Method', 'CONTAINER')
                ->orderBy('id')
                ->get();

            if ($schedules->count() === 1) {
                return $schedules->first();
            }
        }

        return null;
    }

    public function containersFor(ShipmentInstruction $shipment)
    {
        $schedule = $this->resolveSchedule($shipment);

        if (!$schedule) {
            return collect();
        }

        $query = VesselScheduleDtl::active();

        if ($schedule->Order_No) {
            $query->where('Order_No', $schedule->Order_No);
        } else {
            $query->where('Task_ID', $schedule->Task_ID);
        }

        return $query->orderBy('ID')->get();
    }

    private function companyByCode($code)
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        if (!array_key_exists($code, $this->companyCache)) {
            $this->companyCache[$code] = DB::table('supplier')
                ->where('Supplier_ID', $code)
                ->orderBy('ID')
                ->first();
        }

        return $this->companyCache[$code];
    }

    private function companyArray($row): array
    {
        return [
            'name' => (string) ($row->Company_Name ?? 'TBD'),
            'address' => (string) ($row->Address ?? ''),
            'city' => (string) ($row->City ?? ''),
            'logo_data_uri' => $this->imageDataUri($row->Logo ?? null),
            'letterhead_data_uri' => $this->imageDataUri($row->Letterhead ?? null),
            'footer_data_uri' => $this->imageDataUri($row->Footer ?? null),
        ];
    }

    private function companyFromContract($contract): array
    {
        if (!$contract || trim((string) $contract->Buyer_Name) === '') {
            return $this->tbdCompany();
        }

        return [
            'name' => (string) $contract->Buyer_Name,
            'address' => (string) ($contract->Buyer_Address ?? ''),
            'city' => '',
            'logo_data_uri' => null,
            'letterhead_data_uri' => null,
            'footer_data_uri' => null,
        ];
    }

    private function tbdCompany(): array
    {
        return [
            'name' => 'TBD',
            'address' => '',
            'city' => '',
            'logo_data_uri' => null,
            'letterhead_data_uri' => null,
            'footer_data_uri' => null,
        ];
    }

    private function first(array $values, string $fallback = 'TBD'): string
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return $fallback;
    }

    private function formatDate($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return 'TBD';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('F, d Y', $timestamp) : 'TBD';
    }

    private function contractQuantity($contract): ?string
    {
        if (!$contract || (float) $contract->Quantity_MT <= 0) {
            return null;
        }

        $quantity = number_format((float) $contract->Quantity_MT, 3, '.', '');
        $display = $quantity . ' MT';

        if ((int) $contract->Container_Count > 0 && $contract->Container_Size) {
            $display .= ' (' . (int) $contract->Container_Count . ' x ' . $contract->Container_Size . ')';
        }

        return $display;
    }

    private function imageDataUri($path): ?string
    {
        $file = $this->publicFile($path);

        if (!$file) {
            return null;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            $binary = $this->normalizePng($file);

            if ($binary !== null) {
                return 'data:image/png;base64,' . base64_encode($binary);
            }
        }

        $mime = $extension === 'jpg' ? 'jpeg' : $extension;

        return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    private function normalizePng(string $file): ?string
    {
        if (class_exists('\\Imagick')) {
            try {
                $image = new \Imagick($file);
                $image->setImageFormat('png');
                $image->setInterlaceScheme(\Imagick::INTERLACE_NO);
                $binary = $image->getImageBlob();
                $image->clear();
                $image->destroy();

                return $binary;
            } catch (\Throwable $e) {
                // Fall through to ffmpeg/GD.
            }
        }

        $ffmpeg = $this->ffmpegPath();

        if ($ffmpeg) {
            $output = sys_get_temp_dir() . '/cns_png_' . uniqid('', true) . '.png';
            $command = escapeshellarg($ffmpeg)
                . ' -hide_banner -loglevel error -y -i ' . escapeshellarg($file)
                . ' -frames:v 1 -pix_fmt rgba ' . escapeshellarg($output) . ' 2>/dev/null';

            @exec($command, $ignored, $exitCode);

            if ($exitCode === 0 && is_file($output)) {
                $binary = file_get_contents($output);
                @unlink($output);

                if ($binary !== false) {
                    return $binary;
                }
            }

            @unlink($output);
        }

        $image = @imagecreatefrompng($file);

        if ($image === false) {
            return null;
        }

        imageinterlace($image, false);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return $binary === false ? null : $binary;
    }

    private function ffmpegPath(): ?string
    {
        if (!self::$ffmpegChecked) {
            self::$ffmpegChecked = true;
            self::$ffmpegPath = trim((string) @shell_exec('command -v ffmpeg 2>/dev/null')) ?: null;
        }

        return self::$ffmpegPath;
    }

    private function publicFile($path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return null;
        }

        $fullPath = public_path(ltrim($path, '/'));

        return is_file($fullPath) ? $fullPath : null;
    }
}
