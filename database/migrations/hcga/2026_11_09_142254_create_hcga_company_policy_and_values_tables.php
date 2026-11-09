<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHcgaCompanyPolicyAndValuesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Table for Visi, Misi, Core Values, dan Peraturan Perusahaan (Corporate Guideline / Culture & Regulation)
        if (!Schema::hasTable('hcga_company_guidelines')) {
            Schema::create('hcga_company_guidelines', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 50); // VISI, MISI, CORE_VALUE, PERATURAN
                $table->string('title', 255);
                $table->string('code', 50)->nullable(); // e.g. 'AKHLAK', 'INTEGRITY'
                $table->text('summary')->nullable(); // Tagline or brief excerpt
                $table->longText('content')->nullable(); // Rich text / full description
                $table->string('icon', 100)->nullable(); // FontAwesome icon class e.g. 'fa-eye', 'fa-bullseye', 'fa-award', 'fa-book'
                $table->string('badge_color', 50)->default('primary'); // primary, success, info, warning, purple
                $table->string('attachment_file', 255)->nullable(); // For PDF regulation or handbook
                $table->string('attachment_name', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->tinyInteger('is_active')->default(1);
                $table->string('created_by', 150)->nullable();
                $table->dateTime('created_date')->useCurrent();
                $table->string('updated_by', 150)->nullable();
                $table->dateTime('updated_date')->nullable();

                $table->index('type');
                $table->index('is_active');
                $table->index('sort_order');
            });

            // Seed initial data so it is ready right out of the box
            $now = date('Y-m-d H:i:s');
            DB::table('hcga_company_guidelines')->insert([
                [
                    'type' => 'VISI',
                    'title' => 'Visi Perusahaan',
                    'code' => 'VISION',
                    'summary' => 'Menjadi perusahaan terdepan, berkelanjutan, dan berdaya saing global.',
                    'content' => '<p>Menjadi perusahaan terdepan dan terpercaya di tingkat nasional maupun internasional yang berfokus pada keunggulan operasional, keberlanjutan lingkungan, dan inovasi berkelanjutan demi memberikan nilai tambah terbaik bagi pelanggan, karyawan, mitra kerja, serta masyarakat luas.</p>',
                    'icon' => 'fa fa-eye',
                    'badge_color' => 'primary',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 1,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'MISI',
                    'title' => 'Misi Perusahaan',
                    'code' => 'MISSION',
                    'summary' => 'Pilar-pilar strategis demi tercapainya pertumbuhan yang berkelanjutan.',
                    'content' => '<ul><li>Memberikan produk dan layanan berkualitas prima dengan standar keselamatan dan keandalan operasional tertinggi.</li><li>Membangun sumber daya manusia (SDM) yang profesional, berintegritas tinggi, dan adaptif terhadap transformasi digital.</li><li>Menerapkan prinsip Environmental, Social, and Governance (ESG) dan keberlanjutan dalam seluruh rantai pasok.</li><li>Menjaga tata kelola perusahaan yang transparan, akuntabel, dan bertanggung jawab (Good Corporate Governance).</li></ul>',
                    'icon' => 'fa fa-bullseye',
                    'badge_color' => 'success',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 2,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'CORE_VALUE',
                    'title' => 'Integrity (Integritas)',
                    'code' => 'INTEGRITY',
                    'summary' => 'Menjunjung kejujuran, etika profesi, dan komitmen dalam setiap tindakan.',
                    'content' => '<p>Setiap insan perusahaan wajib bertindak jujur, transparan, konsisten antara perkataan dan perbuatan, serta memegang teguh rahasia perusahaan dan etika bisnis.</p>',
                    'icon' => 'fa fa-handshake',
                    'badge_color' => 'info',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 1,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'CORE_VALUE',
                    'title' => 'Excellence (Keunggulan)',
                    'code' => 'EXCELLENCE',
                    'summary' => 'Memberikan hasil kerja terbaik yang melampaui ekspektasi standar.',
                    'content' => '<p>Selalu berorientasi pada kualitas prima, perbaikan berkesinambungan (continuous improvement), dan efisiensi dalam pelaksanaan tugas sehari-hari.</p>',
                    'icon' => 'fa fa-award',
                    'badge_color' => 'warning',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 2,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'CORE_VALUE',
                    'title' => 'Collaboration (Kolaborasi)',
                    'code' => 'COLLABORATION',
                    'summary' => 'Sinergi antar tim dan kemitraan untuk meraih tujuan bersama.',
                    'content' => '<p>Menghargai keberagaman pemikiran, saling percaya, proaktif saling membantu antar departemen, dan mengutamakan kesuksesan bersama di atas kepentingan individu.</p>',
                    'icon' => 'fa fa-users',
                    'badge_color' => 'primary',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 3,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'CORE_VALUE',
                    'title' => 'Agility & Innovation (Inovasi & Adaptabilitas)',
                    'code' => 'INNOVATION',
                    'summary' => 'Cepat beradaptasi terhadap perubahan dan berani berinovasi.',
                    'content' => '<p>Mendorong terciptanya ide-ide baru, berani mencoba solusi inovatif, dan fleksibel menghadapi perubahan iklim bisnis yang dinamis.</p>',
                    'icon' => 'fa fa-lightbulb',
                    'badge_color' => 'danger',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 4,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'PERATURAN',
                    'title' => 'Peraturan Perusahaan (PP) & Tata Tertib Kerja',
                    'code' => 'PP-01',
                    'summary' => 'Pedoman hak, kewajiban, jam kerja, serta disiplin bagi seluruh karyawan.',
                    'content' => '<p>Buku pedoman peraturan perusahaan mencakup ketentuan jam kerja, kehadiran, cuti, hak normatif, sanksi pelanggaran (SP 1, 2, 3), serta kode etik karyawan yang berlaku sah dan disahkan oleh Kementerian Ketenagakerjaan.</p>',
                    'icon' => 'fa fa-book-open',
                    'badge_color' => 'dark',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 1,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
                [
                    'type' => 'PERATURAN',
                    'title' => 'Kebijakan K3LH (Kesehatan, Keselamatan Kerja & Lingkungan)',
                    'code' => 'K3LH-01',
                    'summary' => 'Standar keselamatan operasional dan pengelolaan lingkungan kerja.',
                    'content' => '<p>Ketentuan wajib mengenai penggunaan APD di area operasional, penanganan darurat, pelaporan insiden, dan pencegahan kecelakaan kerja demi zero accident.</p>',
                    'icon' => 'fa fa-hard-hat',
                    'badge_color' => 'warning',
                    'attachment_file' => null,
                    'attachment_name' => null,
                    'sort_order' => 2,
                    'is_active' => 1,
                    'created_by' => 'SYSTEM',
                    'created_date' => $now,
                ],
            ]);
        }

        // 2. Add menu to menu_lists for desktop
        $existsSub = DB::table('menu_lists')->where('Menu_ID', 'SS00106')->exists();
        if (!$existsSub) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SS00107',
                'Name'        => 'Culture & Regulation',
                'Path_Loc'    => '#',
                'Position'    => 3,
                'Parent_Menu' => 'SM00070', // GA / HCGA Module
                'Urutan'      => 13,
                'Modules'     => 'HRD',
            ]);
        }

        $existsItem = DB::table('menu_lists')->where('Menu_ID', 'SI01043')->exists();
        if (!$existsItem) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01043',
                'Name'        => 'Visi Misi & Peraturan CMS',
                'Path_Loc'    => 'hrd-company-guideline-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00107',
                'Urutan'      => 1,
                'Modules'     => 'HRD',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hcga_company_guidelines');
        DB::table('menu_lists')->whereIn('Menu_ID', ['SS00106', 'SI01038'])->delete();
    }
}
