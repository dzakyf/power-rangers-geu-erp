<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CnsMenuSampleDataSeeder extends Seeder
{
    public function run()
    {
        $admin = DB::table('users')->where('username', 'admin')->first()
            ?: DB::table('users')->orderBy('id')->first();

        $location = $admin ? ($admin->Location ?: 'GEU-HO') : 'GEU-HO';
        $actor = $admin ? ($admin->username ?: 'system') : 'system';
        $today = now()->startOfDay();

        DB::transaction(function () use ($location, $actor, $today) {
            $this->seedMasterCompanies($location, $actor);
            $this->seedMasterSuppliers($location, $actor);
            $this->seedMasterConsignees($location, $actor);
            $this->seedBuyerContracts($location, $actor, $today);
            $this->seedVesselSchedules($location, $actor, $today);
            $this->seedStuffingContainers($actor, $today);
            $this->seedShipmentInstructions($location, $actor, $today);
            $this->seedPackingLists($actor, $today);
            $this->seedCommercialInvoices($actor, $today);
            $this->seedWeightBridgeTickets($actor, $today);
            $this->seedCertificatesOfOrigin($actor, $today);
            $this->seedSustainabilityDeclarations($actor, $today);
            $this->seedDeliveryNotes($actor, $today);
            $this->seedAnnexVII($actor, $today);
            $this->seedQualityControl($today);
            $this->seedSalesOrders($actor, $today);
            $this->seedTraceability($today);
            $this->seedPurchaseRequests($location, $actor, $today);
            $this->seedPurchaseOrders($location, $actor, $today);
        });

        $this->command->info('CnsMenuSampleDataSeeder: 5 records seeded for each of the 20 CNS menus.');
    }

    private function seedMasterCompanies(string $location, string $actor): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $code = sprintf('SEED-COMP-%03d', $i);
            DB::table('supplier')->updateOrInsert(
                ['Location' => $location, 'Supplier_ID' => $code],
                [
                    'Company_Name'   => "PT Green Energy Utama {$i}",
                    'Contact_Person' => "Corporate PIC {$i}",
                    'Address'        => "Jl. Industri Ekspor No. {$i}, Jakarta",
                    'City'           => 'Jakarta',
                    'Phone'          => sprintf('021-555000%d', $i),
                    'Email'          => "company{$i}@geu.test",
                    'Emails'         => json_encode(["company{$i}@geu.test"]),
                    'Status'         => 'NORMAL',
                    'Is_Company'     => 1,
                    'Is_Supplier'    => 0,
                    'Is_Consignee'   => 0,
                    'Created_By'     => $actor,
                    'Created_Date'   => now(),
                ]
            );
        }
    }

    private function seedMasterSuppliers(string $location, string $actor): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $code = sprintf('SEED-SUPP-%03d', $i);
            DB::table('supplier')->updateOrInsert(
                ['Location' => $location, 'Supplier_ID' => $code],
                [
                    'Company_Name'      => "CV Mitra Pemasok Mandiri {$i}",
                    'Contact_Person'    => "Supplier PIC {$i}",
                    'Address'           => "Jl. Pengumpul Limbah No. {$i}, Surabaya",
                    'City'              => 'Surabaya',
                    'Phone'             => sprintf('031-777000%d', $i),
                    'Email'             => "supplier{$i}@mitra.test",
                    'Emails'            => json_encode(["supplier{$i}@mitra.test"]),
                    'Status'            => 'NORMAL',
                    'Is_Company'        => 0,
                    'Is_Supplier'       => 1,
                    'Is_Consignee'      => 0,
                    'Supplier_Category' => 'Flexibag',
                    'Created_By'        => $actor,
                    'Created_Date'      => now(),
                ]
            );
        }
    }

    private function seedMasterConsignees(string $location, string $actor): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $code = sprintf('SEED-CONS-%03d', $i);
            DB::table('supplier')->updateOrInsert(
                ['Location' => $location, 'Supplier_ID' => $code],
                [
                    'Company_Name'   => "Global Biodiesel Buyer BV {$i}",
                    'Contact_Person' => "Consignee Agent {$i}",
                    'Address'        => "Havenlaan {$i}, Rotterdam, Netherlands",
                    'City'           => 'Rotterdam',
                    'Phone'          => sprintf('+31-10-55500%d', $i),
                    'Email'          => "consignee{$i}@buyer.test",
                    'Emails'         => json_encode(["consignee{$i}@buyer.test"]),
                    'Status'         => 'NORMAL',
                    'Is_Company'     => 0,
                    'Is_Supplier'    => 0,
                    'Is_Consignee'   => 1,
                    'Created_By'     => $actor,
                    'Created_Date'   => now(),
                ]
            );
        }
    }

    private function seedBuyerContracts(string $location, string $actor, $today): void
    {
        $consignee = DB::table('supplier')->where('Location', $location)->where('Is_Consignee', 1)->first();
        $supplierId = $consignee ? $consignee->ID : null;

        for ($i = 1; $i <= 5; $i++) {
            $contractNo = sprintf('SEED/CTR/2026/%03d', $i);
            DB::table('cns_buyer_contracts')->updateOrInsert(
                ['Contract_No' => $contractNo],
                [
                    'Supplier_ID'     => $supplierId,
                    'Buyer_Name'      => "Global Biodiesel Buyer BV {$i}",
                    'Buyer_Address'   => "Havenlaan {$i}, Rotterdam, Netherlands",
                    'Buyer_Phone'     => sprintf('+31-10-55500%d', $i),
                    'Buyer_Emails'    => json_encode(["consignee{$i}@buyer.test"]),
                    'Quantity_MT'     => 50.000 * $i,
                    'Container_Count' => 2 * $i,
                    'Container_Size'  => '20 ft',
                    'Loading_Start'   => $today->copy()->addDays($i)->toDateString(),
                    'Loading_End'     => $today->copy()->addDays($i + 10)->toDateString(),
                    'Incoterms'       => 'FOB',
                    'Price_Currency'  => 'USD',
                    'Price_Per_MT'    => 950.00 + ($i * 10),
                    'Status'          => 'ACTIVE_IN_PROGRESS',
                    'Location'        => $location,
                    'Created_By'      => $actor,
                    'Created_Date'    => now(),
                ]
            );

            $contract = DB::table('cns_buyer_contracts')->where('Contract_No', $contractNo)->first();
            if ($contract) {
                DB::table('cns_buyer_contract_bookings')->updateOrInsert(
                    ['Contract_ID' => $contract->ID, 'Label' => 'A'],
                    [
                        'Booking_No'   => sprintf('BKG-SEED-%03d', $i),
                        'Created_By'   => $actor,
                        'Created_Date' => now(),
                    ]
                );
            }
        }
    }

    private function seedVesselSchedules(string $location, string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $orderNo = sprintf('SEED-VS-ORD-%03d', $i);
            $contractNo = sprintf('SEED/CTR/2026/%03d', $i);
            $contract = DB::table('cns_buyer_contracts')->where('Contract_No', $contractNo)->first();
            $booking = $contract ? DB::table('cns_buyer_contract_bookings')->where('Contract_ID', $contract->ID)->first() : null;

            DB::table('vessel_schedules')->updateOrInsert(
                ['Order_No' => $orderNo],
                [
                    'Task_ID'         => sprintf('VS/GEU/SEED/%03d', $i),
                    'Contract_ID'     => $contract ? $contract->ID : null,
                    'Contract_No'     => $contractNo,
                    'Booking_ID'      => $booking ? $booking->ID : null,
                    'Booking_No'      => $booking ? $booking->Booking_No : null,
                    'Shipping_Method' => 'CONTAINER',
                    'Vessel'          => "MV Green Explorer {$i}",
                    'POL'             => 'Tanjung Perak, Surabaya',
                    'POD'             => 'Rotterdam Port',
                    'ETD'             => $today->copy()->addDays(5 + $i)->toDateString(),
                    'ETA'             => $today->copy()->addDays(25 + $i)->toDateString(),
                    'Qty'             => '50 MT',
                    'Qty1'            => 50.000 * $i,
                    'Qty2'            => (string) (50000 * $i),
                    'Incoterms'       => 'FOB',
                    'Created_By'      => $actor,
                    'Created_Date'    => now(),
                ]
            );
        }
    }

    private function seedStuffingContainers(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $orderNo = sprintf('SEED-VS-ORD-%03d', $i);
            $taskId = sprintf('VS/GEU/SEED/%03d', $i);
            $containerNo = sprintf('MSKU%07d', 1000000 + $i);

            DB::table('vessel_schedule_dtls')->updateOrInsert(
                ['Order_No' => $orderNo, 'Container' => $containerNo],
                [
                    'Task_ID'      => $taskId,
                    'Seal'         => sprintf('SEAL-%05d', 50000 + $i),
                    'Gross_KG'     => 24000.00,
                    'Tare'         => 2200.00,
                    'Netto_KG'     => 21800.00,
                    'Netto_MT'     => 21.800,
                    'Status'       => 'COMPLETED',
                    'Date'         => $today->copy()->subDays($i)->toDateString(),
                    'Created_By'   => $actor,
                    'Created_Date' => now(),
                ]
            );
        }
    }

    private function seedShipmentInstructions(string $location, string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $siNo = sprintf('SEED/SI/2026/%03d', $i);
            $contractNo = sprintf('SEED/CTR/2026/%03d', $i);

            // 5 Draft SIs
            DB::table('shipment_instructions')->updateOrInsert(
                ['SI_No' => $siNo],
                [
                    'Order_No'         => sprintf('SEED-VS-ORD-%03d', $i),
                    'Final_SI_No'      => null,
                    'Final_SI_Date'    => null,
                    'SI_Date'          => $today->copy()->subDays(5 - $i)->toDateString(),
                    'Qty'              => '2x20 ft Container',
                    'Qty1'             => '43.600 MT',
                    'Qty2'             => '43,600 KGS',
                    'Shipper'          => sprintf('SEED-COMP-%03d', $i),
                    'Consignee'        => sprintf('SEED-CONS-%03d', $i),
                    'Notify'           => 'Same as Consignee',
                    'Destination_Port' => 'Rotterdam, Netherlands',
                    'Delivery_Terms'   => 'FOB Surabaya',
                    'Contract_No'      => $contractNo,
                    'Shipped_By'       => "MV Green Explorer {$i}",
                    'Voyage'           => sprintf('VOY-%03dA', $i),
                    'ETD'              => $today->copy()->addDays(5 + $i)->toDateString(),
                    'ETA'              => $today->copy()->addDays(25 + $i)->toDateString(),
                    'Stuffing'         => $today->copy()->subDays(1)->toDateString(),
                    'PEB_No'           => sprintf('PEB-%06d', 110000 + $i),
                    'PEB_Date'         => $today->toDateString(),
                    'BL_No'            => sprintf('BL-MSK-%05d', 80000 + $i),
                    'POL'              => 'Tanjung Perak, Surabaya',
                    'POD'              => 'Rotterdam, Netherlands',
                    'HS_Code'          => '15180019',
                    'Status'           => 'DRAFT',
                    'Product_Name'     => 'Used Cooking Oil (UCO)',
                    'Created_By'       => $actor,
                    'Created_Date'     => now(),
                ]
            );

            // 5 Final SIs (FSI)
            $fsiNo = sprintf('SEED/FSI/2026/%03d', $i);
            DB::table('shipment_instructions')->updateOrInsert(
                ['Final_SI_No' => $fsiNo],
                [
                    'Order_No'         => sprintf('SEED-VS-ORD-%03d', $i),
                    'SI_No'            => sprintf('SEED/SI-BASE/2026/%03d', $i),
                    'Final_SI_Date'    => $today->toDateString(),
                    'SI_Date'          => $today->copy()->subDays(10)->toDateString(),
                    'Qty'              => '2x20 ft Container',
                    'Qty1'             => '43.600 MT',
                    'Qty2'             => '43,600 KGS',
                    'Shipper'          => sprintf('SEED-COMP-%03d', $i),
                    'Consignee'        => sprintf('SEED-CONS-%03d', $i),
                    'Notify'           => 'Same as Consignee',
                    'Destination_Port' => 'Rotterdam, Netherlands',
                    'Delivery_Terms'   => 'FOB Surabaya',
                    'Contract_No'      => $contractNo,
                    'Shipped_By'       => "MV Green Explorer {$i}",
                    'Voyage'           => sprintf('VOY-%03dA', $i),
                    'ETD'              => $today->copy()->addDays(5 + $i)->toDateString(),
                    'ETA'              => $today->copy()->addDays(25 + $i)->toDateString(),
                    'Stuffing'         => $today->copy()->subDays(1)->toDateString(),
                    'PEB_No'           => sprintf('PEB-FIN-%06d', 220000 + $i),
                    'PEB_Date'         => $today->toDateString(),
                    'BL_No'            => sprintf('BL-FIN-MSK-%05d', 90000 + $i),
                    'POL'              => 'Tanjung Perak, Surabaya',
                    'POD'              => 'Rotterdam, Netherlands',
                    'HS_Code'          => '15180019',
                    'Status'           => 'FINAL',
                    'Product_Name'     => 'Used Cooking Oil (UCO)',
                    'Created_By'       => $actor,
                    'Created_Date'     => now(),
                ]
            );
        }
    }

    private function seedPackingLists(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $plNo = sprintf('SEED/PL/2026/%03d', $i);
            DB::table('packing_lists')->updateOrInsert(
                ['Packing_List_No' => $plNo],
                [
                    'PL_Date'            => $today->toDateString(),
                    'Order_No'           => sprintf('SEED-VS-ORD-%03d', $i),
                    'SI_No'              => sprintf('SEED/SI-BASE/2026/%03d', $i),
                    'Final_SI_No'        => sprintf('SEED/FSI/2026/%03d', $i),
                    'SI_Date'            => $today->copy()->subDays(10)->toDateString(),
                    'Final_SI_Date'      => $today->toDateString(),
                    'Qty'                => '2x20 ft Container',
                    'Qty1'               => '43.600 MT',
                    'Qty2'               => '43,600 KGS',
                    'Shipper'            => sprintf('SEED-COMP-%03d', $i),
                    'Consignee'          => sprintf('SEED-CONS-%03d', $i),
                    'Notify'             => 'Same as Consignee',
                    'Destination_Port'   => 'Rotterdam, Netherlands',
                    'Delivery_Terms'     => 'FOB Surabaya',
                    'Contract_No'        => sprintf('SEED/CTR/2026/%03d', $i),
                    'Voyage'             => sprintf('VOY-%03dA', $i),
                    'ETD'                => $today->copy()->addDays(5 + $i)->toDateString(),
                    'ETA'                => $today->copy()->addDays(25 + $i)->toDateString(),
                    'Stuffing'           => $today->copy()->subDays(1)->toDateString(),
                    'PEB_No'             => sprintf('PEB-%06d', 110000 + $i),
                    'PEB_Date'           => $today->toDateString(),
                    'BL_No'              => sprintf('BL-MSK-%05d', 80000 + $i),
                    'POL'                => 'Tanjung Perak, Surabaya',
                    'POD'                => 'Rotterdam, Netherlands',
                    'HS_Code'            => '15180019',
                    'Status'             => 'CONFIRMED',
                    'Product_Name'       => 'Used Cooking Oil (UCO)',
                    'Created_By'         => $actor,
                    'Created_Date'       => now(),
                ]
            );
        }
    }

    private function seedCommercialInvoices(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $invNo = sprintf('SEED/INV/2026/%03d', $i);
            $qty = 43.60;
            $unitPrice = 1000.00;
            $amount = $qty * $unitPrice;

            DB::table('commercial_invoices')->updateOrInsert(
                ['Inv_No' => $invNo],
                [
                    'SI_No'           => sprintf('SEED/FSI/2026/%03d', $i),
                    'Inv_Date'        => $today->toDateString(),
                    'Shipper'         => sprintf('SEED-COMP-%03d', $i),
                    'Consignee'       => sprintf('SEED-CONS-%03d', $i),
                    'Description'     => 'Used Cooking Oil (UCO) in Flexibag',
                    'Qty'             => $qty,
                    'Unit_Price'      => $unitPrice,
                    'Amount'          => $amount,
                    'Advance_Payment' => 0.00,
                    'Other'           => 0.00,
                    'Total'           => $amount,
                    'Status'          => 'POSTED',
                    'Created_By'      => $actor,
                    'Created_Date'    => now(),
                ]
            );
        }
    }

    private function seedWeightBridgeTickets(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $wbtNo = sprintf('SEED/WBT/2026/%03d', $i);
            DB::table('weight_bridge_ticket')->updateOrInsert(
                ['WBT_No' => $wbtNo],
                [
                    'Packing_List_No' => sprintf('SEED/PL/2026/%03d', $i),
                    'Final_SI_No'     => sprintf('SEED/FSI/2026/%03d', $i),
                    'SI_No'           => sprintf('SEED/SI-BASE/2026/%03d', $i),
                    'Contract_No'     => sprintf('SEED/CTR/2026/%03d', $i),
                    'Voyage'          => sprintf('VOY-%03dA', $i),
                    'Departure_Date'  => $today->toDateString(),
                    'BL_No'           => sprintf('BL-MSK-%05d', 80000 + $i),
                    'Created_By'      => $actor,
                    'Created_Date'    => now(),
                ]
            );

            DB::table('weight_bridge_ticket_dtl')->updateOrInsert(
                ['WBT_No' => $wbtNo, 'VSD_ID' => $i],
                [
                    'Path_Loc'     => "tickets/wbt_{$i}.pdf",
                    'Path_Type'    => 'pdf',
                    'Created_By'   => $actor,
                    'Created_Date' => now(),
                ]
            );
        }
    }

    private function seedCertificatesOfOrigin(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $cooNo = sprintf('SEED/COO/2026/%03d', $i);
            DB::table('certificate_of_origin')->updateOrInsert(
                ['COO_No' => $cooNo],
                [
                    'Consignee'          => sprintf('SEED-CONS-%03d', $i),
                    'Fin_Inv_No'         => sprintf('SEED/INV/2026/%03d', $i),
                    'Fin_Inv_Date'       => $today->toDateString(),
                    'Contract_No'        => sprintf('SEED/CTR/2026/%03d', $i),
                    'Method_Of_Dispatch' => 'Sea Freight',
                    'Shipment_Type'      => 'Containerized',
                    'Port_Of_Loading'    => 'Surabaya, Indonesia',
                    'Port_Of_Discharge'  => 'Rotterdam, Netherlands',
                    'Final_Destination'  => 'Rotterdam, Netherlands',
                    'Kind_Packages'      => 'Flexibags in Containers',
                    'Good_Description'   => 'Used Cooking Oil',
                    'HS_Code'            => '15180019',
                    'Net_Weight'         => '43600 KGS',
                    'Place_Of_Issue'     => 'Surabaya',
                    'Issue_Date'         => $today->toDateString(),
                    'Note'               => 'Clean On Board',
                    'Status'             => 'ISSUED',
                    'Created_By'         => $actor,
                    'Created_Date'       => now(),
                ]
            );

            DB::table('certificate_of_origin_dtl')->updateOrInsert(
                ['COO_No' => $cooNo, 'Container_No' => sprintf('MSKU%07d', 1000000 + $i)],
                [
                    'Seal_No' => sprintf('SEAL-%05d', 50000 + $i),
                ]
            );
        }
    }

    private function seedSustainabilityDeclarations(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $sdNo = sprintf('SEED/SD/2026/%03d', $i);
            DB::table('sustainability_declarations')->updateOrInsert(
                ['SD_No' => $sdNo],
                [
                    'Issue_Date'             => $today->toDateString(),
                    'Contract_No'            => sprintf('SEED/CTR/2026/%03d', $i),
                    'Consignee'              => sprintf('SEED-CONS-%03d', $i),
                    'Certificate_No'         => sprintf('ISCC-EU-REC-%04d', 1000 + $i),
                    'Product_Type'           => 'Used Cooking Oil (UCO)',
                    'Raw_Material_Type'      => 'Used Cooking Oil entirely of veg origin',
                    'Country_Of_Origin'      => 'Indonesia',
                    'Quantity'               => 43.600,
                    'Quantity_Unit'          => 'metric tons',
                    'ISCC_Compliant'         => 1,
                    'EU_RED_Compliant'       => 1,
                    'Chain_Of_Custody'       => 'Mass balance',
                    'Scope_Art29'            => 'Yes',
                    'Scope_Intermediate_Crop'=> 'Yes',
                    'Scope_Low_ILUC'         => 'Yes',
                    'Scope_Waste_Residue'    => 'Yes',
                    'Waste_Permit_No'        => sprintf('W-PERMIT-%04d', 8000 + $i),
                    'Status'                 => 'APPROVED',
                    'Created_By'             => $actor,
                    'Created_Date'           => now(),
                ]
            );
        }
    }

    private function seedDeliveryNotes(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $dnNo = sprintf('SEED/DN/2026/%03d', $i);
            $issuerIndex = (($i - 1) % 2) + 1; // Alternating between entity 1 and entity 2
            DB::table('delivery_notes')->updateOrInsert(
                ['DN_No' => $dnNo],
                [
                    'Shipper'            => sprintf('SEED-COMP-%03d', $issuerIndex),
                    'Consignee'          => sprintf('SEED-CONS-%03d', $i),
                    'Supplier_ISCC_Ref'  => sprintf('ISCC-SUP-%04d', 2000 + $i),
                    'Contract_No'        => sprintf('SEED/CTR/2026/%03d', $i),
                    'Product'            => 'Used Cooking Oil (UCO) entirely of Vege-Oil Origin',
                    'Total_Tonnage'      => 43.600,
                    'Delivery_Date'      => $today->toDateString(),
                    'Country_Of_Origin'  => 'Indonesia',
                    'GHG_Value'          => '85% GHG Savings',
                    'Note'               => 'Standard export batch',
                    'Status'             => 'COMPLETED',
                    'Created_By'         => $actor,
                    'Created_Date'       => now(),
                ]
            );
        }
    }

    private function seedAnnexVII(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $annexNo = sprintf('SEED/ANNEX/2026/%03d', $i);
            DB::table('annex_vii')->updateOrInsert(
                ['Annex_No' => $annexNo],
                [
                    'SI_No'                  => sprintf('SEED/FSI/2026/%03d', $i),
                    'Order_No'               => sprintf('SEED-VS-ORD-%03d', $i),
                    'Qty1'                   => 43.60,
                    'Qty2'                   => 43.60,
                    'SI_Date'                => $today->toDateString(),
                    'Shipper'                => sprintf('SEED-COMP-%03d', $i),
                    'Consignee'              => sprintf('SEED-CONS-%03d', $i),
                    'actual_quantity'        => 43.60,
                    'actual_date'            => $today->toDateString(),
                    'ETD'                    => $today->copy()->addDays(5 + $i)->toDateString(),
                    'ETA'                    => $today->copy()->addDays(25 + $i)->toDateString(),
                    'Stuffing'               => $today->toDateString(),
                    'POL'                    => 'Surabaya',
                    'POD'                    => 'Rotterdam',
                    'Contract_No'            => sprintf('SEED/CTR/2026/%03d', $i),
                    'Voyage'                 => sprintf('VOY-%03dA', $i),
                    'Destination_Port'       => 'Rotterdam',
                    'Product_Name'           => 'Used Cooking Oil',
                    'waste_generator_name'   => 'Green Energy Gathering Point',
                    'waste_generator_address'=> 'Surabaya Industrial Estate',
                    'export_country'         => 'Indonesia',
                    'import_destination'     => 'Netherlands',
                    'declarant_name'         => $actor,
                    'declarant_date'         => $today->toDateString(),
                    'created_by'             => $actor,
                ]
            );
        }
    }

    private function seedQualityControl($today): void
    {
        $types = ['TOP', 'MIDDLE', 'BOTTOM', 'TOP', 'MIDDLE'];
        for ($i = 1; $i <= 5; $i++) {
            DB::table('uco_quality_control')->updateOrInsert(
                ['Source_ID' => $i, 'Type' => $types[$i - 1]],
                [
                    'Created_At'      => $today->copy()->subHours($i * 2),
                    'FFA_As_Palmitic' => 3.20 + ($i * 0.1),
                    'FFA_As_Oleic'    => 2.80 + ($i * 0.1),
                    'MC'              => 0.45,
                    'IMP'             => 0.12,
                    'Is_Deleted'      => 0,
                ]
            );
        }
    }

    private function seedSalesOrders(string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $soNo = sprintf('SEED/SO/2026/%03d', $i);
            $subtotal = 50000000.00 * $i;
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            DB::table('sales_order_cns')->updateOrInsert(
                ['so_no' => $soNo],
                [
                    'so_date'        => $today->toDateString(),
                    'buyer_pic'      => "Purchasing Officer {$i}",
                    'buyer_city'     => 'Surabaya',
                    'buyer_province' => 'Jawa Timur',
                    'buyer_phone'    => sprintf('081234567%02d', $i),
                    'site'           => "Plant {$i}",
                    'site_address'   => "Kawasan Industri Rungkut {$i}",
                    'payment_term'   => 'TOP 30 Hari',
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'shipping'       => 0.00,
                    'other'          => 0.00,
                    'total'          => $total,
                    'remarks'        => 'Seeded sales order record',
                    'created_by'     => $actor,
                    'created_date'   => now(),
                ]
            );

            DB::table('sales_order_cns_dtl')->updateOrInsert(
                ['so_no' => $soNo, 'sort_order' => 1],
                [
                    'product_code' => sprintf('PROD-UCO-%02d', $i),
                    'description'  => 'Used Cooking Oil Grade A',
                    'qty'          => 20.000 * $i,
                    'unit_price'   => 2500000.00,
                    'total'        => $subtotal,
                    'created_by'   => $actor,
                    'created_date' => now(),
                ]
            );
        }
    }

    private function seedTraceability($today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $apiRef = sprintf('SEED-TRC-API-%03d', $i);
            DB::table('traceability_external_deliveries')->updateOrInsert(
                ['api_reference' => $apiRef],
                [
                    'supplier_name'          => "Kolektor UCO Mandiri {$i}",
                    'city'                   => 'Sidoarjo',
                    'province'               => 'Jawa Timur',
                    'delivery_date'          => $today->copy()->subDays($i)->toDateString(),
                    'total_quantity_kg'      => 5000.00 * $i,
                    'status'                 => 'AUDITED',
                    'delivery_documentation' => 'Surat Jalan & Bukti Timbang Terlampir',
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]
            );

            $del = DB::table('traceability_external_deliveries')->where('api_reference', $apiRef)->first();
            if ($del) {
                DB::table('traceability_external_poo_fragments')->updateOrInsert(
                    ['traceability_external_delivery_id' => $del->id, 'fragment_sequence' => 1],
                    [
                        'poo_name'    => "Restoran Sambal Sedap {$i}",
                        'quantity_kg' => 2500.00 * $i,
                        'unit'        => 'KG',
                        'city'        => 'Sidoarjo',
                        'province'    => 'Jawa Timur',
                        'site'        => 'WARU-01',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }
        }
    }

    private function seedPurchaseRequests(string $location, string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $prNo = sprintf('SEED/PR/2026/%03d', $i);
            $subtotal = 10000000.00 * $i;
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            DB::table('purchase_request_cns')->updateOrInsert(
                ['PR_No' => $prNo],
                [
                    'Location'         => $location,
                    'Process_By'       => 'HO',
                    'Supplies_Type'    => 'OPERASIONAL',
                    'Category'         => 'CONSUMABLE',
                    'Sub_Category'     => 'PACKAGING',
                    'PIC'              => "Procurement PIC {$i}",
                    'PIC_Address'      => 'Gedung Grha GEU, Surabaya',
                    'PIC_Phone'        => sprintf('0811998877%02d', $i),
                    'PR_Date'          => $today->copy()->subDays($i)->toDateString(),
                    'Request_By'       => $actor,
                    'Department'       => 'OPERASIONAL',
                    'Status'           => 'APPROVED',
                    'Payment_Term'     => '30 Hari Net',
                    'Supplier'         => sprintf('SEED-SUPP-%03d', $i),
                    'Delivery_Date'    => $today->copy()->addDays(7)->toDateString(),
                    'Delivery_Site'    => 'Warehouse Pelindo',
                    'Delivery_Term'    => 'Loco Warehouse',
                    'Shipped_By'       => 'Trucking Vendor',
                    'Subtotal'         => $subtotal,
                    'Tax'              => $tax,
                    'Shipping'         => 0.00,
                    'Other'            => 0.00,
                    'Total'            => $total,
                    'Created_By'       => $actor,
                    'Created_Date'     => now(),
                ]
            );

            DB::table('purchase_request_cns_dtl')->updateOrInsert(
                ['PR_No' => $prNo, 'Product_Code' => sprintf('ITEM-PACK-%02d', $i)],
                [
                    'Purchase_Category' => 'CONSUMABLE',
                    'Purchase_Type'     => 'FLEXIBAG',
                    'Product_Desc'      => "Flexibag 24,000L Type {$i}",
                    'Qty'               => 10 * $i,
                    'Unit'              => 'PCS',
                    'Default_Price'     => 1000000.00,
                    'Location'          => $location,
                ]
            );
        }
    }

    private function seedPurchaseOrders(string $location, string $actor, $today): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $poNo = sprintf('SEED/PO/2026/%03d', $i);
            $prNo = sprintf('SEED/PR/2026/%03d', $i);
            $subtotal = 10000000.00 * $i;
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            DB::table('purchase_order_cns')->updateOrInsert(
                ['PO_No' => $poNo],
                [
                    'Task_ID'          => sprintf('PO/TASK/%03d', $i),
                    'Term_Payment'     => '30 Hari Net',
                    'Location'         => $location,
                    'Process_By'       => 'HO',
                    'Purchase_Type'    => 'FLEXIBAG',
                    'Purchase_Category'=> 'CONSUMABLE',
                    'PR_No'            => $prNo,
                    'PO_Date'          => $today->toDateString(),
                    'Department'       => 'OPERASIONAL',
                    'PIC'              => "Procurement PIC {$i}",
                    'Supplier'         => sprintf('SEED-SUPP-%03d', $i),
                    'Subtotal'         => $subtotal,
                    'PPN'              => $tax,
                    'Total'            => $total,
                    'Total_Paid'       => 0.00,
                    'Status'           => 'APPROVED',
                    'Date_Trx'         => now(),
                    'User_Trx'         => $actor,
                    'Delivery_Date'    => $today->copy()->addDays(7)->toDateString(),
                    'Delivery_Site'    => 'Warehouse Pelindo',
                    'Delivery_Term'    => 'Loco Warehouse',
                    'Shipped_By'       => 'Trucking Vendor',
                ]
            );

            DB::table('purchase_order_cns_dtl')->updateOrInsert(
                ['PO_No' => $poNo, 'PR_No' => $prNo],
                [
                    'Location'     => $location,
                    'Category'     => 'CONSUMABLE',
                    'Product_Desc' => "Flexibag 24,000L Type {$i}",
                    'Qty'          => 10 * $i,
                    'Unit'         => 'PCS',
                    'Price'        => 1000000.00,
                    'Subtotal'     => $subtotal,
                ]
            );
        }
    }
}
