<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventStreams extends Migration
{
    public function up(): void
    {
        Schema::create('event_streams', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('real_stream_name', 150)->unique();
            $table->char('stream_name', 41);
            $table->jsonb('metadata');
            $table->string('category', 150)->nullable();

            $table->index('category', 'ix_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_streams');
    }
}
