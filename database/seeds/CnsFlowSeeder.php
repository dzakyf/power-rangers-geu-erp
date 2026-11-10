<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed one complete CNS flow:
 * Kontrak Pembelian -> Booking -> Vessel Schedule -> Draft SI -> Stuffing.
 *
 * This seeder is standalone and idempotent. It creates one prerequisite buyer
 * supplier, but does not seed master-company data generally. It also does not
 * create stuffing-monitoring notes/photos.
 */
class CnsFlowSeeder extends Seeder
{
    public function run()
    {
        $admin = DB::table('users')->where('username', 'admin')->first()
            ?: DB::table('users')->orderBy('id')->first();

        if (!$admin) {
            throw new RuntimeException('CnsFlowSeeder requires at least one user. Run UserSeeder first.');
        }

        $location = $admin->Location ?: 'GEU-HO';
        $actor = $admin->username ?: 'system';
        $today = now()->startOfDay();

        DB::transaction(function () use ($location, $actor, $today) {
            $supplier = $this->seedSupplier($location, $actor);
            $contract = $this->seedContract($supplier, $location, $actor, $today);
            $booking = $this->seedBooking($contract, $actor);
            $schedule = $this->seedVesselSchedule($contract, $booking, $actor, $today);
            $si = $this->seedDraftSi($contract, $supplier, $actor, $today);

            $this->seedContainers($schedule, $si, $actor, $today);
        });

        $this->command->info('CnsFlowSeeder: contract, booking, vessel schedule, DRAFT SI, and 2 stuffing containers seeded.');
    }

    private function seedSupplier(string $location, string $actor)
    {
        $supplierCode = 'SEED-BUYER-001';

        DB::table('supplier')->updateOrInsert(
            ['Location' => $location, 'Supplier_ID' => $supplierCode],
            [
                'Company_Name' => 'SEED Buyer Company',
                'Contact_Person' => 'Seed Buyer',
                'Address' => 'Seed Buyer Address',
                'City' => 'Jakarta',
                'Phone' => '021000000',
                'Email' => 'seed.buyer@example.test',
                'Emails' => json_encode(['seed.buyer@example.test']),
                'Status' => 'NORMAL',
                'Is_Consignee' => 1,
                'Is_Company' => 1,
                'Is_Supplier' => 0,
                'Created_By' => $actor,
                'Created_Date' => now(),
            ]
        );

        return DB::table('supplier')
            ->where('Location', $location)
            ->where('Supplier_ID', $supplierCode)
            ->first();
    }

    private function seedContract($supplier, string $location, string $actor, $today)
    {
        $contractNo = 'SEED/CTR/001';

        DB::table('cns_buyer_contracts')->updateOrInsert(
            ['Contract_No' => $contractNo],
            [
                'Supplier_ID' => $supplier->ID,
                'Buyer_Name' => 'SEED Buyer Company',
                'Buyer_Address' => 'Seed Buyer Address',
                'Buyer_Phone' => '021000000',
                'Buyer_Emails' => json_encode(['seed.buyer@example.test']),
                'Quantity_MT' => 36,
                'Container_Count' => 2,
                'Container_Size' => '20 ft',
                'Loading_Start' => $today->toDateString(),
                'Loading_End' => $today->copy()->addDays(5)->toDateString(),
                'Incoterms' => 'FOB',
                'Price_Currency' => 'USD',
                'Price_Per_MT' => 1000,
                'Status' => 'ACTIVE_IN_PROGRESS',
                'Contract_File' => null,
                'Contract_File_Name' => null,
                'Note' => 'Seeded end-to-end CNS flow contract.',
                'Location' => $location,
                'Created_By' => $actor,
                'Created_Date' => now(),
            ]
        );

        return DB::table('cns_buyer_contracts')->where('Contract_No', $contractNo)->first();
    }

