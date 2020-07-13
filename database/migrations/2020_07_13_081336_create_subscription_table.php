<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('subscription', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('platform');
            $table->string('product');
            $table->string('event')->nullable();
            $table->boolean('auto_renew_status');
            $table->string('status_change_date');
            $table->string('notification_type');
            $table->json('request');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription');
    }
}
