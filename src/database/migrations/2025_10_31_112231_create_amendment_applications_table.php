<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmendmentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amendment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approval_status_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->text('comment');
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amendment_applications');
    }
}
