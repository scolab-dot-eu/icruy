<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ChangeRequest;
use App\EditableLayerDef;

class CreateCaminosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'cr_caminos';
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_camino', 8)->unique();
            $table->string('departamento', 4);
            $table->string('status', 23)->default(ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
            $table->decimal('ancho_calzada', 2, 1);
            $table->string('rodadura');
            $table->boolean('banquina');
            $table->boolean('cordon');
            $table->string('cuneta');
            $table->string('senaliz_horiz');
            $table->string('observaciones');
            $table->string('origin');
            $table->timestamps();
            $table->foreign('departamento')->references('code')->on('departments');
            $table->index(['status', 'codigo_camino']);
            $table->index(['departamento', 'status', 'codigo_camino']);
        });
        
        $historicName = EditableLayerDef::getHistoricTableName($name);
        $specificFields = ['ancho_calzada', 'rodadura', 'banquina',
                'cordon', 'cuneta', 'senaliz_horiz', 'observaciones'];
        DB::unprepared("
            CREATE TRIGGER ".$name."_before_insert
            BEFORE INSERT
               ON ".$name." FOR EACH ROW
            BEGIN
               IF NEW.status IS NULL OR (NEW.status <> '".ChangeRequest::FEATURE_STATUS_VALIDATED."' AND 
                   NEW.status <> '".ChangeRequest::FEATURE_STATUS_PENDING_CREATE."') THEN
                       SET NEW.status = '".ChangeRequest::FEATURE_STATUS_PENDING_CREATE."';
               END IF;
               IF NEW.origin IS NULL OR NEW.origin <> '".ChangeRequest::FEATURE_ORIGIN_ICRWEB."' THEN
                   SET NEW.origin = '".ChangeRequest::FEATURE_ORIGIN_BATCHLOAD."';
                   SET NEW.created_at = CURDATE();
                   SET NEW.updated_at = CURDATE();
               END IF;
            END
        ");
        
        DB::unprepared("
            CREATE TRIGGER ".$name."_create_changerequest
            AFTER INSERT
                ON ".$name." FOR EACH ROW BEGIN
                IF NEW.origin = '".ChangeRequest::FEATURE_ORIGIN_BATCHLOAD."' THEN
                    IF NEW.status = '".ChangeRequest::FEATURE_STATUS_PENDING_CREATE."' THEN
                        INSERT INTO changerequests
                            (requested_by_id, layer, feature_id, departamento, status, operation)
                        VALUES
                            (0, '".$name."', NEW.id, NEW.departamento, 0, '".ChangeRequest::OPERATION_CREATE."');
                    END IF;
                END IF;
            END
        ");
        
        DB::unprepared("
            CREATE TRIGGER ".$name."_after_insert
            AFTER INSERT
                ON ".$name." FOR EACH ROW BEGIN
                IF NEW.status = '".ChangeRequest::FEATURE_STATUS_VALIDATED."' THEN
                    -- Insert the new record into history table
                    INSERT INTO ".$historicName."
                        ( feat_id, valid_from, valid_to, departamento, codigo_camino, updated_at, created_at, "
            ."`".implode('`, `', $specificFields)."` )
                    VALUES
                        ( NEW.id, NOW(), '9999-12-31 23:59:59', NEW.departamento, NEW.codigo_camino, NEW.updated_at, NEW.created_at, "
            ."NEW.`".implode('`, NEW.`', $specificFields)."` );
                END IF;
            END
        ");
        DB::unprepared("
            CREATE TRIGGER ".$name."_before_update
            BEFORE UPDATE
                ON ".$name." FOR EACH ROW BEGIN
                DECLARE theCurrentTime DATETIME;
                IF NEW.status = '".ChangeRequest::FEATURE_STATUS_VALIDATED."' THEN
                    SELECT NOW() INTO theCurrentTime;
                    -- Insert the new record into history table
                    UPDATE ".$historicName."
                        SET valid_to = theCurrentTime
                    WHERE feat_id = OLD.id AND valid_to = '9999-12-31 23:59:59';
                    INSERT INTO ".$historicName."
                        ( feat_id, valid_from, valid_to, departamento, codigo_camino, updated_at, created_at, "
                          ."`".implode('`, `', $specificFields)."` )
                    VALUES
                        ( NEW.id, theCurrentTime, '9999-12-31 23:59:59', NEW.departamento, NEW.codigo_camino, NEW.updated_at, NEW.created_at, "
                          ."NEW.`".implode('`, NEW.`', $specificFields)."` );
                END IF;
            END
        ");
        DB::unprepared("
            CREATE TRIGGER ".$name."_before_delete
            BEFORE DELETE
               ON ".$name." FOR EACH ROW
            BEGIN
              -- Set end of life for the old record
              UPDATE ".$historicName."
              SET valid_to = NOW()
              WHERE feat_id = OLD.id AND valid_to = '9999-12-31 23:59:59';
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cr_caminos');
    }
}
