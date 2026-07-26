<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnboardingStageToOpportunityPartner extends Migration
{
    public function up()
    {
        Schema::table('opportunity_partner', function (Blueprint $table) {
            $table->string('onboarding_stage', 50)->nullable()->after('status');
            $table->timestamp('onboarding_stage_updated_at')->nullable()->after('onboarding_stage');
            $table->unsignedBigInteger('onboarding_stage_updated_by')->nullable()->after('onboarding_stage_updated_at');
        });
    }

    public function down()
    {
        Schema::table('opportunity_partner', function (Blueprint $table) {
            $table->dropColumn(['onboarding_stage', 'onboarding_stage_updated_at', 'onboarding_stage_updated_by']);
        });
    }
}
