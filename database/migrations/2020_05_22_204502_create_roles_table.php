<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insert some production data
        DB::table('roles')->insert(
            [
                ['name' => Role::ROLE_SUPER_ADMIN, 'created_at' => now(), 'updated_at' => now() ],
                ['name' => Role::ROLE_ADMIN, 'created_at' => now(), 'updated_at' => now() ],
                ['name' => Role::ROLE_MODERATOR, 'created_at' => now(), 'updated_at' => now() ],
                ['name' => Role::ROLE_EDITOR, 'created_at' => now(), 'updated_at' => now() ],
                ['name' => Role::ROLE_AUTHENTICATED, 'created_at' => now(), 'updated_at' => now() ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