    private function seedBooking($contract, string $actor)
    {
        DB::table('cns_buyer_contract_bookings')->updateOrInsert(
            ['Contract_ID' => $contract->ID, 'Label' => 'BC-1'],
            [
                'Booking_No' => 'SEED-BK-001',
                'Booking_File' => null,
                'Booking_File_Name' => null,
                'Created_By' => $actor,
                'Created_Date' => now(),
            ]
        );

        return DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $contract->ID)
            ->where('Label', 'BC-1')
            ->first();
    }

    private function seedVesselSchedule($contract, $booking, string $actor, $today)
    {
        $taskId = 'VS/SEED/001';
        $orderNo = 'SEED-BK-001';

        DB::table('vessel_schedules')->updateOrInsert(
            ['Task_ID' => $taskId],
            [
                'Order_No' => $orderNo,
                'Contract_ID' => $contract->ID,
                'Contract_No' => $contract->Contract_No,
                'Incoterms' => $contract->Incoterms,
                'Shipping_Method' => 'CONTAINER',
                'Booking_ID' => $booking->ID,
                'Booking_No' => $booking->Booking_No,
                'Qty1' => '2',
                'Qty2' => '20 FT',
                'Qty' => '2 x 20 FT',
                'Vessel' => 'SEED VESSEL',
                'POL' => 'SEED POL',
                'POD' => 'SEED POD',
                'Stuffing' => $today->copy()->addDays(7)->toDateString(),
                'Checking_BC' => $today->copy()->addDays(8)->toDateString(),
                'Closing_CY' => $today->copy()->addDays(9)->toDateString(),
                'ETD' => $today->copy()->addDays(10)->toDateString(),
                'ETA' => $today->copy()->addDays(17)->toDateString(),
                'Note' => 'Seeded end-to-end CNS flow vessel schedule.',
                'Created_By' => $actor,
                'Created_Date' => now(),
                'Updated_By' => $actor,
                'Updated_Date' => now(),
            ]
        );

        return DB::table('vessel_schedules')->where('Task_ID', $taskId)->first();
    }

    private function seedDraftSi($contract, $supplier, string $actor, $today)
    {
        $siNo = 'SI/SEED/001';

        DB::table('shipment_instructions')->updateOrInsert(
            ['SI_No' => $siNo],
            [
                'Order_No' => null,
                'SI_Date' => $today->toDateString(),
                'Qty' => '',
                'Qty1' => '',
                'Qty2' => '',
                'Shipper' => $supplier->Supplier_ID,
                'Consignee' => $supplier->Supplier_ID,
                'Notify' => $supplier->Supplier_ID,
                'Destination_Port' => $supplier->Supplier_ID,
                'Delivery_Terms' => $contract->Incoterms,
                'Contract_No' => $contract->Contract_No,
                'Contract_ID' => $contract->ID,
                'Shipped_By' => '',
                'Voyage' => '',
                'ETD' => null,
                'ETA' => null,
                'Stuffing' => null,
                'PEB_No' => '',
                'PEB_Date' => null,
                'BL_No' => '',
                'POL' => '',
                'POD' => '',
                'HS_Code' => '15119020',
                'Status' => 'DRAFT',
                'Product_Name' => 'SEED Product',
                'Created_By' => $actor,
                'Created_Date' => now(),
            ]
        );

        return DB::table('shipment_instructions')->where('SI_No', $siNo)->first();
    }

    private function seedContainers($schedule, $si, string $actor, $today): void
    {
        DB::table('vessel_schedule_dtls')
            ->where('Task_ID', $schedule->Task_ID)
            ->where('Container', 'like', 'SEEDU%')
            ->delete();

        $stuffingDate = $schedule->Stuffing ?: $today->copy()->addDays(7)->toDateString();
        $containers = [
            ['SEEDU0000001', 'SEED-SEAL-001', 'B 9001 SED'],
            ['SEEDU0000002', 'SEED-SEAL-002', 'B 9002 SED'],
        ];

        foreach ($containers as $container) {
            $tare = 2000;
            $netto = 18000;

            DB::table('vessel_schedule_dtls')->insert([
                'Task_ID' => $schedule->Task_ID,
                'Order_No' => $schedule->Order_No,
                'SI_No' => $si->SI_No,
                'Container' => $container[0],
                'Seal' => $container[1],
                'Time_From' => '08:00:00',
                'Time_To' => '17:00:00',
                'Date' => $stuffingDate,
                'Police_No' => $container[2],
                'Tare' => $tare,
                'Netto_KG' => $netto,
                'Netto_MT' => round($netto / 1000, 2),
                'Gross_KG' => $tare + $netto,
                'Gross_MT' => round(($tare + $netto) / 1000, 2),
                'Note' => 'Seeded stuffing container.',
                'Status' => 'CLOSED',
                'Created_By' => $actor,
                'Created_Date' => now(),
                'Updated_By' => $actor,
                'Updated_Date' => now(),
            ]);
        }
    }
}
