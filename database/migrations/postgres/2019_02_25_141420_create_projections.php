<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjections extends Migration
{
    public function up(): void
    {
        Schema::create('projections', function (Blueprint $table) {
            $table->bigInteger('no', true);
            $table->string('name', 150)->unique();
            $table->jsonb('position');
            $table->jsonb('state');
            $table->string('status', 28);
            $table->char('locked_until', 26)->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projections');
    }
}
